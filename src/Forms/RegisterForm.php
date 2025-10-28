<?php
namespace App\Forms;


class RegisterForm {

    protected string|null $_nice_name;
    protected string|null $_email;
    protected string|null $_password;
    protected string|null $_password_repeat;


    public string $message;
    

    public function __construct(array $args) {
        $this->_nice_name = $args['nice_name'] ?? null;
        $this->_email = $args['email'] ?? null;
        $this->_password = $args['password'] ?? null;
        $this->_password_repeat = $args['password_repeat'] ?? null;
    }


    public function load() {
        HttpHandler::$request->body['nice_name'] = $this->_nice_name;
        HttpHandler::$request->body['email'] = $this->_email;
        HttpHandler::$request->body['password'] = $this->_password;
        HttpHandler::$request->body['password_repeat'] = $this->_password_repeat;

        return true;
    }


    public function isValidate() {
        if (
            !$this->_nice_name ||
            !$this->_email ||
            !$this->_password ||
            !$this->_password_repeat
        ) {
            $this->message = "Некоторые поля не заполнены!";
            return false;
        }

        if (
            strlen($this->_nice_name) < 4 ||
            strlen($this->_email) < 4 ||
            strlen($this->_password) < 4
        ) {
            $this->message = "Длина некоторых полей содержит менее 4 символов";
            return false;
        }

        if ($this->_password != $this->_password_repeat) {
            $this->message = "Пароли не совпадают!";
            return false;
        }

        return true;
    }

}
