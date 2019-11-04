<?php declare(strict_types=1);

namespace Sharkodlak\Market\Quandl\Adapter;

class Chris extends \Sharkodlak\Market\Quandl\Futures {
	const DATABASE = 'CHRIS';
	protected static $columnNames = [
		'Date' => 'date',
		'Open' => 'open',
		'Open Price' => 'open',
		'High' => 'high',
		'High Price' => 'high',
		'Low' => 'low',
		'Low Price' => 'low',
		'Settle' => 'settle',
		'Settlement Price' => 'settle',
		'Previous Settlement' => 'previous_settlement',
		'Pre Settlement' => 'previous_settlement',
		'Prev. Day Settlement Price' => 'previous_settlement',
		'Close' => 'close',
		'Last' => 'close',
		'Last Close Price' => 'close',
		'Last Price' => 'close',
		'Last Traded' => 'close',
		'Change' => 'change',
		'Net Change' => 'change',
		'Previous Change' => 'previous_change',
		'Wave' => 'wave',
		'Volume' => 'volume',
		'Open Interest' => 'open_interest',
		'Prev. Day Open Interest' => 'previous_open_interest',
		'Previous Day Open Interest' => 'previous_open_interest',
		'EFP Volume' => 'efp_volume',
		'EFS Volume' => 'efs_volume',
		'Block Volume' => 'block_volume',
		'Bid' => 'bid',
		'Bid Price' => 'bid',
		'Ask' => 'ask',
		'Ask Price' => 'ask',
		'Bid Size' => 'bid_size',
		'Ask Size' => 'ask_size',
		'Total Value' => 'total',
		'Nb. Trades' => 'trades',
		'Implied Volatility' => 'implied_volatility',
		'Morning 1st Session' => 'morning_1st_session',
		'Morning 2nd Session' => 'morning_2nd_session',
		'Morning 3rd Session' => 'morning_3rd_session',
		'Afternoon 1st Session' => 'afternoon_1st_session',
		'Afternoon 2nd Session' => 'afternoon_2nd_session',
		'Afternoon 3rd Session' => 'afternoon_3rd_session',
		'Morning 2nd Session' => 'morning_2nd_session',
	];

	protected function getContractCodePattern(): string {
		return '~^(?P<exchangeCode>[^_]+)_(?P<contractCode>(?P<instrumentSymbol>.+?)(?P<depth>\d+))$~i';
	}

	protected function getContractNamePattern(): string {
		return '~^(?P<name>.+?), (?P<meta>Continuous Contract(?: #(?P<depth>\d+))?.*)$~';
	}

	protected function getExchangeCode(array $contractNameMatches): ?string {
		return null;
	}

	protected function getContractIdentifier(array $matchesCode, array $matchesName): array {
		return [
			'depth' => \intval($matchesName['depth'] ?? $matchesCode['depth']),
		];
	}

	protected function getContractUniqueFieldNames(): array {
		return ['instrument_id', 'depth'];
	}

	public function getDatabase(): string {
		return self::DATABASE;
	}

	public function getDataset(string $code, array $contractIdentifier): string {
		return $code . $contractIdentifier['depth'];
	}
}
