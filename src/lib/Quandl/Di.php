<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Quandl;

interface Di {
	public function getApiKey(): string;
	public function getConnector(): Connector;
	public function getFutures(): \Sharkodlak\Market\Futures;
	public function getLogger(): \Psr\Log\LoggerInterface;
	public function getProgressBar(): \ProgressBar\Manager;
	public function getRootDir(): string;
	public function initProgressBar(int $current, int $max);
}
