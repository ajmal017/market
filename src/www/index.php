<?php declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
const DB_CONNECT = '/etc/webconf/market/connect.pgsql';


$loader = new \Phalcon\Loader();
$loader->registerDirs([
	APP_PATH . '/controllers/',
	APP_PATH . '/models/',
]);
$loader->register();

$di = new \Phalcon\Di\FactoryDefault();
$di->set('db', function () {
	$fp = \fopen(DB_CONNECT, 'r');
	$dbConnect = \fgets($fp);
	$dbConnect = \substr($dbConnect, 0, -1);
	$optionNameTranslate = ['user' => 'username'];
	[$type, $options] = \explode(':', $dbConnect);
	foreach (\explode(';', $options) as $option) {
		[$name, $value] = \explode('=', $option);
		$name = $optionNameTranslate[$name] ?? $name;
		$settings[$name] = $value;
	}
	$pdo = new \Phalcon\Db\Adapter\Pdo\PostgreSQL($settings);
	return $pdo;
});
$di->set('router', function () {
	$convert = function (string $oldName): string {
		$names = \preg_split('~[-_]~', $oldName);
		foreach ($names as $i => &$name) {
			if ($i >= 1) {
				$name = \ucfirst($name);
			}
		}
		return \implode('', $names);
	};
	$router = new \Phalcon\Mvc\Router(false);
	$route = $router->add('/:controller/:action/:params', [
		'controller' => 1,
		'action' => 2,
		'params' => 3,
	]);
	$route->convert('action', $convert);
	$router->handle($_SERVER['REQUEST_URI']);
	return $router;
});
$di->set('view', function () {
	$viewsDir = APP_PATH . '/views/';
	$view = new \Phalcon\Mvc\View();
	$view->setViewsDir($viewsDir);
	$view->registerEngines([
		'.volt' => function ($view, $di) use ($viewsDir) {
			$volt = new \Phalcon\Mvc\View\Engine\Volt($view, $di);
			$volt->setOptions([
				'compiledPath' => function ($templatePath) use ($viewsDir) {
					$absDirName = \dirname($templatePath);
					$templateName = \substr($templatePath, 1 + \strlen($absDirName));
					$dirName = \substr($absDirName, \strlen($viewsDir));
					$cacheDir = APP_PATH . '/cache/';
					$cacheDirName = $cacheDir . $dirName;
					if (!\is_dir($cacheDirName)) {
						\mkdir($cacheDirName, 0664, true);
					}
					return $cacheDirName . $templateName . '.php';
				},
				'path' => function ($templatePath) {
					exit("This Phalcon's version works with 'path' instead of 'compiledPath'.");
					return APP_PATH . '/cache/';
				},
			]);
			return $volt;
		},
	]);
	return $view;
});
$di->set('url', function() {
	$url = new \Phalcon\Url();
	$url->setBaseUri('/');
	return $url;
});

$application = new \Phalcon\Mvc\Application($di);
try {
	$response = $application->handle($_SERVER['REQUEST_URI']);
	$response->send();
} catch (\Exception $e) {
	echo 'Exception: ', $e->getMessage();
}


/*
<!DOCTYPE HTML>
<html>
<head>
<script>
window.onload = function() {

var chart = new CanvasJS.Chart("chartContainer", {
	title: {
		text: "Ericsson Stock Price - December 2017"
	},
	subtitles: [{
		text: "Currency in Swedish Krona"
	}],
	axisX: {
		valueFormatString: "DD MMM"
	},
	axisY: {
		includeZero: false,
		suffix: " kr"
	},
	data: [{
		type: "candlestick",
		xValueType: "dateTime",
		yValueFormatString: "#,##0.0 kr",
		xValueFormatString: "DD MMM",
		dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
	}]
});
chart.render();

}
</script>
</head>
<body>
<div id="chartContainer" style="height: 370px; width: 100%;"></div>
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>
</html>
