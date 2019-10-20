<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

define('SKIP', isset($argv[1]) && is_numeric($argv[1]) ? intval($argv[1]) : 0);
define('LOG_LEVEL', $argv[2] ?? 'debug');
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
$futures->getAndStoreContracts($db, SKIP);
$futures->getAndStoreData($db, 'ICE', 'CC', 2016, 3);
