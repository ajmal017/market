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

	public function getAndStoreContracts(\Sharkodlak\Db\Db $db, int $skip = 0): void {
		$contracts = $this->getContracts();
		$numberOfRows = count($contracts);
		$this->di->initProgressBar(0, $numberOfRows);
		foreach ($contracts as $i => $row) {
			if ($i >= $skip) {
				try {
					$msg = sprintf("Row %d.", $i);
					$this->di->logger->debug($msg);
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
		$databaseName = $this->getDatabaseName();
		return $this->di->connector->getDatabaseMetadata($databaseName);
	}

	public function getDatabaseName(): string {
		return static::DATABASE;
	}

	private function getAndStoreContractsInnerLoop(\Sharkodlak\Db\Db $db, array $data): void {
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
			$this->getAndStoreData($db, $exchange['main_exchange_code'], $instrumentData['symbol'], ...\array_values($contractIdentifier));
		}
	}

	abstract protected function getContractCodePattern(): string;
	abstract protected function getContractNamePattern(): string;
	abstract protected function getContractIdentifier(array $matchesCode, array $matchesName): array;
	abstract protected function getContractUniqueFieldNames(): array;

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

	public function getAndStoreDataCommon(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $contractId, array $data): void {
		$timeLap = microtime(true);
		$numberOfRows = count($data['data']);
		$columnNames = $this->translateColumnNames($data['column_names']);
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
			$columnNames[$key] = static::$columnNames[$columnName] ?? $columnName;
		}
		return $columnNames;
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
}
