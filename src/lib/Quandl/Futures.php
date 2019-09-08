<?php

declare(strict_types=1);
namespace Quandl;
require_once(__DIR__ . '/Connector.php');
require_once(__DIR__ . '/../Futures.php');

class Futures {
	const DATABASE = 'SRF';
	private $connector;
	private $futures;

	public function __construct(Connector $connector, \Futures $futures) {
		$this->connector = $connector;
		$this->futures = $futures;
	}

	public function getData(string $code, int $year, int $month): array {
		$dataset = $code . $this->futures->getMonthLetter($month) . $year;
		return $this->connector->getData(self::DATABASE, $dataset);
	}

	public function getAndStoreData(\PDO $pdo, string $exchange, string $instrumentCode, int $year, int $month) {
		$data = $this->getData($exchange . '_' . $instrumentCode, $year, $month);
		$params = [
			'exchange' => $exchange,
			'instrumentCode' => $instrumentCode,
			'year' => $year,
			'month' => $month,
		];
		$select = 'SELECT id FROM contract WHERE instrument_id = (
				SELECT id FROM instrument WHERE code = :instrumentCode AND exchange_id = (
					SELECT id FROM exchange WHERE code = :exchange
				)
			) AND year = :year AND month = :month';
		$statement = $pdo->prepare($select);
		$success = $statement->execute($params);
		$id = $statement->fetchColumn();
		\var_dump($data, $success, $id);exit();
		$insert = 'INSERT INTO contract (instrument_id, year, month) VALUES (:instrument_id, :year, :month) RETURNING id';
		foreach ($data['data'] as $i => $dailyData) {
			$dailyData = \array_combine($data['column_names'], $dailyData);
			$insert = 'INSERT INTO ';
		}
	}
}
