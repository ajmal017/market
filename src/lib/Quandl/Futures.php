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

	private function getContractId(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $year, int $month): int {
		$fields = [
			'year' => $year,
			'month' => $month,
			'instrument_id' => $db->query('SELECT id FROM instrument WHERE symbol = :instrument_symbol AND exchange_id = (
					SELECT exchange_id FROM exchange_code WHERE code = :exchange_code
				)')->setParams(['exchange_code' => $exchangeCode, 'instrument_symbol' => $instrumentSymbol]),
		];
		['id' => $contractId] = $db->adapter->insertOrSelect('contract', $fields, ['id'], array_keys($fields));
		return $contractId;
	}

	private function getAndStoreDataInnerLoop(\Sharkodlak\Db\Db $db, float $timeLap, int $rows, int $i, array $dailyData): float {
		$timeCurrent = microtime(true);
		if ($timeCurrent - $timeLap > 1) {
			$msg = sprintf("\x0DImported %02d%%", 100 * $i / $rows);
			$this->di->logger->info($msg);
			$timeLap = $timeCurrent;
		}
		$db->adapter->insertIgnore('trade_day', $dailyData, ['date', 'contract_id']);
		return $timeLap;
	}

	public function getAndStoreData(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $year, int $month) {
		$timeLap = microtime(true);
		$data = $this->getData($exchangeCode . '_' . $instrumentSymbol, $year, $month);
		$rows = count($data['data']);
		$columnNames = $this->translateColumnNames($data['column_names']);
		$contractId = $this->getContractId($db, $exchangeCode, $instrumentSymbol, $year, $month);
		foreach ($data['data'] as $i => $dailyData) {
			$dailyData = \array_combine($columnNames, $dailyData);
			$dailyData['contract_id'] = $contractId;
			$timeLap = $this->getAndStoreDataInnerLoop($db, $timeLap, $rows, $i, $dailyData);
		}
		$this->di->logger->info("\x0DImported 100%\n");
	}

	public function translateColumnNames(array $originalColumnNames): array {
		$columnNames = [];
		foreach($originalColumnNames as $key => $columnName) {
			$columnNames[$key] = self::$columnNames[$columnName] ?? $columnName;
		}
		return $columnNames;
	}
}
