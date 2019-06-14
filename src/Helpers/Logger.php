<?php
namespace EC\Helpers;
use Katzgrau\KLogger\Logger as KLogger;
use Psr\Log\LogLevel;
use RuntimeException;

class Logger extends KLogger{
	protected static $logger;

	protected static $levels = [
		'EMERGENCY' => LogLevel::EMERGENCY,
		'ALERT' => LogLevel::ALERT,
		'CRITICAL' => LogLevel::CRITICAL,
		'ERROR' => LogLevel::ERROR,
		'WARNING' => LogLevel::WARNING,
		'NOTICE' => LogLevel::NOTICE,
		'INFO' => LogLevel::INFO,
		'DEBUG' => LogLevel::DEBUG
	];

	public static function init(Config $config){
		try{
			self::$logger = new KLogger(LOGS, self::$levels[$config->logLevel], [
				'extension' => 'log',
				'dateFormat' => 'd-m-Y G:i:s'
			]);
		}
		catch (RuntimeException $e){
			echo "Klogger error: " . $e->getMessage() . "\n" . LOGS . "\nTerminated script!\n";
			die;
		}
	}

	public static function getLogger(){
		if(is_null(self::$logger))
			throw new \Exception("Logger não iniciado!");

		return self::$logger;
	}

}
