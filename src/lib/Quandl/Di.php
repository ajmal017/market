<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Quandl;

interface Di {
	public function getConnector(): Connector;
	public function getFutures(): \Sharkodlak\Market\Futures;
	public function getLogger(): \Psr\Log\LoggerInterface;
}
