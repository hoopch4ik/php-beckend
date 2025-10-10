<?php
namespace App\Handlers;


class IncludeScripts {
    /**
     *
     * @var string[]
     */
    protected static $static_path_scripts = [];
    /**
     *
     * @var string[]
     */
    protected static $path_scripts = [];



    private function __construct() {}

    /**
     * $path_scripts - Список скриптов (путь + название файла)
     *
     * @param string[] $path_scripts
     * @return void
     */
    public static function setStaticScripts($path_scripts) {
        self::$static_path_scripts = $path_scripts;
    }

    /**
     * $path_scripts - Список скриптов (путь + название файла)
     *
     * @param string[] $path_scripts
     * @return void
     */
    public static function setScripts($path_scripts) {
        self::$path_scripts = $path_scripts;
    }

    /**
     * $path_scripts - Список скриптов (путь + название файла)
     *
     * @param string[] $path_scripts
     * @return void
     */
    public static function addScripts(array $path_scripts) {
        if (!in_array($path_scripts, self::$path_scripts)) {
            self::$path_scripts += $path_scripts;
        }
    }

    public static function render() {
        foreach (self::$static_path_scripts as $path_script) {
            echo "<script src='$path_script'></script>";
        }
        foreach (self::$path_scripts as $path_script) {
            echo "<script src='$path_script'></script>";
        }
    }
}
?>