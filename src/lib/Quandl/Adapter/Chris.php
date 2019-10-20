<?php declare(strict_types=1);

namespace Sharkodlak\Market\Quandl\Adapter;

class Chris extends \Sharkodlak\Market\Quandl\Futures {
	const DATABASE = 'CHRIS';
	protected static $columnNames = [
		'Date' => 'date',
		'Open' => 'open',
		'High' => 'high',
		'Low' => 'low',
		'Settle' => 'settle',
		'Previous Settlement' => 'previous_settlement',
		'Change' => 'change',
		'Wave' => 'wave',
		'Volume' => 'volume',
		'Open Interest' => 'open_interest',
		'Prev. Day Open Interest' => 'previous_open_interest',
		'Previous Day Open Interest' => 'previous_open_interest',
		'EFP Volume' => 'efp_volume',
		'EFS Volume' => 'efs_volume',
		'Block Volume' => 'block_volume',
		'Last' => 'last',
	];

	protected function getContractCodePattern(): string {
		return '~^(?P<exchangeCode>[^_]+)_(?P<contractCode>(?P<instrumentSymbol>.+)(?P<depth>\d))$~i';
	}

	protected function getContractNamePattern(): string {
		return '~^(?P<name>.+?), (?P<meta>Continuous Contract #(?P<depth>\d+).*)$~';
	}

	protected function getContractIdentifier(array $matchesCode, array $matchesName): array {
		return [
			'depth' => \intval($matchesName['depth'] ?? $matchesCode['depth']),
		];
	}

	protected function getContractUniqueFieldNames(): array {
		return ['instrument_id', 'depth'];
	}

	protected function getExchangeCode(array $contractNameMatches): ?string {
		return null;
	}

	public function getAndStoreData(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $depth): void {
		$exchangeInstrument = $exchangeCode . '_' . $instrumentSymbol;
		$data = $this->getData($exchangeInstrument, $depth);
		$contractId = $this->getContractId($db, $exchangeCode, $instrumentSymbol, $depth);
		$this->getAndStoreDataCommon($db, $exchangeCode, $instrumentSymbol, $contractId, $data);
	}

	public function getData(string $code, int $depth): array {
		$dataset = $code . $depth;
		return $this->di->connector->getDataset(self::DATABASE, $dataset);
	}

	private function getContractId(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $depth): int {
		$fields = [
			'depth' => $depth,
			'instrument_id' => $db->query('SELECT id FROM instrument WHERE symbol = :instrument_symbol AND exchange_id IN (
					SELECT exchange_id FROM exchange WHERE main_exchange_code = :exchange_code
				)')->setParams(['exchange_code' => $exchangeCode, 'instrument_symbol' => $instrumentSymbol]),
		];
		['id' => $contractId] = $db->adapter->insertOrSelect(['id'], 'contract', $fields, array_keys($fields));
		return $contractId;
	}
}
