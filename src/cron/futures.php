<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

const DB_CONNECT = '/etc/webconf/market/connect.powerUser.pgsql';
const QUANDL_API_KEY = '/etc/webconf/quandl.api.key';

$di = new class implements \Sharkodlak\Db\Di {
	public function getQuery(...$args): \Sharkodlak\Db\Queries\Query {
		return new \Sharkodlak\Db\Queries\Query(...$args);
	}
};
$pdo = new \PDO('uri:file://' . DB_CONNECT);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$dbAdapter = new \Sharkodlak\Db\Adapter\Postgres($pdo);
$db = new \Sharkodlak\Db\Db($di, $dbAdapter);
$apiKey = trim(file_get_contents(QUANDL_API_KEY));
$connector = new Sharkodlak\Market\Quandl\Connector($apiKey);
$futuresHelper = new Sharkodlak\Market\Futures();
$futures = new Sharkodlak\Market\Quandl\Futures($connector, $futuresHelper);
$data = $futures->getAndStoreData($db, 'ICE', 'CC', 2016, 3);
