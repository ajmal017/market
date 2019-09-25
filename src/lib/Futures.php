<?php

declare(strict_types=1);
namespace Sharkodlak\Market;

class Futures {
	static private $monthLetters = [1 => 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'Q', 'U', 'V', 'X', 'Z'];

	static public function getMonthLetter(int $month): string {
		if (!array_key_exists($month, self::$monthLetters)) {
			$msg = sprintf('Month can be between 1 and 12, %d given!', $month);
			throw new \InvalidArgumentException($msg);
		}
		return self::$monthLetters[$month];
	}

	static public function getMonthNumber(string $letter): int {
		if (!in_array($letter, self::$monthLetters)) {
			$msg = sprintf('Month can be from [FGHJKMNQUVXZ], %s given!', $letter);
			throw new \InvalidArgumentException($msg);
		}
		return array_search($letter, self::$monthLetters);
	}
}
