<?php declare(strict_types=1);

class GraphController extends \Phalcon\Mvc\Controller {
	public function priceHistoryAction(string $symbol) {
		exit(__FILE__);
		$this->view->tradeDays = TradeDay::find([
			'conditions' => [
				'symbol = :symbol:',
			],
			'bind' => [
				'symbol' => $symbol,
			],
			'order' => 'symbol',
		]);
	}
}
