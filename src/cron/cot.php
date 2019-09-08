<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

const DB_CONNECT = '/etc/webconf/market/connect.powerUser.pgsql';
define('YEAR', isset($argv[1]) ? $argv[1] : null);

$pdo = new \PDO('uri:file://' . DB_CONNECT);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$dbAdapter = new \Sharkodlak\Db\Adapter\Postgres($pdo);
$cot = new \Sharkodlak\Market\Cot($dbAdapter);
$filename = YEAR === null ?
	$cot->downloadFileIfMissing() :
	'/vagrant/data/cot/com_disagg_txt_' . YEAR . '.zip';
$filename = $cot->extractFile($filename);
$cot->importFromFile($filename);
foreach ($cot->counter as $table => $counts) {
	printf("Table '%s': %d rows readed, %d rows added.\n", $table, $counts['select'], $counts['insert']);
}
