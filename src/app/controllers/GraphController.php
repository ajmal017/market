<?php declare(strict_types=1);

class GraphController extends \Phalcon\Mvc\Controller {
	public function priceHistoryAction(string $symbol) {
		$instrument = Instrument::findFirst([
			'conditions' => 'symbol = :symbol:',
			'bind' => [
				'symbol' => $symbol,
			],
		]);
		$contract = Contract::findFirst([
			'conditions' => 'instrument_id = :instrument_id: AND depth = 1',
			'bind' => [
				'instrument_id' => $instrument->id,
			],
		]);
		$this->view->tradeDays = TradeDay::find([
			'conditions' => 'contract_id = :contract_id:',
			'bind' => [
				'contract_id' => $contract->id,
			],
			'order' => 'date',
		]);
	}
}
