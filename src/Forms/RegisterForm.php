<?php
namespace App\Forms;


class RegisterForm {
    public string $nice_name;
    public string $email;
    public string $password;
    public string $password_repeat;



    protected string|null $_nice_name;
    protected string|null $_email;
    protected string|null $_password;
    protected string|null $_password_repeat;


    public string $message;
    

    public function __construct(
        string|null $nice_name,
        string|null $email,
        string|null $password,
        string|null $password_repeat,
    ) {
        $this->_nice_name = $nice_name;
        $this->_email = $email;
        $this->_password = $password;
        $this->_password_repeat = $password_repeat;
    }


    public function load() {
        $this->nice_name = $this->_nice_name;
        $this->email = $this->_email;
        $this->password = $this->_password;
        $this->password_repeat = $this->_password_repeat;
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
