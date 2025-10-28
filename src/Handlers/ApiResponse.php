<?php
namespace App\Handlers;


class ApiResponse {
    public int $code;
    public bool $success;
    public string $message;
    public array|object $data;


    public function __construct(
        int $code,
        bool $success,
        string $message,
        array|object $data = []
    ) {
        $this->code = $code;
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
    }

    public function __toString() {
        return json_encode([
            "code"=>$this->code,
            "success"=>$this->success,
            "message"=>json_decode('"' . $this->message . '"'),
            "data"=>$this->data,
        ], JSON_UNESCAPED_UNICODE);
    }
}