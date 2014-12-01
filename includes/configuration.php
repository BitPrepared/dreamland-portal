<?php

function configure_slim($config){
	$log_level = \Slim\Log::WARN;
	$log_enable = false;
	if ( isset($config['log']) ){
		$handlers = array();
		if ( $config['enviroment'] == 'production' && isset($config['log']['hipchat']) ) {
			$hipchat = $config['log']['hipchat'];
			$handlers[] = new \Monolog\Handler\HipChatHandler($hipchat['token'], $hipchat['room'], $hipchat['name'], $hipchat['notify'], \Monolog\Logger::INFO, $hipchat['bubble'], $hipchat['useSSL']);
		}
		$handlers[] = new \Monolog\Handler\StreamHandler($config['log']['filename']);
		$logger = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
		    'handlers' => $handlers
		));
		switch ($config['log']['level']) {
			case "EMERGENCY" 	:
				$log_level = \Slim\Log::EMERGENCY;
				break;
			case "ALERT" 		:
				$log_level = \Slim\Log::ALERT;
				break;
			case "CRITICAL"		:
				$log_level = \Slim\Log::CRITICAL;
				break;
			case "ERROR"		:
				$log_level = \Slim\Log::ERROR;
				break;
			case "WARN"			:
				$log_level = \Slim\Log::WARN;
				break;
			case "NOTICE"		:
				$log_level = \Slim\Log::NOTICE;
				break;
			case "INFO"			:
				$log_level = \Slim\Log::INFO;
				break;
			case "DEBUG"		:
				$log_level = \Slim\Log::DEBUG;
				break;
			default:	
				$log_level = \Slim\Log::WARN;
				break;
		}
		$log_enable = true;
	}
	return array(
		'log_enable' => $log_enable,
		'log_level' => $log_level,
		'logger' => isset($logger) ? $logger : null
	);
}