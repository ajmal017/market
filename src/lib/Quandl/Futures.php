<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Quandl;

class Futures {
	const DATABASE = 'SRF';
	const IMPORTED_MESSAGE = "Imported %02d%%. ";
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

	public function getContracts(): array {
		return $this->di->connector->getDatabaseMetadata(self::DATABASE);
	}

	private function getAndStoreContractsInnerLoop(\Sharkodlak\Db\Db $db, float $timeLap, array $data, int $i, int $numberOfRows): float {
		$timeCurrent = microtime(true);
		if ($timeCurrent - $timeLap > 1) {
			$msg = sprintf(self::IMPORTED_MESSAGE, 100 * $i / $numberOfRows);
			$this->di->logger->info($msg);
			$timeLap = $timeCurrent;
		}
		$match = \preg_match('~^(?P<exchangeCode>[^_]+)_(?P<instrumentSymbol>.+)(?P<monthCode>[a-z])(?P<year>\d{4})$~i', $data['code'], $matches);
		unset($matches[0], $matches[1], $matches[2], $matches[3], $matches[4]);
		\var_dump($matches);
		$instrumentData = [];
		$instrumentData += $db->adapter->select('exchange_code', ['exchange_id'], ['code' => $matches['exchangeCode']]);
		$instrument = $db->adapter->insertIgnore('instrument', $instrumentData, ['id', 'symbol']);
		\var_dump($instrument);exit;
		return $timeLap;
	}

	public function getAndStoreContracts(\Sharkodlak\Db\Db $db): void {
		$timeLap = microtime(true);
		$contracts = $this->getContracts();
		$numberOfRows = count($contracts);
		foreach($contracts as $i => $row) {
			$timeLap = $this->getAndStoreContractsInnerLoop($db, $timeLap, $row, $i, $numberOfRows);
			\var_dump($numberOfRows, $row);exit;
		}
		$this->di->logger->info(sprintf(self::IMPORTED_MESSAGE, 100) . "\n");
	}

	public function getData(string $code, int $year, int $month): array {
		$dataset = $code . $this->di->futures->getMonthLetter($month) . $year;
		return $this->di->connector->getDataset(self::DATABASE, $dataset);
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
			$msg = sprintf(self::IMPORTED_MESSAGE, 100 * $i / $rows);
			$this->di->logger->info($msg);
			$timeLap = $timeCurrent;
		}
		$db->adapter->insertIgnore('trade_day', $dailyData, ['date', 'contract_id']);
		return $timeLap;
	}

	public function getAndStoreData(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $year, int $month): void {
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
		$this->di->logger->info(sprintf(self::IMPORTED_MESSAGE, 100) . "\n");
	}

	public function translateColumnNames(array $originalColumnNames): array {
		$columnNames = [];
		foreach($originalColumnNames as $key => $columnName) {
			$columnNames[$key] = self::$columnNames[$columnName] ?? $columnName;
		}
		return $columnNames;
	}
}
