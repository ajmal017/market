<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$commando = new \Commando\Command();
$commando->setHelp('Import futures data.');
$commando->option('s')
	->aka('source')
	->describedAs('Specify QUANDL database as source.')
	->must(function($source) {
		$sources = ['CHRIS', 'SRF'];
		return \in_array(\strtoupper($source), $sources);
	})
	->default('CHRIS')
	->map(function($source) {
		return \ucfirst(\strtolower($source));
	});
$commando->option('skip')
	->describedAs('Skip rows defined by this number.')
	->must(function($skip) {
		return \ctype_digit($skip);
	})
	->default('0')
	->map(function($skip) {
		return \intval($skip);
	});
$commando->option('log-level')
	->describedAs('Print logs with this or higher level to STDERR. Default is "debug" level.')
	->must(function($logLevel) {
		$levels = [
			\Psr\Log\LogLevel::EMERGENCY,
			\Psr\Log\LogLevel::ALERT,
			\Psr\Log\LogLevel::CRITICAL,
			\Psr\Log\LogLevel::ERROR,
			\Psr\Log\LogLevel::WARNING,
			\Psr\Log\LogLevel::NOTICE,
			\Psr\Log\LogLevel::INFO,
			\Psr\Log\LogLevel::DEBUG,
		];
		return \in_array(\strtolower($logLevel), $levels);
	})
	->default(\Psr\Log\LogLevel::DEBUG);
$commando->option('symbol')
	->describedAs('Import only future contracts with this symbol.')
	->default(null);


define('LOG_LEVEL', $commando['log-level']);

const DB_CONNECT = '/etc/webconf/market/connect.powerUser.pgsql';
const QUANDL_API_KEY = '/etc/webconf/quandl.api.key';

$apiKey = trim(file_get_contents(QUANDL_API_KEY));
$di = new class($apiKey) implements \Sharkodlak\Db\Di, \Sharkodlak\Db\Adapter\Di, \Sharkodlak\Market\Quandl\Di {
	private $privateApiKey;
	private $services = [];
	public function __construct($apiKey) {
		$this->privateApiKey = $apiKey;
	}
	public function __get($name) {
		if (!isset($services[$name])) {
			$method = "get$name";
			$services[$name] = $this->$method();
		}
		return $services[$name];
	}
	public function getApiKey(): string {
		return $this->privateApiKey;
	}
	public function getQuery(...$args): \Sharkodlak\Db\Queries\Query {
		return new \Sharkodlak\Db\Queries\Query(...$args);
	}
	public function getConnector(): \Sharkodlak\Market\Quandl\Connector {
		return new Sharkodlak\Market\Quandl\Connector($this);
	}
	public function getFutures(): \Sharkodlak\Market\Futures {
		return new Sharkodlak\Market\Futures();
	}
	public function getLogger(): \Psr\Log\LoggerInterface {
		$logger = new class extends \Psr\Log\AbstractLogger {
			static private $defaultBashStyle = "\e[2m";
			static private $defaultBashStyleEnd = " \e[0m";
			static private $bashStyle = [
				\Psr\Log\LogLevel::EMERGENCY => "\e[91m",
				\Psr\Log\LogLevel::ALERT => "\e[91m",
				\Psr\Log\LogLevel::CRITICAL => "\e[91m",
				\Psr\Log\LogLevel::ERROR => "\e[91m",
				\Psr\Log\LogLevel::WARNING => "\e[33m",
				\Psr\Log\LogLevel::NOTICE => "\e[93m",
				\Psr\Log\LogLevel::INFO => "\e[2m",
			];
			public function log($level, $message, array $context = []) {
				$logLevelName = \strtoupper(LOG_LEVEL);
				if ($level >= \constant("\\Psr\\Log\\LogLevel::$logLevelName")) {
					$styleStart = self::$bashStyle[$level] ?? self::$defaultBashStyle;
					$message = $styleStart . $message . self::$defaultBashStyleEnd;
					fputs(STDERR, $message);
				}
			}
		};
		return $logger;
	}
	public function getProgressBar(): \ProgressBar\Manager {
		return $this->progressBar;
	}
	public function getRootDir(): string {
		return '/vagrant';
	}
	public function initProgressBar(int $current, int $max): self {
		$this->progressBar = new \ProgressBar\Manager($current, $max);
		return $this;
	}
};
$pdo = new \PDO('uri:file://' . DB_CONNECT);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$dbAdapter = new \Sharkodlak\Db\Adapter\Postgres($pdo, $di);
$db = new \Sharkodlak\Db\Db($di, $dbAdapter);
$futures = new Sharkodlak\Market\Quandl\Adapter\Chris($di);
$futures->getAndStoreContracts($db, iterator_to_array($commando));
