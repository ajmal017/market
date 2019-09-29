<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

define('YEAR', $argv[1] ?? null);

$di = new class implements \Sharkodlak\Market\Cot\Di {
	private $services = [];
	public function __get($name) {
		if (!isset($services[$name])) {
			$method = "get$name";
			$services[$name] = $this->$method();
		}
		return $services[$name];
	}
	public function getDbAdapter(): \Sharkodlak\Db\Adapter\Base {
		$pdo = new \PDO('uri:file:///etc/webconf/market/connect.powerUser.pgsql');
		$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		return new \Sharkodlak\Db\Adapter\Postgres($pdo);
	}
	public function getLogger(): \Psr\Log\LoggerInterface {
		$logger = new class extends \Psr\Log\AbstractLogger {
			public function log($level, $message, array $context = []) {
				switch ($level) {
					case \Psr\Log\LogLevel::EMERGENCY:
					case \Psr\Log\LogLevel::ALERT:
					case \Psr\Log\LogLevel::CRITICAL:
					case \Psr\Log\LogLevel::ERROR:
						$style = "\e[91m";
					break;
					case \Psr\Log\LogLevel::WARNING:
						$style = "\e[33m";
					break;
					case \Psr\Log\LogLevel::NOTICE:
						$style = "\e[93m";
					break;
					default:
						$style = "\e[2m";
				}
				fputs(STDERR, "\x0D$style$message\e[0m\n");
			}
		};
		return $logger;
	}
	public function getProgressBar(): \ProgressBar\Manager {
		return $this->progressBar;
	}
	public function getRootDir(): string {
		return '/vagrant';
	}
	public function initProgressBar(int $current, int $max): self {
		$this->progressBar = new \ProgressBar\Manager($current, $max);
		return $this;
	}
};
$cot = new \Sharkodlak\Market\Cot\Cot($di);
$filename = YEAR === null ?
	$cot->downloadFileIfMissing() :
	$di->getRootDir() . '/data/cot/com_disagg_txt_' . YEAR . '.zip';
$filename = $cot->extractFile($filename);
$cot->importFromFile($filename);
foreach ($cot->counter as $table => $counts) {
	printf("Table '%s': %d rows readed, %d rows added.\n", $table, $counts['select'], $counts['insert']);
}
