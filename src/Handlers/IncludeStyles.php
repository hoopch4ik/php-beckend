<?php
namespace App\Handlers;


class IncludeStyles {
    /**
     *
     * @var string[]
     */
    protected static $static_path_styles = [];
    /**
     *
     * @var string[]
     */
    protected static $path_styles = [];


    private function __construct() {}


    /**
     * $path_styles - Список скриптов (путь + название файла)
     *
     * @param string[] $path_styles
     * @return void
     */
    public static function setStaticStyles($path_styles) {
        self::$static_path_styles = $path_styles;
    }

    /**
     * $path_styles - Список скриптов (путь + название файла)
     *
     * @param string[] $path_styles
     * @return void
     */
    public static function setStyles($path_styles) {
        self::$path_styles = $path_styles;
    }


    /**
     * $path_styles - Список стилей (путь + название файла)
     *
     * @param string[] $path_styles
     * @return void
     */
    public static function addStyles($path_styles) {
        if (!in_array($path_styles, self::$path_styles)) {
            self::$path_styles += $path_styles;
        }
    }

    public static function render() {
        foreach (self::$static_path_styles as $path_style) {
            echo "<link rel='stylesheet' href='$path_style'>";
        }
        foreach (self::$path_styles as $path_style) {
            echo "<link rel='stylesheet' href='$path_style'>";
        }
    }
}
