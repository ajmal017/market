<?php

declare(strict_types=1);
require_once(__DIR__ . '/../lib/Cot.php');

const DB_CONNECT = '/etc/webconf/market/connect.powerUser.pgsql';
define('YEAR', isset($argv[1]) ? $argv[1] : null);

$pdo = new \PDO('uri:file://' . DB_CONNECT);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$cot = new COT($pdo);
$filename = YEAR === null ?
	$cot->downloadFileIfMissing() :
	'/vagrant/data/cot/com_disagg_txt_' . YEAR . '.zip';
$filename = $cot->extractFile($filename);
$cot->importFromFile($filename);
foreach ($cot->counter as $table => $counts) {
	printf("Table '%s': %d rows readed, %d rows added.\n", $table, $counts['select'], $counts['insert']);
}
