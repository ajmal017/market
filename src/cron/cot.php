<?php

const DB_CONNECT = '/etc/webconf/market/connect.powerUser.pgsql';

class COT {
	const READ_ONLY = 'r';
	const URL = 'http://www.cftc.gov/files/dea/history/deahistfo2017.zip';
	const ZIP = '/tmp/cot.zip';

	public $counter = [
		'exchange' => ['select' => 0, 'insert' => 0],
		'instrument' => ['select' => 0, 'insert' => 0],
		'cot' => ['select' => 0, 'insert' => 0],
	];
	private $exchanges = [];
	private static $fieldNames = [
		"Market and Exchange Names",
		"As of Date in Form YYMMDD",
		"As of Date in Form YYYY-MM-DD",
		"CFTC Contract Market Code",
		"CFTC Market Code in Initials",
		"CFTC Region Code",
		"CFTC Commodity Code",
		"Open Interest (All)",
		"Noncommercial Positions-Long (All)",
		"Noncommercial Positions-Short (All)",
		"Noncommercial Positions-Spreading (All)",
		"Commercial Positions-Long (All)",
		"Commercial Positions-Short (All)",
		" Total Reportable Positions-Long (All)",
		"Total Reportable Positions-Short (All)",
		"Nonreportable Positions-Long (All)",
		"Nonreportable Positions-Short (All)",
		"Open Interest (Old)",
		"Noncommercial Positions-Long (Old)",
		"Noncommercial Positions-Short (Old)",
		"Noncommercial Positions-Spreading (Old)",
		"Commercial Positions-Long (Old)",
		"Commercial Positions-Short (Old)",
		"Total Reportable Positions-Long (Old)",
		"Total Reportable Positions-Short (Old)",
		"Nonreportable Positions-Long (Old)",
		"Nonreportable Positions-Short (Old)",
		"Open Interest (Other)",
		"Noncommercial Positions-Long (Other)",
		"Noncommercial Positions-Short (Other)",
		"Noncommercial Positions-Spreading (Other)",
		"Commercial Positions-Long (Other)",
		"Commercial Positions-Short (Other)",
		"Total Reportable Positions-Long (Other)",
		"Total Reportable Positions-Short (Other)",
		"Nonreportable Positions-Long (Other)",
		"Nonreportable Positions-Short (Other)",
		"Change in Open Interest (All)",
		"Change in Noncommercial-Long (All)",
		"Change in Noncommercial-Short (All)",
		"Change in Noncommercial-Spreading (All)",
		"Change in Commercial-Long (All)",
		"Change in Commercial-Short (All)",
		"Change in Total Reportable-Long (All)",
		"Change in Total Reportable-Short (All)",
		"Change in Nonreportable-Long (All)",
		"Change in Nonreportable-Short (All)",
		"% of Open Interest (OI) (All)",
		"% of OI-Noncommercial-Long (All)",
		"% of OI-Noncommercial-Short (All)",
		"% of OI-Noncommercial-Spreading (All)",
		"% of OI-Commercial-Long (All)",
		"% of OI-Commercial-Short (All)",
		"% of OI-Total Reportable-Long (All)",
		"% of OI-Total Reportable-Short (All)",
		"% of OI-Nonreportable-Long (All)",
		"% of OI-Nonreportable-Short (All)",
		"% of Open Interest (OI)(Old)",
		"% of OI-Noncommercial-Long (Old)",
		"% of OI-Noncommercial-Short (Old)",
		"% of OI-Noncommercial-Spreading (Old)",
		"% of OI-Commercial-Long (Old)",
		"% of OI-Commercial-Short (Old)",
		"% of OI-Total Reportable-Long (Old)",
		"% of OI-Total Reportable-Short (Old)",
		"% of OI-Nonreportable-Long (Old)",
		"% of OI-Nonreportable-Short (Old)",
		"% of Open Interest (OI) (Other)",
		"% of OI-Noncommercial-Long (Other)",
		"% of OI-Noncommercial-Short (Other)",
		"% of OI-Noncommercial-Spreading (Other)",
		"% of OI-Commercial-Long (Other)",
		"% of OI-Commercial-Short (Other)",
		"% of OI-Total Reportable-Long (Other)",
		"% of OI-Total Reportable-Short (Other)",
		"% of OI-Nonreportable-Long (Other)",
		"% of OI-Nonreportable-Short (Other)",
		"Traders-Total (All)",
		"Traders-Noncommercial-Long (All)",
		"Traders-Noncommercial-Short (All)",
		"Traders-Noncommercial-Spreading (All)",
		"Traders-Commercial-Long (All)",
		"Traders-Commercial-Short (All)",
		"Traders-Total Reportable-Long (All)",
		"Traders-Total Reportable-Short (All)",
		"Traders-Total (Old)",
		"Traders-Noncommercial-Long (Old)",
		"Traders-Noncommercial-Short (Old)",
		"Traders-Noncommercial-Spreading (Old)",
		"Traders-Commercial-Long (Old)",
		"Traders-Commercial-Short (Old)",
		"Traders-Total Reportable-Long (Old)",
		"Traders-Total Reportable-Short (Old)",
		"Traders-Total (Other)",
		"Traders-Noncommercial-Long (Other)",
		"Traders-Noncommercial-Short (Other)",
		"Traders-Noncommercial-Spreading (Other)",
		"Traders-Commercial-Long (Other)",
		"Traders-Commercial-Short (Other)",
		"Traders-Total Reportable-Long (Other)",
		"Traders-Total Reportable-Short (Other)",
		"Concentration-Gross LT = 4 TDR-Long (All)",
		"Concentration-Gross LT =4 TDR-Short (All)",
		"Concentration-Gross LT =8 TDR-Long (All)",
		"Concentration-Gross LT =8 TDR-Short (All)",
		"Concentration-Net LT =4 TDR-Long (All)",
		"Concentration-Net LT =4 TDR-Short (All)",
		"Concentration-Net LT =8 TDR-Long (All)",
		"Concentration-Net LT =8 TDR-Short (All)",
		"Concentration-Gross LT =4 TDR-Long (Old)",
		"Concentration-Gross LT =4 TDR-Short (Old)",
		"Concentration-Gross LT =8 TDR-Long (Old)",
		"Concentration-Gross LT =8 TDR-Short (Old)",
		"Concentration-Net LT =4 TDR-Long (Old)",
		"Concentration-Net LT =4 TDR-Short (Old)",
		"Concentration-Net LT =8 TDR-Long (Old)",
		"Concentration-Net LT =8 TDR-Short (Old)",
		"Concentration-Gross LT =4 TDR-Long (Other)",
		"Concentration-Gross LT =4 TDR-Short(Other)",
		"Concentration-Gross LT =8 TDR-Long (Other)",
		"Concentration-Gross LT =8 TDR-Short(Other)",
		"Concentration-Net LT =4 TDR-Long (Other)",
		"Concentration-Net LT =4 TDR-Short (Other)",
		"Concentration-Net LT =8 TDR-Long (Other)",
		"Concentration-Net LT =8 TDR-Short (Other)",
		"Contract Units",
		"CFTC Contract Market Code (Quotes)",
		"CFTC Market Code in Initials (Quotes)",
		"CFTC Commodity Code (Quotes)",
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
