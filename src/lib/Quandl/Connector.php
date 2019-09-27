<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Quandl;

class Connector {
	const DATABASE_METADATA_URL = 'https://www.quandl.com/api/v3/databases/%s/metadata?api_key=%s';
	const DATASET_URL = 'https://www.quandl.com/api/v3/datasets/%s/%s.json?api_key=%s';
	private $di;

	public function __construct(Di $di) {
		$this->di = $di;
	}

	public function downloadFileIfMissing(string $url, string $database): string {
		$filename = $this->di->rootDir . "/data/quandl/{$database}_metadata.csv.zip";
		$headerFilename = "$filename.head";
		$curlOptions = [
			CURLOPT_FILETIME => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HEADER => true,
			CURLOPT_RETURNTRANSFER => true,
		];
		if (\file_exists($filename)) {
			$etag = \md5_file($filename);
			$curlOptions[CURLOPT_HTTPHEADER] = ["If-None-Match: \"$etag\""];
		}
		$curl = \curl_init($url);
		\curl_setopt_array($curl, $curlOptions);
		$response = \curl_exec($curl);
		$headerSize = \curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$header = \substr($response, 0, $headerSize);
		\file_put_contents($headerFilename, $header);
		if (200 == \curl_getinfo($curl, CURLINFO_RESPONSE_CODE)) {
			\file_put_contents($filename, substr($response, $headerSize));
		}
		return $filename;
	}

	public function getDatabaseMetadata(string $database): array {
		$url = \sprintf(self::DATABASE_METADATA_URL, $database, $this->di->apiKey);
		$filename = "{$database}_metadata.csv";
		$filename = 'zip://' . $this->downloadFileIfMissing($url, $database) . "#$filename";
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
		$url = \sprintf(self::DATASET_URL, $database, $dataset, $this->di->apiKey);
		$response = \file_get_contents($url);
		if ($response === false) {
			throw new \Exception(sprintf("Can not download URL '%s'!", $url));
		}
		$data = \json_decode($response, true, 512, JSON_THROW_ON_ERROR);
		return $data['dataset'];
	}
}
