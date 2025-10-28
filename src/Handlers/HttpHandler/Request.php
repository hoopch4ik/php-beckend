<?php
namespace App\Handlers\HttpHandler;

use stdClass;


class Request {
    public array $headers;
    public string $method;
    public string $full_path;
    public string $route;
    public array $params;
    public array $body;
    public stdClass $decoded_data;



    public function __construct() {
        $jsonData = file_get_contents('php://input');

        
        $this->headers = getallheaders();
        $this->method = $_SERVER["REQUEST_METHOD"];
        $this->route = parse_url($_SERVER['REQUEST_URI'], 5);
        $this->full_path = $_SERVER['HTTP_HOST'].$this->route;
        $this->params = $_GET;
        $this->body = json_decode($jsonData, true) ?? [];
        $this->decoded_data = new stdClass();
    }

    public function setDecodedData(stdClass $data) {
        $this->decoded_data = $data;
    }
}
