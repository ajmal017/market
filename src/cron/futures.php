<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

$logLevels = [
	-5 => \Psr\Log\LogLevel::EMERGENCY,
	-4 => \Psr\Log\LogLevel::ALERT,
	-3 => \Psr\Log\LogLevel::CRITICAL,
	-2 => \Psr\Log\LogLevel::ERROR,
	-1 => \Psr\Log\LogLevel::WARNING,
	0 => \Psr\Log\LogLevel::NOTICE,
	1 => \Psr\Log\LogLevel::INFO,
	2 => \Psr\Log\LogLevel::DEBUG,
];
$commando = new \Commando\Command();
$commando->setHelp('Import futures data.');
$commando->option('batch')
	->aka('limit')
	->describedAs('Import only this number of rows.')
	->must(function($batch) {
		return \ctype_digit($batch);
	})
	->default(PHP_INT_MAX)
	->map(function($batch) {
		return \intval($batch);
	});
$commando->option('log-level')
	->describedAs('Print logs with this or higher level to STDERR. Default is "notice" level. Flag -v is ignored if this option is used.')
	->must(function($logLevel) use ($logLevels) {
		return \in_array(\strtolower($logLevel), $logLevels);
	});
$commando->option('offline')
	->describedAs('Work offline - use only data from "data" directory.')
	->boolean()
	->default(false);
$commando->option('reimport')
	->describedAs('Reimport all trade days? Without this flag only new data will be imported.')
	->boolean()
	->default(false);
$commando->option('source')
	->describedAs('Specify QUANDL database as source.')
	->must(function($source) {
		$sources = ['Chris', 'Srf'];
		return \in_array(\ucfirst(\strtolower($source)), $sources);
	})
	->default('Chris')
	->map(function($source) {
		return \ucfirst(\strtolower($source));
	});
$commando->option('skip')
	->aka('offset')
	->describedAs('Skip rows defined by this number.')
	->must(function($skip) {
		return \ctype_digit($skip);
	})
	->default('0')
	->map(function($skip) {
		return \intval($skip);
	});
$commando->option('symbol')
	->describedAs('Import only future contracts with this symbol.')
	->default(null);
$commando->flag('v')
	->title('Logging verbosity')
	->describedAs('Repeat flag for more detailed log level. Use this flag to easily switch --log-level to "info" or "debug".')
	->increment(2);

define('LOG_LEVEL', $commando['log-level'] ?? $logLevels[$commando['v']]);

const DB_CONNECT = '/etc/webconf/market/connect.powerUser.pgsql';
const QUANDL_API_KEY = '/etc/webconf/quandl.api.key';

$apiKey = trim(file_get_contents(QUANDL_API_KEY));
$settings = [
	'batch' => $commando['batch'],
	'offline' => $commando['offline'],
	'reimport' => $commando['reimport'],
	'skip' => $commando['skip'],
	'symbol' => $commando['symbol'],
];
$di = new class($apiKey, $settings) implements \Sharkodlak\Db\Di, \Sharkodlak\Db\Adapter\Di, \Sharkodlak\Market\Quandl\Di {
	private $privateApiKey;
	private $services = [];
	public function __construct(string $apiKey, array $settings = []) {
		$this->privateApiKey = $apiKey;
		$this->settings = $settings;
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
		return new Sharkodlak\Market\Quandl\Connector($this, $this->settings);
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
					$message = "\n" . $styleStart . $message . self::$defaultBashStyleEnd;
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
$adapterClass = "\\Sharkodlak\\Market\\Quandl\\Adapter\\" . $commando['source'];
$futures = new $adapterClass($di, $settings);
$futures->getAndStoreContracts($db);
