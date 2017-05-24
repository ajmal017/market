<?php

const COT_URL = 'http://www.cftc.gov/files/dea/history/deahistfo2017.zip';
const COT_ZIP = '/tmp/cot.zip';
const COT_UNZIP = 'zip://' . COT_ZIP . '#annualof.txt';
const DB_CONNECT = '/etc/webconf/market/connect.powerUser.pgsql';
const READ_ONLY = 'r';

if (!file_exists(COT_ZIP)) {
	copy(COT_URL, COT_ZIP);
}

$fieldNames = [
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
$exchanges = [];
$markets = [];
$pdo = new \PDO('uri:file://' . DB_CONNECT);
$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
$firstLine = true;
$fp = fopen(COT_UNZIP, READ_ONLY);
while ($line = fgetcsv($fp)) {
	if ($firstLine) {
		$unknownFieldNames = array_diff($line, $fieldNames);
		if (!empty($unknownFieldNames)) {
			print_r($unknownFieldNames);
			trigger_error('Unknown field names!', E_USER_ERROR);
		}
		unset($unknownFieldNames);
		$firstLine = false;
	} else {
		list($market, $exchange) = preg_split('~ - (?!.* - )~', $line[array_search('Market and Exchange Names', $fieldNames)]);
		if (!in_array($exchange, $exchanges)) {
			$exchanges[] = $exchange;
			$sql = "INSERT INTO exchange (name) SELECT ':exchange' WHERE NOT EXISTS (SELECT id FROM exchange WHERE name = :exchange) RETURNING id";
			$prepare = $pdo->prepare($sql);
			//$prepare->bindParam(':exchange', $exchange, PDO::PARAM_STR);
			$success = $prepare->execute([':exchange' => $exchange]);
			if (!$success) {
				trigger_error('Exchange insertion failure!', E_USER_ERROR);
			}
		}
		if (!in_array($market, $markets)) {
			$markets[] = $market;
		}

	}
}
