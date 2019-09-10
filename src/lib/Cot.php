<?php

declare(strict_types=1);
namespace Sharkodlak\Market;

class Cot {
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
		'exchangeCode' => "CFTC_Market_Code",
		"CFTC_Region_Code",
		"CFTC_Commodity_Code",
		'open_interest' => "Open_Interest_All",
		'hedgers_long' => "Prod_Merc_Positions_Long_All",
		'hedgers_short' => "Prod_Merc_Positions_Short_All",
		'swap_long' => "Swap_Positions_Long_All",
		'swap_short' => "Swap__Positions_Short_All",
		'swap_spread' => "Swap__Positions_Spread_All",
		'managed_long' => "M_Money_Positions_Long_All",
		'managed_short' => "M_Money_Positions_Short_All",
		'managed_spread' => "M_Money_Positions_Spread_All",
		'other_long' => "Other_Rept_Positions_Long_All",
		'other_short' => "Other_Rept_Positions_Short_All",
		'other_spread' => "Other_Rept_Positions_Spread_All",
		"Tot_Rept_Positions_Long_All",
		"Tot_Rept_Positions_Short_All",
		'nonreportable_long' => "NonRept_Positions_Long_All",
		'nonreportable_short' => "NonRept_Positions_Short_All",
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
	private $dbAdapter;

	public function __construct(\Sharkodlak\Db\Adapter\Base $dbAdapter) {
		$this->dbAdapter = $dbAdapter;
	}

	public static function getUrl(?int $year = null): string {
		if (!isset($year)) {
			$year = idate('Y');
		}
		return sprintf(self::URL, $year);
	}

	public static function downloadFileIfMissing(?string $filename = null, ?string $url = null): string {
		$url = $url ?: self::getUrl();
		$filename = $filename ?: tempnam(sys_get_temp_dir(), 'cot-') . '.zip';
		if (!file_exists($filename)) {
			copy($url, $filename);
		}
		return $filename;
	}

	public static function extractFile($filename): string {
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

	public static function arrayChangeKeysToStrings(array $list): array {
		$result = [];
		foreach ($list as $key => $value) {
			$result[is_string($key) ? $key : $value] = $value;
		}
		return $result;
	}

	public static function getFieldKeys(): array {
		if (empty(self::$fieldsTranslation)) {
			self::$fieldsTranslation = self::arrayChangeKeysToStrings(self::$fields);
		}
		return array_keys(self::$fieldsTranslation);
	}

	public static function getNamedFields(array $line): array {
		return array_combine(self::getFieldKeys(), $line);
	}

	private function selectOrInsertReturnsId(string $counterName, array $params, string $select, string $insert,
		array $additionalFields = []
	): int {
		++$this->counter[$counterName]['select'];
		$statement = $this->dbAdapter->pdo->prepare($select);
		$success = $statement->execute($params);
		$id = $statement->fetchColumn();
		if ($id === false) {
			$statement = $this->dbAdapter->pdo->prepare($insert);
			$success = $statement->execute($params + $additionalFields);
			if ($success) {
				++$this->counter[$counterName]['insert'];
				$id = $statement->fetchColumn();
			}
		}
		return $id;
	}

	private function updateCounter(string $counterName): void {
		if (!isset($this->counter[$counterName])) {
			$this->counter[$counterName] = ['insert' => 0, 'select' => 0];
		}
		foreach ($this->dbAdapter->resetQueryCounter() as $queryType => $count) {
			$this->counter[$counterName][$queryType] += $count;
		}
	}

	public function getExchangeId(string $exchange, string $exchangeCode): int {
		if (!array_key_exists($exchange, $this->exchanges)) {
			$exchangeFields = ['name' => $exchange];
			['id' => $this->exchanges[$exchange]] = $this->dbAdapter->insertOrSelect('exchange', $exchangeFields, ['id'], ['name']);
			$this->updateCounter('exchange');
			$exchangeCodeFields = [
				'exchange_id' => $this->exchanges[$exchange],
				'code' => $exchangeCode,
			];
			$this->dbAdapter->insertIgnore('exchange_code', $exchangeCodeFields, ['exchange_id']);
			$this->updateCounter('exchange_code');
		}
		return $this->exchanges[$exchange];
	}

	public function getInstrumentId(int $exchangeId, string $instrument, ?string $contractVolume): int {
		if (!array_key_exists($instrument, $this->instruments)) {
			$fields = [
				'exchange_id' => $exchangeId,
				'name' => $instrument,
				'contract_volume' => $contractVolume,
			];
			['id' => $this->instruments[$instrument]] = $this->dbAdapter->insertOrSelect('instrument', $fields, ['id'], ['name']);
			$this->updateCounter('instrument');
		}
		return $this->instruments[$instrument];
	}

	private static function filterAndMapFields(array $fields, array $filterAndMap): array {
		$result = [];
		foreach (self::arrayChangeKeysToStrings($filterAndMap) as $filtered => $map) {
			$result[$filtered] = is_callable($map) ? call_user_func($map, $fields[$filtered]) : $fields[$filtered];
		}
		return $result;
	}

	public function processCot(int $instrumentId, string $date, array $fields): ?int {
		if (!isset($this->cot[$instrumentId][$date])) {
			$negate = static function($value) {
				return -$value;
			};
			$where = ['instrument_id' => $instrumentId, 'date' => $date];
			$fields = $where + self::filterAndMapFields($fields,
				[
					'hedgers_long', 'hedgers_short' => $negate,
					'swap_long', 'swap_short' => $negate, 'swap_spread',
					'managed_long', 'managed_short' => $negate, 'managed_spread',
					'other_long', 'other_short' => $negate, 'other_spread',
					'nonreportable_long', 'nonreportable_short' => $negate,
					'open_interest',
				]
			);
			['open_interest' => $this->cot[$instrumentId][$date]] = $this->dbAdapter->insertOrSelect('cot', $fields, ['open_interest'], array_keys($where));
			$this->updateCounter('cot');
		}
		return $this->cot[$instrumentId][$date];
	}

	private static function getDate(array $fields): string {
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
			$line = array_map('\\trim', $line); // Trim each line cell
			if ($firstLine) {
				self::checkFields($line);
				$firstLine = false;
			} else {
				$fields = self::getNamedFields($line);
				[$market, $exchange] = preg_split('~ - (?!.* - )~', $fields['Market_and_Exchange_Names']);
				$exchangeId = $this->getExchangeId($exchange, $fields['exchangeCode']);
				$instrumentId = $this->getInstrumentId($exchangeId, $market, $fields['Contract_Units']);
				$date = self::getDate($fields);
				$this->processCot($instrumentId, $date, $fields);
			}
		}
	}
}
