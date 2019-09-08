<?php

declare(strict_types=1);
require_once(__DIR__ . '/../lib/Quandl/Futures.php');

const DB_CONNECT = '/etc/webconf/market/connect.powerUser.pgsql';
const QUANDL_API_KEY = '/etc/webconf/quandl.api.key';

$pdo = new \PDO('uri:file://' . DB_CONNECT);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$apiKey = trim(file_get_contents(QUANDL_API_KEY));
$connector = new \Quandl\Connector($apiKey);
$futures = new \Quandl\Futures($connector, new \Futures());
$data = $futures->getAndStoreData($pdo, 'ICE', 'CC', 2016, 3);
