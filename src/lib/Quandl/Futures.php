<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Quandl;

abstract class Futures {
	const IMPORTED_MESSAGE = "Imported %02d%% (%d/%d). ";
	protected $di;
	private $loggedContractNames = [];

	public function __construct(Di $di) {
		$this->di = $di;
	}

	public function getAndStoreContracts(\Sharkodlak\Db\Db $db, array $settings = []): void {
		$contracts = $this->getContracts();
		$numberOfRows = count($contracts);
		$this->di->initProgressBar(0, $numberOfRows);
		$settings['batch'] = (int) $settings['batch'] ?? PHP_INT_MAX;
		$settings['skip'] = (int) $settings['skip'] ?? 0;
		foreach ($contracts as $i => $row) {
			$this->di->progressBar->update($i);
			if ($i >= $settings['skip']) {
				if ($i >= $settings['skip'] + $settings['batch']) {
					break;
				}
				try {
					$msg = sprintf("Row %d.", $i);
					$this->di->logger->debug($msg);
					$this->getAndStoreContractsInnerLoop($db, $row, $settings);
				} catch (\Sharkodlak\Exception\HTTPException $e) {
					$this->di->logger->warning($e->getCode() . ': ' . $e->getMessage(), $row);
				}
			}
		}
	}

	public function getContracts(): array {
		$databaseName = $this->getDatabaseName();
		return $this->di->connector->getDatabaseMetadata($databaseName);
	}

	public function getDatabaseName(): string {
		return static::DATABASE;
	}

	private function getAndStoreContractsInnerLoop(\Sharkodlak\Db\Db $db, array $data, array $settings = []): void {
		$contractCodePattern = $this->getContractCodePattern();
		$contractCodeMatch = \preg_match($contractCodePattern, $data['code'], $data['contractCode']);
		if (!$contractCodeMatch) {
			$msg = sprintf('Unknown code format "%s"!', $data['code']);
			$this->di->logger->warning($msg);
		}
		$contractNamePattern = $this->getContractNamePattern();
		$contractNameMatch = \preg_match($contractNamePattern, $data['name'], $data['contractName']);
		if (!$contractNameMatch) {
			$msg = sprintf('Unknown name format "%s"!', $data['name']);
			$this->di->logger->warning($msg);
		}
		if ($contractCodeMatch && $contractNameMatch) {
			$exchangeCode = $this->getExchangeCode($data['contractName']) ?: $data['contractCode']['exchangeCode'];
			$instrumentData = [
				'name' => $data['contractName']['name'],
				'symbol' => $data['contractCode']['instrumentSymbol'],
			];
			if (empty($settings['symbol']) ||
				\strcasecmp($settings['symbol'], $instrumentData['symbol']) == 0
			) {
				$instrumentData['name_lower'] = \strtolower($instrumentData['name']);
				$contractIdentifier = $this->getContractIdentifier($data['contractCode'], $data['contractName']);
				$contractData = $contractIdentifier + [
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
				$uniqueCodeFieldNames = $this->getContractUniqueFieldNames();
				$updateSetFieldNames = \array_diff(array_keys($contractData), $uniqueCodeFieldNames);
				$contract = $db->adapter->upsert(['id'], 'contract', $contractData, $updateSetFieldNames, $uniqueCodeFieldNames);
				$exchange = $db->adapter->select(['main_exchange_code'], 'exchange', ['id' => $instrumentData['exchange_id']]);
				$this->getAndStoreData($db, $exchange['main_exchange_code'], $instrumentData['symbol'], $contractIdentifier, $settings);
			}
		}
	}

	abstract protected function getContractCodePattern(): string;
	abstract protected function getContractNamePattern(): string;

	protected function getExchangeCode(array $contractNameMatches): ?string {
		if (empty($contractNameMatches['exchangeCode'])) {
			if (!in_array($contractNameMatches['instrumentName'], $this->loggedContractNames)) {
				$msg = sprintf('Missing exchange code in "%s"!', $contractNameMatches[0]);
				$this->di->logger->notice($msg);
				$this->loggedContractNames[] = $contractNameMatches['instrumentName'];
			}
			return null;
		}
		return $contractNameMatches['exchangeCode'];
	}

	abstract protected function getContractIdentifier(array $matchesCode, array $matchesName): array;
	abstract protected function getContractUniqueFieldNames(): array;

	public function getAndStoreData(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, array $contractIdentifier, array $settings = []): void {
		$exchangeInstrument = $exchangeCode . '_' . $instrumentSymbol;
		$data = $this->getData($exchangeInstrument, $contractIdentifier);
		$contractId = $this->getContractId($db, $exchangeCode, $instrumentSymbol, $contractIdentifier);
		$this->getAndStoreDataCommon($db, $exchangeCode, $instrumentSymbol, $contractId, $data, $settings);
	}

	public function getData(string $code, array $contractIdentifier): array {
		$database = $this->getDatabase();
		$dataset = $this->getDataset($code, $contractIdentifier);
		return $this->di->connector->getDataset($database, $dataset);
	}

	abstract public function getDatabase(): string;
	abstract public function getDataset(string $code, array $contractIdentifier): string;

	private function getContractId(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, array $contractIdentifier): int {
		$fields = $contractIdentifier + [
			'instrument_id' => $db->query('SELECT id FROM instrument WHERE symbol = :instrument_symbol AND exchange_id IN (
					SELECT exchange_id FROM exchange WHERE main_exchange_code = :exchange_code
				)')->setParams(['exchange_code' => $exchangeCode, 'instrument_symbol' => $instrumentSymbol]),
		];
		['id' => $contractId] = $db->adapter->insertOrSelect(['id'], 'contract', $fields, array_keys($fields));
		return $contractId;
	}

	public function getAndStoreDataCommon(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $contractId, array $data, array $settings = []): void {
		$timeLap = \microtime(true);
		$numberOfRows = \count($data['data']);
		$columnNames = $this->translateColumnNames($data['column_names']);
		$query = "SELECT MAX(date) AS max_date FROM trade_day WHERE contract_id = :contractId";
		$saved = $db->adapter->query($query, ['contractId' => $contractId]);
		foreach ($data['data'] as $i => $dailyData) {
			$dailyData = \array_combine($columnNames, $dailyData);
			$dailyData['contract_id'] = $contractId;
			if (!empty($settings['reimport']) || $dailyData['date'] > $saved['max_date']) {
				$timeLap = $this->getAndStoreDataInnerLoop($db, $timeLap, $numberOfRows, ++$i, $dailyData);
			}
		}
		$this->di->logger->info(\sprintf(self::IMPORTED_MESSAGE, 100, $i, $numberOfRows) . "\n");
	}

	public function translateColumnNames(array $originalColumnNames): array {
		$columnNames = [];
		foreach ($originalColumnNames as $key => $columnName) {
			$columnNames[$key] = static::$columnNames[$columnName] ?? $columnName;
		}
		return $columnNames;
	}

	private function getAndStoreDataInnerLoop(\Sharkodlak\Db\Db $db, float $timeLap, int $numberOfRows, int $i, array $dailyData): float {
		$timeCurrent = \microtime(true);
		if ($timeCurrent - $timeLap > 1) {
			$msg = \sprintf(self::IMPORTED_MESSAGE, 100 * $i / $numberOfRows, $i, $numberOfRows);
			$this->di->logger->info($msg);
			$timeLap = $timeCurrent;
		}
		$db->adapter->insertIgnore(['date', 'contract_id'], 'trade_day', $dailyData);
		return $timeLap;
	}
}
