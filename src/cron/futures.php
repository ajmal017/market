<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

define('SKIP', $argv[1] && is_numeric($argv[1]) ? intval($argv[1]) : 0);
const DB_CONNECT = '/etc/webconf/market/connect.powerUser.pgsql';
const QUANDL_API_KEY = '/etc/webconf/quandl.api.key';

$apiKey = trim(file_get_contents(QUANDL_API_KEY));
$di = new class($apiKey) implements \Sharkodlak\Db\Di, \Sharkodlak\Market\Quandl\Di {
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
			public function log($level, $message, array $context = []) {
				$styleEnd = "\e[0m\n";
				switch ($level) {
					case \Psr\Log\LogLevel::EMERGENCY:
					case \Psr\Log\LogLevel::ALERT:
					case \Psr\Log\LogLevel::CRITICAL:
					case \Psr\Log\LogLevel::ERROR:
						$style = "\e[91m";
					break;
					case \Psr\Log\LogLevel::WARNING:
						$style = "\e[33m";
					break;
					case \Psr\Log\LogLevel::NOTICE:
						$style = "\x0D\e[93m";
						$styleEnd = "\e[0m";
					break;
					default:
						$style = "\e[2m";
				}
				fputs(STDERR, "$style$message");
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
$dbAdapter = new \Sharkodlak\Db\Adapter\Postgres($pdo);
$db = new \Sharkodlak\Db\Db($di, $dbAdapter);
$futures = new Sharkodlak\Market\Quandl\Futures($di);
$futures->getAndStoreContracts($db, SKIP);
$futures->getAndStoreData($db, 'ICE', 'CC', 2016, 3);
