<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Quandl;

class Connector {
	const DATABASE_METADATA_URL = 'https://www.quandl.com/api/v3/databases/%s/metadata?api_key=%s';
	const DATASET_URL = 'https://www.quandl.com/api/v3/datasets/%s/%s.json?api_key=%s';
	private $di;
	private $settings;

	public function __construct(Di $di, array $settings = []) {
		$this->di = $di;
		$this->settings = $settings;
	}

	public function downloadFileIfMissing(string $url, string $filename): string {
		if (!empty($this->settings['offline'])) {
			return $filename;
		}
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
		if ($response) {
			$headerSize = \curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$header = \substr($response, 0, $headerSize);
			if (\preg_match('~x-ratelimit-remaining: (\d+)~', $header, $matches)) {
				$msg = sprintf('API limit %d.', $matches[1]);
				$this->di->logger->info($msg);
			}
			$dirname = \dirname($filename);
			$httpStatusCode = \curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
			switch ($httpStatusCode) {
				case 200:
					if (!\is_dir($dirname)) {
						\mkdir($dirname, 0775, true);
					}
					\file_put_contents($headerFilename, $header);
					\file_put_contents($filename, substr($response, $headerSize));
				case 304:
					break;
				default:
					$lastHeaderStart = \strrpos($header, "\r\n\r\n", -5) ?: 0;
					$lastHeaderStart += $lastHeaderStart ? 4 : 0;
					$lastHeader = \substr($header, $lastHeaderStart);
					$httpLine = \substr($lastHeader, 0, \strpos($lastHeader, "\r\n"));
					throw new \Sharkodlak\Exception\HTTPException($httpLine, $httpStatusCode);
			}
			return $filename;
		} else {
			throw new \Sharkodlak\Exception\SocketTimeoutException();
		}
	}

	public function getDatabaseMetadata(string $database): array {
		$url = \sprintf(self::DATABASE_METADATA_URL, $database, $this->di->apiKey);
		$filename = "{$database}_metadata.csv";
		$filePath = $this->di->rootDir . "/data/quandl/$filename.zip";
		$filePath = 'zip://' . $this->downloadFileIfMissing($url, $filePath) . "#$filename";
		$fp = fopen($filePath, 'r');
		$columnNames = [];
		while ($line = fgetcsv($fp)) {
			$line = array_map('\\trim', $line); // Trim each line cell
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
		$filePath = $this->di->rootDir . "/data/quandl/$database/$dataset.json";
		$filename = $this->downloadFileIfMissing($url, $filePath);
		if (!file_exists($filename)) {
			$msg = sprintf('File "%s" not found!', $filename);
			$e = new \Sharkodlak\Exception\FileNotFoundException($msg);
			$e->filename = $filename;
			throw $e;
		}
		$json = \file_get_contents($filename);
		$data = \json_decode($json, true, 512, JSON_THROW_ON_ERROR);
		return $data['dataset'];
	}
}
