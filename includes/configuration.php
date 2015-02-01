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
        $streamToFile = new \Monolog\Handler\StreamHandler( $config['log']['filename'] );

        //@See https://github.com/Seldaek/monolog/blob/master/doc/usage.md
        // DEFAULT: "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
        $output = "[%datetime%] [%level_name%] [%extra%] : %message% %context%\n";
        $formatter = new Monolog\Formatter\LineFormatter($output);
        $streamToFile->setFormatter($formatter);
		$handlers[] = $streamToFile;
		$logger = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
		    'handlers' => $handlers,
            'processors' => array(
                new Monolog\Processor\UidProcessor(),
                new Monolog\Processor\WebProcessor($_SERVER),
            )
		));

		if ( isset($filenameQuery) ) {
			$streamToFileQuery = new \Monolog\Handler\StreamHandler( $config['log']['filenameQuery'] );
			$streamToFileQuery->setFormatter($formatter);

			$loggerQuery = new \Monolog\Logger('queryLog');
			$loggerQuery->pushHandler($streamToFileQuery);
			$loggerQuery->pushProcessor(new Monolog\Processor\UidProcessor());
			$loggerQuery->pushProcessor(new \Monolog\Processor\WebProcessor($_SERVER));
		}

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
		'logger' => isset($logger) ? $logger : null,
		'loggerQuery' => isset($loggerQuery) ? $loggerQuery : null
	);
}