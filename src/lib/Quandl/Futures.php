<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Quandl;

class Futures {
	const DATABASE = 'SRF';
	private $connector;
	private static $columnNames = [
		'Date' => 'date',
		'Open' => 'open',
		'High' => 'high',
		'Low' => 'low',
		'Settle' => 'settle',
		'Volume' => 'volume',
		'Prev. Day Open Interest' => 'previous_open_interest',
	];
	private $futures;

	public function __construct(Connector $connector, \Sharkodlak\Market\Futures $futures) {
		$this->connector = $connector;
		$this->futures = $futures;
	}

	public function getData(string $code, int $year, int $month): array {
		$dataset = $code . $this->futures->getMonthLetter($month) . $year;
		return $this->connector->getData(self::DATABASE, $dataset);
	}

	public function getAndStoreData(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $year, int $month) {
		$data = $this->getData($exchangeCode . '_' . $instrumentSymbol, $year, $month);
		$columnNames = $this->translateColumnNames($data['column_names']);
		$fields = [
			'year' => $year,
			'month' => $month,
			'instrument_id' => $db->query('SELECT id FROM instrument WHERE symbol = :instrument_symbol AND exchange_id = (
					SELECT exchange_id FROM exchange_code WHERE code = :exchange_code
				)')->setParams(['exchange_code' => $exchangeCode, 'instrument_symbol' => $instrumentSymbol]),
		];
		['id' => $contractId] = $db->adapter->insertOrSelect('contract', $fields, ['id'], array_keys($fields));
		foreach ($data['data'] as $i => $dailyData) {
			$dailyData = \array_combine($columnNames, $dailyData);
			$dailyData['contract_id'] = $contractId;
			$db->adapter->insertIgnore('trade_day', $dailyData, ['date', 'contract_id']);
		}
	}

	public function translateColumnNames(array $originalColumnNames): array {
		$columnNames = [];
		foreach($originalColumnNames as $key => $columnName) {
			$columnNames[$key] = self::$columnNames[$columnName] ?? $columnName;
		}
		return $columnNames;
	}
}
