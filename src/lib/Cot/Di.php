<?php

declare(strict_types=1);
namespace Sharkodlak\Market\Cot;

interface Di {
	public function getDbAdapter(): \Sharkodlak\Db\Adapter\Base;
	public function getProgressBar(): \ProgressBar\Manager;
	public function getRootDir(): string;
	public function initProgressBar(int $current, int $max);
}
