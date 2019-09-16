<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

const DB_CONNECT = '/etc/webconf/market/connect.powerUser.pgsql';
const QUANDL_API_KEY = '/etc/webconf/quandl.api.key';

$apiKey = trim(file_get_contents(QUANDL_API_KEY));
$di = new class($apiKey) implements \Sharkodlak\Db\Di, \Sharkodlak\Market\Quandl\Di {
	private $apiKey;
	private $services = [];
	public function __construct($apiKey) {
		$this->apiKey = $apiKey;
	}
	public function __get($name) {
		if (!isset($services[$name])) {
			$method = "get$name";
			$services[$name] = $this->$method();
		}
		return $services[$name];
	}
	public function getQuery(...$args): \Sharkodlak\Db\Queries\Query {
		return new \Sharkodlak\Db\Queries\Query(...$args);
	}
	public function getConnector(): \Sharkodlak\Market\Quandl\Connector {
		return new Sharkodlak\Market\Quandl\Connector($this->apiKey);
	}
	public function getFutures(): \Sharkodlak\Market\Futures {
		return new Sharkodlak\Market\Futures();
	}
	public function getLogger(): \Psr\Log\LoggerInterface {
		$logger = new class extends \Psr\Log\AbstractLogger {
			public function log($level, $message, array $context = []) {
				echo $message;
			}
		};
		return $logger;
	}
};
$pdo = new \PDO('uri:file://' . DB_CONNECT);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$dbAdapter = new \Sharkodlak\Db\Adapter\Postgres($pdo);
$db = new \Sharkodlak\Db\Db($di, $dbAdapter);
$futures = new Sharkodlak\Market\Quandl\Futures($di);
$data = $futures->getAndStoreData($db, 'ICE', 'CC', 2016, 3);
