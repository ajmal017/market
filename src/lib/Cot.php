<?php

declare(strict_types=1);

class COT {
	const READ_ONLY = 'r';
	const URL = 'http://www.cftc.gov/files/dea/history/com_disagg_txt_%d.zip';

	public $counter = [
		'exchange' => ['select' => 0, 'insert' => 0],
		'instrument' => ['select' => 0, 'insert' => 0],
		'cot' => ['select' => 0, 'insert' => 0],
	];
	private $exchanges = [];
	private static $fields = [
		"Market_and_Exchange_Names",
		"As_of_Date_In_Form_YYMMDD",
		'date' => "Report_Date_as_YYYY-MM-DD", // method checkFields can modify this value to "Report_Date_as_MM_DD_YYYY"
			// "Report_Date_as_MM_DD_YYYY" is used until 2012
		"CFTC_Contract_Market_Code",
		"CFTC_Market_Code",
		"CFTC_Region_Code",
		"CFTC_Commodity_Code",
		"Open_Interest_All",
		'hedgersLong' => "Prod_Merc_Positions_Long_All",
		'hedgersShort' => "Prod_Merc_Positions_Short_All",
		'swapLong' => "Swap_Positions_Long_All",
		'swapShort' => "Swap__Positions_Short_All",
		'swapSpread' => "Swap__Positions_Spread_All",
		'managedLong' => "M_Money_Positions_Long_All",
		'managedShort' => "M_Money_Positions_Short_All",
		'managedSpread' => "M_Money_Positions_Spread_All",
		'otherLong' => "Other_Rept_Positions_Long_All",
		'otherShort' => "Other_Rept_Positions_Short_All",
		'otherSpread' => "Other_Rept_Positions_Spread_All",
		"Tot_Rept_Positions_Long_All",
		"Tot_Rept_Positions_Short_All",
		'nonReportableLong' => "NonRept_Positions_Long_All",
		'nonReportableShort' => "NonRept_Positions_Short_All",
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
	private static $fieldsTranslation = [];
	private $instruments = [];
	private $pdo;

	public function __construct(\PDO $pdo) {
		$this->pdo = $pdo;
	}

	public static function getUrl(?int $year = null) {
		if (!isset($year)) {
			$year = idate('Y');
		}
		return sprintf(self::URL, $year);
	}

	public static function downloadFileIfMissing(?string $filename = null, ?string $url = null) {
		$url = $url ?: self::getUrl();
		$filename = $filename ?: tempnam(sys_get_temp_dir(), 'cot-') . '.zip';
		if (!file_exists($filename)) {
			copy($url, $filename);
		}
		return $filename;
	}

	public static function extractFile($filename) {
		return 'zip://' . $filename . '#c_year.txt';
	}

	public static function checkFields(array $fields) {
		if (in_array("Report_Date_as_MM_DD_YYYY", $fields)) {
			self::$fields['date'] = "Report_Date_as_MM_DD_YYYY";
		}
		$unknownFieldNames = array_diff($fields, self::$fields);
		if (!empty($unknownFieldNames)) {
			$msg = sprintf('Unknown field names! [%s]', implode(', ', $unknownFieldNames));
			$e = new \Exception($msg);
			$e->data = $unknownFieldNames;
			throw $e;
		}
	}

	public static function arrayChangeKeysToStrings(array $list) {
		$result = [];
		foreach ($list as $key => $value) {
			$result[is_string($key) ? $key : $value] = $value;
		}
		return $result;
	}

	public static function getFieldKeys() {
		if (empty(self::$fieldsTranslation)) {
			self::$fieldsTranslation = self::arrayChangeKeysToStrings(self::$fields);
		}
		return array_keys(self::$fieldsTranslation);
	}

	public static function getNamedFields(array $line) {
		return array_combine(self::getFieldKeys(), $line);
	}

	private function selectOrInsertReturnsId(string $counterName, array $params, string $select, string $insert,
		array $additionalFields = []
	) {
		++$this->counter[$counterName]['select'];
		$statement = $this->pdo->prepare($select);
		$success = $statement->execute($params);
		$id = $statement->fetchColumn();
		if ($id === false) {
			$statement = $this->pdo->prepare($insert);
			$success = $statement->execute($params + $additionalFields);
			if ($success) {
				++$this->counter[$counterName]['insert'];
				$id = $statement->fetchColumn();
			}
		}
		return $id;
	}

	public function getExchangeId(string $exchange) {
		if (!array_key_exists($exchange, $this->exchanges)) {
			$counterName = 'exchange';
			$params = ['exchange' => $exchange];
			$select = "SELECT id FROM exchange WHERE name = :exchange";
			$insert = "INSERT INTO exchange (name) VALUES (:exchange) RETURNING id";
			$this->exchanges[$exchange] = $this->selectOrInsertReturnsId($counterName, $params, $select, $insert);
		}
		return $this->exchanges[$exchange];
	}

	public function getInstrumentId(int $exchangeId, string $instrument, ?string $contractVolume) {
		if (!array_key_exists($instrument, $this->instruments)) {
			$counterName = 'instrument';
			$params = ['instrument' => $instrument];
			$select = "SELECT id, exchange_id, contract_volume FROM instrument WHERE name = :instrument";
			$insert = "INSERT INTO instrument (exchange_id, name, contract_volume) VALUES (:exchangeId, :instrument, :contractVolume) RETURNING id";
			$additionalFields = ['exchangeId' => $exchangeId, 'contractVolume' => $contractVolume];
			$this->instruments[$instrument] = $this->selectOrInsertReturnsId($counterName, $params, $select, $insert, $additionalFields);
		}
		return $this->instruments[$instrument];
	}

	private static function filterAndMapFields(array $fields, array $filterAndMap) {
		$result = [];
		foreach (self::arrayChangeKeysToStrings($filterAndMap) as $filtered => $map) {
			$result[$filtered] = is_callable($map) ? call_user_func($map, $fields[$filtered]) : $fields[$filtered];
		}
		return $result;
	}

	public function processCot(int $instrumentId, string $date, array $fields) {
		if (!isset($this->cot[$instrumentId][$date])) {
			$counterName = 'cot';
			$params = ['instrumentId' => $instrumentId, 'date' => $date];
			$select = "SELECT instrument_id, date FROM cot WHERE instrument_id = :instrumentId AND date = :date";
			$insert = "INSERT INTO cot (instrument_id, date, hedgers_long, hedgers_short, swap_long, swap_short, swap_spread, managed_long, managed_short, managed_spread, other_long, other_short, other_spread, nonreportable_long, nonreportable_short)
				VALUES (:instrumentId, :date, :hedgersLong, :hedgersShort, :swapLong, :swapShort, :swapSpread, :managedLong, :managedShort, :managedSpread, :otherLong, :otherShort, :otherSpread, :nonReportableLong, :nonReportableShort) RETURNING instrument_id, date";
			$negate = function($value) {
				return -$value;
			};
			$additionalFields = self::filterAndMapFields($fields, ['hedgersLong', 'hedgersShort' => $negate,
				'swapLong', 'swapShort' => $negate, 'swapSpread',
				'managedLong', 'managedShort' => $negate, 'managedSpread',
				'otherLong', 'otherShort' => $negate, 'otherSpread',
				'nonReportableLong', 'nonReportableShort' => $negate,
			]);
			$this->cot[$instrumentId][$date] = $this->selectOrInsertReturnsId($counterName, $params, $select, $insert, $additionalFields);
		}
		return $this->cot[$instrumentId][$date];
	}

	private static function getDate(array $fields) {
		if (substr($fields['As_of_Date_In_Form_YYMMDD'], 0, 2) !== substr($fields['date'], 2, 2)
			|| substr($fields['As_of_Date_In_Form_YYMMDD'], 2, 2) !== substr($fields['date'], 5, 2)
			|| substr($fields['As_of_Date_In_Form_YYMMDD'], 4, 2) !== substr($fields['date'], 8, 2)
		) {
			$data = [
				'Report_Date_as_YYYY-MM-DD' => $fields['date'],
				'As_of_Date_In_Form_YYMMDD' => $fields['As_of_Date_In_Form_YYMMDD'],
			];
			$msg = vsprintf("Dates doesn't match each other! '%s' and '%s'", $data);
			$e = new \Exception($msg);
			$e->data = $data;
			throw $e;
		}
		return $fields['date'];
	}

	public function importFromFile(string $filename) {
		$firstLine = true;
		$fp = fopen($filename, self::READ_ONLY);
		while ($line = fgetcsv($fp)) {
			if ($firstLine) {
				self::checkFields($line);
				$firstLine = false;
			} else {
				$fields = self::getNamedFields($line);
				[$market, $exchange] = preg_split('~ - (?!.* - )~', $fields['Market_and_Exchange_Names']);
				$exchangeId = $this->getExchangeId($exchange);
				$instrumentId = $this->getInstrumentId($exchangeId, $market, $fields['Contract_Units']);
				$date = self::getDate($fields);
				$this->processCot($instrumentId, $date, $fields);
			}
		}
	}
}
