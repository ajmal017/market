<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Quandl;

class Connector {
	const DATABASE_METADATA_URL = 'https://www.quandl.com/api/v3/databases/%s/metadata?api_key=%s';
	const DATASET_URL = 'https://www.quandl.com/api/v3/datasets/%s/%s.json?api_key=%s';
	private $apiKey;

	public function __construct(string $apiKey) {
		$this->apiKey = $apiKey;
	}

	public static function downloadFileIfMissing(string $url, string $filename): string {
		if (!file_exists($filename)) {
			$curl = \curl_init($url);
			curl_setopt_array($curl, [
				CURLOPT_FILETIME => true,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_RETURNTRANSFER => true,
			]);
			$file = fopen($filename, 'wb');
			fputs($file, curl_exec($curl));
		}
		return $filename;
	}

	public static function downloadTempFile(string $url, string $tmpNamePrefix): string {
		$filename = tempnam(sys_get_temp_dir(), "$tmpNamePrefix-") . '.zip';
		return self::downloadFileIfMissing($url, $filename);
	}

	public function getDatabaseMetadata(string $database): array {
		$url = \sprintf(self::DATABASE_METADATA_URL, $database, $this->apiKey);
		$filename = "{$database}_metadata.csv";
		$filename = 'zip://' . self::downloadTempFile($url, $filename) . "#$filename";
		$fp = fopen($filename, 'r');
		$columnNames = [];
		while ($line = fgetcsv($fp)) {
			if (empty($columnNames)) {
				$columnNames = $line;
			} else {
				$metadata[] = \array_combine($columnNames, $line);
			}
		}
		return $metadata;
	}

	public function getDataset(string $database, string $dataset): array {
		$url = \sprintf(self::DATASET_URL, $database, $dataset, $this->apiKey);
		$response = \file_get_contents($url);
		if ($response === false) {
			throw new \Exception(sprintf("Can not download URL '%s'!", $url));
		}
		$data = \json_decode($response, true, 512, JSON_THROW_ON_ERROR);
		return $data['dataset'];
	}
}
