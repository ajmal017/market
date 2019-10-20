<?php declare(strict_types=1);

namespace Sharkodlak\Market\Quandl\Adapter;

class Srf extends \Sharkodlak\Market\Quandl\Futures {
	const DATABASE = 'SRF';
	protected static $columnNames = [
		'Date' => 'date',
		'Open' => 'open',
		'High' => 'high',
		'Low' => 'low',
		'Settle' => 'settle',
		'Volume' => 'volume',
		'Prev. Day Open Interest' => 'previous_open_interest',
	];

	protected function getContractCodePattern(): string {
		return '~^(?P<exchangeCode>[^_]+)_(?P<contractCode>(?P<instrumentSymbol>.+)(?P<monthCode>[a-z])(?P<year>\d{4}))$~i';
	}

	protected function getContractNamePattern(): string {
		return '~^(?P<instrumentName>(?:(?P<exchangeCode>[A-Z]+) )?(?P<name>.*)), (?P<month>\w+) (?P<year>\d{4}) \((?P<contractCode>[A-Z]+\d{4})\)$~';
	}

	protected function getContractIdentifier(array $matchesCode, array $matchesName): array {
		return [
			'year' => \intval($matchesCode['year']),
			'month' => $this->di->futures->getMonthNumber($matchesCode['monthCode']),
		];
	}

	protected function getContractUniqueFieldNames(): array {
		return ['instrument_id', 'year', 'month'];
	}

	public function getAndStoreData(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $year, int $month): void {
		$exchangeInstrument = $exchangeCode . '_' . $instrumentSymbol;
		$data = $this->getData($exchangeInstrument, $year, $month);
		$contractId = $this->getContractId($db, $exchangeCode, $instrumentSymbol, $year, $month);
		$this->getAndStoreDataCommon($db, $exchangeCode, $instrumentSymbol, $contractId, $data);
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
}
