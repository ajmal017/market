<?php

const DB_CONNECT = '/etc/webconf/market/connect.powerUser.pgsql';

class COT {
	const READ_ONLY = 'r';
	const URL = 'http://www.cftc.gov/files/dea/history/com_disagg_txt_2018.zip';
	const ZIP = '/tmp/cot.zip';

	public $counter = [
		'exchange' => ['select' => 0, 'insert' => 0],
		'instrument' => ['select' => 0, 'insert' => 0],
		'cot' => ['select' => 0, 'insert' => 0],
	];
	private $exchanges = [];
	private static $fieldNames = [
		"Market_and_Exchange_Names",
		"As_of_Date_In_Form_YYMMDD",
		"Report_Date_as_YYYY-MM-DD",
		"CFTC_Contract_Market_Code",
		"CFTC_Market_Code",
		"CFTC_Region_Code",
		"CFTC_Commodity_Code",
		"Open_Interest_All",
		"Prod_Merc_Positions_Long_All",
		"Prod_Merc_Positions_Short_All",
		"Swap_Positions_Long_All",
		"Swap__Positions_Short_All",
		"Swap__Positions_Spread_All",
		"M_Money_Positions_Long_All",
		"M_Money_Positions_Short_All",
		"M_Money_Positions_Spread_All",
		"Other_Rept_Positions_Long_All",
		"Other_Rept_Positions_Short_All",
		"Other_Rept_Positions_Spread_All",
		"Tot_Rept_Positions_Long_All",
		"Tot_Rept_Positions_Short_All",
		"NonRept_Positions_Long_All",
		"NonRept_Positions_Short_All",
		"Open_Interest_Old",
		"Prod_Merc_Positions_Long_Old",
		"Prod_Merc_Positions_Short_Old",
		"Swap_Positions_Long_Old",
		"Swap__Positions_Short_Old",
		"Swap__Positions_Spread_Old",
		"M_Money_Positions_Long_Old",
		"M_Money_Positions_Short_Old",
		"M_Money_Positions_Spread_Old",
		"Other_Rept_Positions_Long_Old",
		"Other_Rept_Positions_Short_Old",
		"Other_Rept_Positions_Spread_Old",
		"Tot_Rept_Positions_Long_Old",
		"Tot_Rept_Positions_Short_Old",
		"NonRept_Positions_Long_Old",
		"NonRept_Positions_Short_Old",
		"Open_Interest_Other",
		"Prod_Merc_Positions_Long_Other",
		"Prod_Merc_Positions_Short_Other",
		"Swap_Positions_Long_Other",
		"Swap__Positions_Short_Other",
		"Swap__Positions_Spread_Other",
		"M_Money_Positions_Long_Other",
		"M_Money_Positions_Short_Other",
		"M_Money_Positions_Spread_Other",
		"Other_Rept_Positions_Long_Other",
		"Other_Rept_Positions_Short_Other",
		"Other_Rept_Positions_Spread_Other",
		"Tot_Rept_Positions_Long_Other",
		"Tot_Rept_Positions_Short_Other",
		"NonRept_Positions_Long_Other",
		"NonRept_Positions_Short_Other",
		"Change_in_Open_Interest_All",
		"Change_in_Prod_Merc_Long_All",
		"Change_in_Prod_Merc_Short_All",
		"Change_in_Swap_Long_All",
		"Change_in_Swap_Short_All",
		"Change_in_Swap_Spread_All",
		"Change_in_M_Money_Long_All",
		"Change_in_M_Money_Short_All",
		"Change_in_M_Money_Spread_All",
		"Change_in_Other_Rept_Long_All",
		"Change_in_Other_Rept_Short_All",
		"Change_in_Other_Rept_Spread_All",
		"Change_in_Tot_Rept_Long_All",
		"Change_in_Tot_Rept_Short_All",
		"Change_in_NonRept_Long_All",
		"Change_in_NonRept_Short_All",
		"Pct_of_Open_Interest_All",
		"Pct_of_OI_Prod_Merc_Long_All",
		"Pct_of_OI_Prod_Merc_Short_All",
		"Pct_of_OI_Swap_Long_All",
		"Pct_of_OI_Swap_Short_All",
		"Pct_of_OI_Swap_Spread_All",
		"Pct_of_OI_M_Money_Long_All",
		"Pct_of_OI_M_Money_Short_All",
		"Pct_of_OI_M_Money_Spread_All",
		"Pct_of_OI_Other_Rept_Long_All",
		"Pct_of_OI_Other_Rept_Short_All",
		"Pct_of_OI_Other_Rept_Spread_All",
		"Pct_of_OI_Tot_Rept_Long_All",
		"Pct_of_OI_Tot_Rept_Short_All",
		"Pct_of_OI_NonRept_Long_All",
		"Pct_of_OI_NonRept_Short_All",
		"Pct_of_Open_Interest_Old",
		"Pct_of_OI_Prod_Merc_Long_Old",
		"Pct_of_OI_Prod_Merc_Short_Old",
		"Pct_of_OI_Swap_Long_Old",
		"Pct_of_OI_Swap_Short_Old",
		"Pct_of_OI_Swap_Spread_Old",
		"Pct_of_OI_M_Money_Long_Old",
		"Pct_of_OI_M_Money_Short_Old",
		"Pct_of_OI_M_Money_Spread_Old",
		"Pct_of_OI_Other_Rept_Long_Old",
		"Pct_of_OI_Other_Rept_Short_Old",
		"Pct_of_OI_Other_Rept_Spread_Old",
		"Pct_of_OI_Tot_Rept_Long_Old",
		"Pct_of_OI_Tot_Rept_Short_Old",
		"Pct_of_OI_NonRept_Long_Old",
		"Pct_of_OI_NonRept_Short_Old",
		"Pct_of_Open_Interest_Other",
		"Pct_of_OI_Prod_Merc_Long_Other",
		"Pct_of_OI_Prod_Merc_Short_Other",
		"Pct_of_OI_Swap_Long_Other",
		"Pct_of_OI_Swap_Short_Other",
		"Pct_of_OI_Swap_Spread_Other",
		"Pct_of_OI_M_Money_Long_Other",
		"Pct_of_OI_M_Money_Short_Other",
		"Pct_of_OI_M_Money_Spread_Other",
		"Pct_of_OI_Other_Rept_Long_Other",
		"Pct_of_OI_Other_Rept_Short_Other",
		"Pct_of_OI_Other_Rept_Spread_Other",
		"Pct_of_OI_Tot_Rept_Long_Other",
		"Pct_of_OI_Tot_Rept_Short_Other",
		"Pct_of_OI_NonRept_Long_Other",
		"Pct_of_OI_NonRept_Short_Other",
		"Traders_Tot_All",
		"Traders_Prod_Merc_Long_All",
		"Traders_Prod_Merc_Short_All",
		"Traders_Swap_Long_All",
		"Traders_Swap_Short_All",
		"Traders_Swap_Spread_All",
		"Traders_M_Money_Long_All",
		"Traders_M_Money_Short_All",
		"Traders_M_Money_Spread_All",
		"Traders_Other_Rept_Long_All",
		"Traders_Other_Rept_Short_All",
		"Traders_Other_Rept_Spread_All",
		"Traders_Tot_Rept_Long_All",
		"Traders_Tot_Rept_Short_All",
		"Traders_Tot_Old",
		"Traders_Prod_Merc_Long_Old",
		"Traders_Prod_Merc_Short_Old",
		"Traders_Swap_Long_Old",
		"Traders_Swap_Short_Old",
		"Traders_Swap_Spread_Old",
		"Traders_M_Money_Long_Old",
		"Traders_M_Money_Short_Old",
		"Traders_M_Money_Spread_Old",
		"Traders_Other_Rept_Long_Old",
		"Traders_Other_Rept_Short_Old",
		"Traders_Other_Rept_Spread_Old",
		"Traders_Tot_Rept_Long_Old",
		"Traders_Tot_Rept_Short_Old",
		"Traders_Tot_Other",
		"Traders_Prod_Merc_Long_Other",
		"Traders_Prod_Merc_Short_Other",
		"Traders_Swap_Long_Other",
		"Traders_Swap_Short_Other",
		"Traders_Swap_Spread_Other",
		"Traders_M_Money_Long_Other",
		"Traders_M_Money_Short_Other",
		"Traders_M_Money_Spread_Other",
		"Traders_Other_Rept_Long_Other",
		"Traders_Other_Rept_Short_Other",
		"Traders_Other_Rept_Spread_Other",
		"Traders_Tot_Rept_Long_Other",
		"Traders_Tot_Rept_Short_Other",
		"Conc_Gross_LE_4_TDR_Long_All",
		"Conc_Gross_LE_4_TDR_Short_All",
		"Conc_Gross_LE_8_TDR_Long_All",
		"Conc_Gross_LE_8_TDR_Short_All",
		"Conc_Net_LE_4_TDR_Long_All",
		"Conc_Net_LE_4_TDR_Short_All",
		"Conc_Net_LE_8_TDR_Long_All",
		"Conc_Net_LE_8_TDR_Short_All",
		"Conc_Gross_LE_4_TDR_Long_Old",
		"Conc_Gross_LE_4_TDR_Short_Old",
		"Conc_Gross_LE_8_TDR_Long_Old",
		"Conc_Gross_LE_8_TDR_Short_Old",
		"Conc_Net_LE_4_TDR_Long_Old",
		"Conc_Net_LE_4_TDR_Short_Old",
		"Conc_Net_LE_8_TDR_Long_Old",
		"Conc_Net_LE_8_TDR_Short_Old",
		"Conc_Gross_LE_4_TDR_Long_Other",
		"Conc_Gross_LE_4_TDR_Short_Other",
		"Conc_Gross_LE_8_TDR_Long_Other",
		"Conc_Gross_LE_8_TDR_Short_Other",
		"Conc_Net_LE_4_TDR_Long_Other",
		"Conc_Net_LE_4_TDR_Short_Other",
		"Conc_Net_LE_8_TDR_Long_Other",
		"Conc_Net_LE_8_TDR_Short_Other",
		"Contract_Units",
		"CFTC_Contract_Market_Code_Quotes",
		"CFTC_Market_Code_Quotes",
		"CFTC_Commodity_Code_Quotes",
		"CFTC_SubGroup_Code",
		"FutOnly_or_Combined",
	];
	private $instruments = [];
	private $pdo;

	public function __construct(\PDO $pdo) {
		$this->pdo = $pdo;
	}

	public static function copyMissingFile(string $filename = self::ZIP, string $url = self::URL) {
		if (!file_exists($filename)) {
			copy($url, $filename);
		}
		return 'zip://' . $filename . '#annualof.txt';
	}

	public function checkFields(array $fields) {
		$unknownFieldNames = array_diff($fields, self::$fieldNames);
		if (!empty($unknownFieldNames)) {
			print_r($unknownFieldNames);
			throw new \Exception('Unknown field names!');
		}
	}

	public static function filterColumns(array $line, array $filterFieldNames) {
		$fieldNames = array_intersect(self::$fieldNames, $filterFieldNames);
		var_dump($fieldNames);
	}

	private function selectOrInsertReturnsId(string $counterKey, string $select, array $params, string $insert, array $additionalFields = []) {
		++$this->counter[$counterKey]['select'];
		$statement = $this->pdo->prepare($select);
		$success = $statement->execute($params);
		$id = $statement->fetchColumn();
		if ($id === false) {
			$statement = $this->pdo->prepare($insert);
			$success = $statement->execute($params + $additionalFields);
			if ($success) {
				++$this->counter[$counterKey]['insert'];
				$id = $statement->fetchColumn();
			}
		}
		return $id;
	}

	public function getExchangeId(string $exchange) {
		if (!array_key_exists($exchange, $this->exchanges)) {
			$counterKey = 'exchange';
			$select = "SELECT id FROM exchange WHERE name = :exchange";
			$params = ['exchange' => $exchange];
			$insert = "INSERT INTO exchange (name) VALUES (:exchange) RETURNING id";
			$this->exchanges[$exchange] = $this->selectOrInsertReturnsId($counterKey, $select, $params, $insert);
		}
		return $this->exchanges[$exchange];
	}

	public function getInstrumentId(int $exchangeId, string $market, ?string $contractVolume) {
		if (!array_key_exists($market, $this->instruments)) {
			$counterKey = 'instrument';
			$select = "SELECT id, exchange_id, contract_volume FROM instrument WHERE name = :instrument";
			$params = ['instrument' => $market];
			$insert = "INSERT INTO instrument (exchange_id, name, contract_volume) VALUES (:exchangeId, :instrument, :contractVolume) RETURNING id";
			$additionalFields = ['exchangeId' => $exchangeId, 'contractVolume' => $contractVolume];
			$this->instruments[$market] = $this->selectOrInsertReturnsId($counterKey, $select, $params, $insert, $additionalFields);
		}
		return $this->instruments[$market];
	}

	public function processCot(int $instrumentId, string $date, int $hedgersLong, int $hedgersShort, int $swapLong, int $swapShort, int $managedLong, int $managedShort, int $otherLong, int $otherShort) {
		if (!isset($this->cot[$instrumentId][$date])) {
			$counterKey = 'cot';
			$select = "SELECT instrument_id, date FROM cot WHERE instrument_id = :instrumentId AND date = :date";
			$params = ['instrumentId' => $instrumentId, 'date' => $date];
			$insert = "INSERT INTO cot (instrument_id, date, hedgers_long, hedgers_short, swap_long, swap_short, managed_long, managed_short, other_long, other_short)
				VALUES (:instrumentId, :date, :hedgersLong, :hedgersShort, :swapLong, :swapShort, :managedLong, :managedShort, :otherLong, :otherShort) RETURNING instrument_id, date";
			$additionalFields = [
				'hedgersLong' => $hedgersLong,
				'hedgersShort' => $hedgersShort,
				'swapLong' => $swapLong,
				'swapShort' => $swapShort,
				'managedLong' => $managedLong,
				'managedShort' => $managedShort,
				'otherLong' => $otherLong,
				'otherShort' => $otherShort,
			];
			$this->instruments[$market] = $this->selectOrInsertReturnsId($counterKey, $select, $params, $insert, $additionalFields);
		}
		return $this->instruments[$market];
	}

	public static function indexOfFieldName(string $fieldName) {
		return array_search($fieldName, self::$fieldNames);
	}

	public function importFromFile(string $filename) {
		$firstLine = true;
		$fp = fopen($filename, self::READ_ONLY);
		while ($line = fgetcsv($fp)) {
			if ($firstLine) {
				$this->checkFields($line);
				$firstLine = false;
			} else {
				$fieldIndex = $this->indexOfFieldName('Market and Exchange Names');
				[$market, $exchange] = preg_split('~ - (?!.* - )~', $line[$fieldIndex]);
				$exchangeId = $this->getExchangeId($exchange);
				$instrumentId = $this->getInstrumentId($exchangeId, $market, $line[$this->indexOfFieldName('Contract Units')]);
				$cotFields = [
					'Commercial Positions-Long (All)',
					'Commercial Positions-Short (All)',

				];
				$this->filterColumns($line, )
			}
		}
	}
}



$pdo = new \PDO('uri:file://' . DB_CONNECT);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$cot = new COT($pdo);
$filename = $cot->copyMissingFile();
$cot->importFromFile($filename);
foreach ($cot->counter as $table => $counts) {
	printf("%d new rows from %d totaly readed were imported into table %s.\n", $counts['insert'], $counts['select'], $table);
}
