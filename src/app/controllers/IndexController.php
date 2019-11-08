<?php declare(strict_types=1);

class IndexController extends \Phalcon\Mvc\Controller {
	public function indexAction() {
		$this->view->instruments = []; //Instruments::find();
	}
}
