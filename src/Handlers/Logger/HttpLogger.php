<?php
namespace App\Utils;

class HttpLogger {

	public static function getIp() {
	    $ip = '';
	    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	        $ip = $_SERVER['HTTP_CLIENT_IP'];
	    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    } else {
	        $ip = $_SERVER['REMOTE_ADDR'];
	    }
	    return $ip;
	}

	public static function getAgent() {
		return $_SERVER['HTTP_USER_AGENT'];
	}

}