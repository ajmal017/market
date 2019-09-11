<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Quandl;

class Futures {
	const DATABASE = 'SRF';
	private $connector;
	private $futures;

	public function __construct(Connector $connector, \Sharkodlak\Market\Futures $futures) {
		$this->connector = $connector;
		$this->futures = $futures;
	}

	public function getData(string $code, int $year, int $month): array {
		$dataset = $code . $this->futures->getMonthLetter($month) . $year;
		return $this->connector->getData(self::DATABASE, $dataset);
	}

	public function getAndStoreData(\Sharkodlak\Db\Db $db, string $exchangeCode, string $instrumentSymbol, int $year, int $month) {
		$data = $this->getData($exchangeCode . '_' . $instrumentSymbol, $year, $month);
		$yearMonth = ['year' => $year, 'month' => $month];
		$values = ['instrument_id' => $db->query(
				'SELECT id FROM instrument WHERE code = :instrument_symbol AND exchange_id = %s',
				$db->query('SELECT id FROM exchange WHERE code = :exchange_code')
			)] + $yearMonth;
		$params = ['exchange_code' => $exchangeCode, 'instrument_symbol' => $instrumentSymbol] + $yearMonth;
		['id' => $contractId] = $db->adapter->insertOrSelect('contract', $fields, ['id'], array_keys($fields), $params);
		\var_dump($contractId);exit();
		foreach ($data['data'] as $i => $dailyData) {
			$dailyData = \array_combine($data['column_names'], $dailyData);
			$insert = 'INSERT INTO ';
		}
	}
}
