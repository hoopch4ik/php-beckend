<?php
namespace App\Api\V1\Controllers;

use App\Handlers\ApiResponse;
use App\Handlers\HttpHandler;
use App\Handlers\JwtHandlers\AuthJWT;
use App\Interfaces\Base\IUserRole;
use App\Models\UserModel;



class AuthController {

    public static function login() {
        $body = HttpHandler::$request->body;

        $email = $body["email"];
        $password = $body["password"];

        $user = UserModel::getbyEmail($email);

        if (
            !$user ||
            !UserModel::isCorrectPasswords($password, $user->password_hash)
        ) {
            new ApiResponse(
                400,
                false,
                "Данные введены некорректно!"
            );
        }
        UserModel::disconnect();

        $bearer = AuthJWT::generate([
            "id"=>$user->id,
            "role"=>$user->role,
            "nice_name"=>$user->nice_name,
            "email"=>$user->email,
            "image_url"=>$user->image_url,
            // "created_at"->$user->created_at,
            // "updated_at"->$user->updated_at
        ]);

        HttpHandler::$response->setFinish(
            new ApiResponse(
                200,
                true,
                "Аутентификация прошла успешно!",
                [
                    "bearer"=>$bearer
                ]
            )
        );
    }

    public static function register() {
        $body = HttpHandler::$request->body;
        
        $nice_name = $body["nice_name"];
        $email = $body["email"];
        $password = $body["password"];
        $password_repeat = $body["password_repeat"];
        $image_url = null; // Proccess upload file, get link and save


        $registerForm = new RegisterForm(
            $nice_name,
            $email,
            $password,
            $password_repeat
        );
        if (
            !$registerForm->isValidate() ||
            !$registerForm->load()
        ) {
            return new ApiResponse(
                400,
                false,
                $registerForm->message
            );
        }

        $isUser = UserModel::isUserByEmail($registerForm->email);
        if ($isUser) {
            return new ApiResponse(
                400,
                false,
                "Данная почта уже зарегистрирована!"
            );
        }


        $saved_user = UserModel::create(
            IUserRole::USER,
            $registerForm->nice_name,
            $registerForm->email,
            $image_url,
            $registerForm->password,
        );
        UserModel::disconnect();

        if (!$saved_user) {
            return new ApiResponse(
                400,
                false,
                UserModel::$error_message
            );
        }

        HttpHandler::$response->setFinish(
            new ApiResponse(
                200,
                true,
                "Учётная запись создана!",
            )
        );
    }

}
