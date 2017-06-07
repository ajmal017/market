<?php

const DB_CONNECT = '/etc/webconf/market/connect.pgsql';
$pdo = new \PDO('uri:file://' . DB_CONNECT);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$sql = 'SELECT exchange.name AS exchange_name, instrument.* FROM exchange JOIN instrument ON instrument.exchange_id = exchange.id ORDER BY exchange.name, instrument.name';
$result = $pdo->query($sql);
while ($row = $result->fetch(\PDO::FETCH_ASSOC)) {
	var_dump($row);
}
