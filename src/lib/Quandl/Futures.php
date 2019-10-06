<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Quandl;

class Futures {
	const DATABASE = 'SRF';
	const IMPORTED_MESSAGE = "Imported %02d%% (%d/%d). ";
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
	private $loggedContractNames = [];

	public function __construct(Di $di) {
		$this->di = $di;
	}

	public function getAndStoreContracts(\Sharkodlak\Db\Db $db, int $skip = 0): void {
		$contracts = $this->getContracts();
		$numberOfRows = count($contracts);
		$this->di->initProgressBar(0, $numberOfRows);
		foreach ($contracts as $i => $row) {
			if ($i >= $skip) {
				try {
					$this->getAndStoreContractsInnerLoop($db, $row);
				} catch (\Sharkodlak\Exception\HTTPException $e) {
					$this->di->logger->warning($e->getCode() . ': ' . $e->getMessage(), $row);
					// Log HTTP status, continue with next contract
				}
			}
			$this->di->progressBar->update($i);
		}
	}

	public function getContracts(): array {
		return $this->di->connector->getDatabaseMetadata(self::DATABASE);
	}

	private function getAndStoreContractsInnerLoop(\Sharkodlak\Db\Db $db, array $data): void {
		$contractCodeMatch = \preg_match('~^(?P<exchangeCode>[^_]+)_(?P<contractCode>(?P<instrumentSymbol>.+)(?P<monthCode>[a-z])(?P<year>\d{4}))$~i', $data['code'], $data['contractCode']);
		if (!$contractCodeMatch) {
			$msg = sprintf('Unknown code format "%s"!', $data['code']);
			$this->di->logger->warning($msg);
		}
		$contractNameMatch = \preg_match('~^(?P<instrumentName>(?:(?P<exchangeCode>[A-Z]+) )?(?P<name>.*)), (?P<month>\w+) (?P<year>\d{4}) \((?P<contractCode>[A-Z]+\d{4})\)$~', $data['name'], $data['contractName']);
		if (!$contractNameMatch) {
			$msg = sprintf('Unknown name format "%s"!', $data['name']);
			$this->di->logger->warning($msg);
		}
		if ($contractCodeMatch && $contractNameMatch) {
			$this->checkIfExchangeCodeIsMissing($data['contractName']['exchangeCode'], $data['contractName']);
			$exchangeCode = $data['contractName']['exchangeCode'] ?: $data['contractCode']['exchangeCode'];
			$instrumentData = [
				'name' => $data['contractName']['name'],
				'symbol' => $data['contractCode']['instrumentSymbol'],
			];
			$instrumentData['name_lower'] = \strtolower($instrumentData['name']);
			$contractData = [
				'year' => \intval($data['contractCode']['year']),
				'month' => $this->di->futures->getMonthNumber($data['contractCode']['monthCode']),
				'description' => $data['description'],
				'refreshed_at' => $data['refreshed_at'],
				'from_date' => $data['from_date'],
				'to_date' => $data['to_date'],
			];
			$instrumentData += $db->adapter->select(['exchange_id'], 'exchange_code', ['code' => $exchangeCode]);
			$instrument = $db->adapter->select(['id'], 'instrument', ['symbol' => $data['contractCode']['instrumentSymbol']]);
			if ($instrument === null) {
				$instrument = $db->adapter->upsert(['id'], 'instrument', $instrumentData, ['symbol'], ['name_lower', 'exchange_id']);
			}
			$contractData['instrument_id'] = $instrument['id'];
			$uniqueCodeFieldNames = ['instrument_id', 'year', 'month'];
			$updateSetFieldNames = \array_diff(array_keys($contractData), $uniqueCodeFieldNames);
			$contract = $db->adapter->upsert(['id'], 'contract', $contractData, $updateSetFieldNames, $uniqueCodeFieldNames);
			$exchange = $db->adapter->select(['main_exchange_code'], 'exchange', ['id' => $instrumentData['exchange_id']]);
			$this->getAndStoreData($db, $exchange['main_exchange_code'], $instrumentData['symbol'], $contractData['year'], $contractData['month']);
		}
	}

	private function checkIfExchangeCodeIsMissing(?string $exchangeCode, array $contractName): bool {
		if (empty($exchangeCode)) {
			if (!in_array($contractName['instrumentName'], $this->loggedContractNames)) {
				$msg = sprintf('Missing exchange code in "%s"!', $contractName[0]);
				$this->di->logger->notice($msg);
				$this->loggedContractNames[] = $contractName['instrumentName'];
			}
			return false;
		}
		return true;
	}

	public function getData(string $code, int $year, int $month): array {
		$dataset = $code . $this->di->futures->getMonthLetter($month) . $year;
		return $this->di->connector->getDataset(self::DATABASE, $dataset);
	}

	private function getContractId(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $year, int $month): int {
		$fields = [
			'year' => $year,
			'month' => $month,
			'instrument_id' => $db->query('SELECT id FROM instrument WHERE symbol = :instrument_symbol AND exchange_id IN (
					SELECT exchange_id FROM exchange WHERE main_exchange_code = :exchange_code
				)')->setParams(['exchange_code' => $exchangeCode, 'instrument_symbol' => $instrumentSymbol]),
		];
		['id' => $contractId] = $db->adapter->insertOrSelect(['id'], 'contract', $fields, array_keys($fields));
		return $contractId;
	}

	private function getAndStoreDataInnerLoop(\Sharkodlak\Db\Db $db, float $timeLap, int $numberOfRows, int $i, array $dailyData): float {
		$timeCurrent = microtime(true);
		if ($timeCurrent - $timeLap > 1) {
			$msg = sprintf(self::IMPORTED_MESSAGE, 100 * $i / $numberOfRows, $i, $numberOfRows);
			$this->di->logger->info($msg);
			$timeLap = $timeCurrent;
		}
		$db->adapter->insertIgnore(['date', 'contract_id'], 'trade_day', $dailyData);
		return $timeLap;
	}

	public function getAndStoreData(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $year, int $month): void {
		$timeLap = microtime(true);
		$data = $this->getData($exchangeCode . '_' . $instrumentSymbol, $year, $month);
		$numberOfRows = count($data['data']);
		$columnNames = $this->translateColumnNames($data['column_names']);
		$contractId = $this->getContractId($db, $exchangeCode, $instrumentSymbol, $year, $month);
		foreach ($data['data'] as $i => $dailyData) {
			$dailyData = \array_combine($columnNames, $dailyData);
			$dailyData['contract_id'] = $contractId;
			$timeLap = $this->getAndStoreDataInnerLoop($db, $timeLap, $numberOfRows, ++$i, $dailyData);
		}
		$this->di->logger->info(sprintf(self::IMPORTED_MESSAGE, 100, $i, $numberOfRows) . "\n");
	}

	public function translateColumnNames(array $originalColumnNames): array {
		$columnNames = [];
		foreach ($originalColumnNames as $key => $columnName) {
			$columnNames[$key] = self::$columnNames[$columnName] ?? $columnName;
		}
		return $columnNames;
	}
}
