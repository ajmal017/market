<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Quandl;

class Futures {
	const DATABASE = 'SRF';
	private $di;
	private static $columnNames = [
		'Date' => 'date',
		'Open' => 'open',
		'High' => 'high',
		'Low' => 'low',
		'Settle' => 'settle',
		'Volume' => 'volume',
		'Prev. Day Open Interest' => 'previous_open_interest',
	];

	public function __construct(Di $di) {
		$this->di = $di;
	}

	public function getData(string $code, int $year, int $month): array {
		$dataset = $code . $this->di->futures->getMonthLetter($month) . $year;
		return $this->di->connector->getData(self::DATABASE, $dataset);
	}

	public function getAndStoreData(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $year, int $month) {
		$timeLap = microtime(true);
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
		$rows = count($data['data']);
		foreach ($data['data'] as $i => $dailyData) {
			$timeCurrent = microtime(true);
			if ($timeCurrent - $timeLap > 1) {
				$msg = sprintf("\x0D%02d%%", 100 * $i / $rows);
				$this->di->logger->info($msg);
				$timeLap = $timeCurrent;
			}
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
