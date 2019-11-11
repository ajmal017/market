<?php declare(strict_types=1);

class TradeDay extends \Phalcon\Mvc\Model {
	public $date;
	public $contract_id;
	public $open;
	public $high;
	public $low;
	public $close;
	public $volume;
}
