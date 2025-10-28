<?php
namespace App\Forms;

use App\Handlers\ApiResponse;
use App\Handlers\HttpHandler;


class LoginForm {

    protected string|null $_email;
    protected string|null $_password;


    public function __construct(array $args) {
        $this->_email = $args['email'] ?? null;
        $this->_password = $args['password'] ?? null;
    }


    public function load() {
        HttpHandler::$request->body['email'] = $this->_email;
        HttpHandler::$request->body['password'] = $this->_password;

        return true;
    }


    public function isValidate() {
        if (
            !$this->_email ||
            !$this->_password
        ) {
            $this->message = "Некоторые поля не заполнены!";
            return false;
        }

        if (
            strlen($this->_email) < 4 ||
            strlen($this->_password) < 4
        ) {
            $this->message = "Длина некоторых полей содержит менее 4 символов!";
            return false;
        }

        return true;
    }

}
