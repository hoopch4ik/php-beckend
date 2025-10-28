<?php
namespace App\Handlers;

use App\Handlers\HttpHandler;
use App\Config\ConfigWeb;
use App\Handlers\Logger\HttpLogger;


class Logger {
	protected static $clientTG = null;
	protected static $start_time = 0;
	protected static $end_time = 0;


	public function __construct() {
		self::log();
	}

	protected static function log() {
		if (!ConfigWeb::IS_LOGGING) return;

		self::$start_time = time();
		
		if (!self::$clientTG) {
			self::$clientTG = new Requester('https://api.telegram.org/bot'.ConfigWeb::botToken());
		}
	}

	public static function endpoint() {
		if (!ConfigWeb::IS_LOGGING) return;

		self::$end_time = time();
		self::sendTgClient(self::parse());
	}




	protected static function sendTgClient(string $text) {
		self::$clientTG->get("/sendMessage?chat_id=".ConfigWeb::tgChatId()."&text=$text")->then()->wait();
	}

	protected static function parse(): string {
		$method = HttpHandler::$request->method;
		$full_path = HttpHandler::$request->full_path;
		$timeout = self::$end_time-self::$start_time;
		$memory_MB = round(memory_get_usage()/1024/1024, 2);
		$ip = HttpLogger::getIp();
		$userAgent = HttpLogger::getAgent();

		return "'$method': $full_path
--------------------------------------------------
IP адрес: $ip
--------------------------------------------------
User-Agent: $userAgent
--------------------------------------------------
Время ожидания: $timeout сек.
--------------------------------------------------
Использовано памяти: $memory_MB MB";
	}
}