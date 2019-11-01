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

	public function getDatabase(): string {
		return self::DATABASE;
	}

	public function getDataset(string $code, array $contractIdentifier): string {
		return $code . $this->di->futures->getMonthLetter($contractIdentifier['month']) . $contractIdentifier['year'];
	}
}
