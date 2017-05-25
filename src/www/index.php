<?php

const DB_CONNECT = '/etc/webconf/market/connect.pgsql';
$pdo = new \PDO('uri:file://' . DB_CONNECT);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$sql = 'SELECT * FROM exchange ORDER BY name';
$result = $pdo->query($sql);
while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
	var_dump($row);
}
