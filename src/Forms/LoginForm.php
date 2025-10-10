<?php
namespace App\Forms;


class LoginForm {
    public string $email;
    public string $password;


    protected string|null $_email;
    protected string|null $_password;


    public string $message;

    public function __construct(
        string|null $email,
        string|null $password,
    ) {
        $this->_email = $email;
        $this->_password = $password;
    }


    public function load() {
        $this->email = $this->_email;
        $this->password = $this->_password;
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
            $this->message = "Длина некоторых полей содержит менее 4 символов";
            return false;
        }

        return true;
    }

}
