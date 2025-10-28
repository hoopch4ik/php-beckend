<?php
namespace App\Config;


class ConfigWeb {
    public const IS_OPEN_API = true;
    public const IS_CACHED = true;
    public const IS_LOGGING = false;

    public const SITE_NAME = "";
    public const SITE_DESCRIPTION = "";
    public const SITE_DOMAIN = "http://test.php";
    public const ORIGINS = [
        "http://frontend",
    ];

    public static function botToken() {
        return $_ENV['BOT_TOKEN'];
    }

    public static function tgChatId() {
        return $_ENV['TG_CHAT_ID'];
    }
}