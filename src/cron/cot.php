<?php

declare(strict_types=1);
require __DIR__ . '/../../vendor/autoload.php';

define('YEAR', $argv[1] ?? null);
define('LOG_LEVEL', $argv[2] ?? 'debug');


$di = new class implements \Sharkodlak\Market\Cot\Di, \Sharkodlak\Db\Adapter\Di {
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
		return new \Sharkodlak\Db\Adapter\Postgres($pdo, $this);
	}
	public function getLogger(): \Psr\Log\LoggerInterface {
		$logger = new class extends \Psr\Log\AbstractLogger {
			static private $defaultBashStyle = "\e[2m";
			static private $defaultBashStyleEnd = " \e[0m";
			static private $bashStyle = [
				\Psr\Log\LogLevel::EMERGENCY => "\e[91m",
				\Psr\Log\LogLevel::ALERT => "\e[91m",
				\Psr\Log\LogLevel::CRITICAL => "\e[91m",
				\Psr\Log\LogLevel::ERROR => "\e[91m",
				\Psr\Log\LogLevel::WARNING => "\e[33m",
				\Psr\Log\LogLevel::NOTICE => "\e[93m",
				\Psr\Log\LogLevel::INFO => "\e[2m",
			];
			public function log($level, $message, array $context = []) {
				$logLevelName = \strtoupper(LOG_LEVEL);
				if ($level >= \constant("\\Psr\\Log\\LogLevel::$logLevelName")) {
					$styleStart = self::$bashStyle[$level] ?? self::$defaultBashStyle;
					$message = $styleStart . $message . self::$defaultBashStyleEnd;
					fputs(STDERR, $message);
				}
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
$filename = empty(YEAR) ?
	$cot->downloadFileIfMissing() :
	$di->getRootDir() . '/data/cot/com_disagg_txt_' . YEAR . '.zip';
$filename = $cot->extractFile($filename);
$cot->importFromFile($filename);
foreach ($cot->counter as $table => $counts) {
	printf("Table '%s': %d rows readed, %d rows added.\n", $table, $counts['select'], $counts['insert']);
}
