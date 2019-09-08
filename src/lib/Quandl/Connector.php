<?php

declare(strict_types=1);
namespace Quandl;

class Connector {
	const URL = 'https://www.quandl.com/api/v3/datasets/%s/%s.json?api_key=%s';
	private $apiKey;

	public function __construct(string $apiKey) {
		$this->apiKey = $apiKey;
	}

	public function getData(string $database, string $dataset): array {
		$url = \sprintf(self::URL, $database, $dataset, $this->apiKey);
		$response = \file_get_contents($url);
		if ($response === false) {
			throw new \Exception(sprintf("Can not download URL '%s'!", $url));
		}
		$data = \json_decode($response, true, 512, JSON_THROW_ON_ERROR);
		return $data['dataset'];
	}
}
