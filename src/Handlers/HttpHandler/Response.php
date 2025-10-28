<?php
namespace App\Handlers\HttpHandler;

use App\Config\ConfigWeb;


class Response {

	public function __construct() {
		$this->handleOrigins();
		$this->handleMethods();
	}

	protected function handleOrigins() {
		if (isset($_SERVER['HTTP_ORIGIN'])) {
		    if (in_array($_SERVER['HTTP_ORIGIN'], ConfigWeb::ORIGINS)) {
		        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		    }
		    header('Access-Control-Allow-Credentials: true');
		}
	}

	protected function handleMethods() {
		// Access-Control headers are received during OPTIONS requests
		if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
		        header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
		    }
		    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
		        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
		    }
		    exit(0);
		}
	}

}