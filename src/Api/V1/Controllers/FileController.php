<?php
namespace App\Api\V1\Controllers;

use App\Handlers\ApiResponse;
use App\Config\ConfigWeb;
use App\Config\ConfigDirs;
use App\Models\FilesModel;
use App\Handlers\HttpHandler;
use \DateTime;


class FileController {

    public static function post() {
        if (empty($_FILES['upload_files'])) {
            new ApiResponse(
                400,
                false,
                "Файл не найден!"
            );
        }
        if (!is_array($_FILES['upload_files']['name'])) {
            new ApiResponse(
                400,
                false,
                "Файлы должны приходить списком! (макс.: 5)"
            );
        }

        $file_urls = [];
        $product_id = HttpHandler::$request->params["product_id"] ?? null;

        if (
            !$product_id ||
            !is_numeric($product_id)
        ) {
            new ApiResponse(
                400,
                false,
                "Параметр product_id явлется обязательным и целочисленным!"
            );
        }

        for ($i = 0; $i < count($_FILES["upload_files"]["name"]); $i++) {
            $formatted_name = self::randomHash().self::getFileExtension($_FILES['upload_files']["type"][$i]);
        
            $file_url = ConfigWeb::SITE_DOMAIN . ConfigDirs::PUBLIC . "/" . $formatted_name;
            self::saveFileDB($formatted_name, $product_id);

            try {
                self::uploadFile(
                    $formatted_name,
                    $_FILES['upload_files']["full_path"][$i],
                    $_FILES['upload_files']["type"][$i],
                    $_FILES['upload_files']["tmp_name"][$i],
                    $_FILES['upload_files']["error"][$i],
                    $_FILES['upload_files']["size"][$i]
                );
            } catch (\Exception) {
                new ApiResponse(
                    500,
                    false,
                    "Произошла ошибка загрузки, повторите запрос позднее!"
                );
            }

            $file_urls[] = $file_url;
        }

        new ApiResponse(
            200,
            true,
            "Файлы успешно загружены на сервер!",
            [
                "type"=>"post/file",
                "file_urls"=>$file_urls
            ]
        );
    }

    // public static function update() {
    //     $id = HttpHandler::$request->params["id"] ?? null;
    //     if (
    //         !$id ||
    //         !is_numeric($id)
    //     ) {
    //         new ApiResponse(
    //             400,
    //             false,
    //             "Параметр id является обязательным и целочисленным!"
    //         );
    //     }

    //     $url = HttpHandler::$request->body["url"] ?? null;
    //     if (!$url) {
    //         new ApiResponse(
    //             400,
    //             false,
    //             "Поле url является обязательным!"
    //         );
    //     }

    //     try {
    //         $updatedModel = FilesModel::update(new FilesModel(
    //             (int)$id,
    //             $url,
    //             0,
    //             new DateTime()
    //         ));
    //         if (!$updatedModel) {
    //             new ApiResponse(
    //                 400,
    //                 false,
    //                 "Ошибка обновления записи!"
    //             );
    //         }
    //     } catch (\Exception) {
    //         new ApiResponse(
    //             500,
    //             false,
    //             "Ошибка обновления записи!"
    //         );
    //     }

    //     new ApiResponse(
    //         200,
    //         true,
    //         "Сущность успешно обновлена!",
    //         [
    //             "type"=>"patch/files",
    //             "data"=>$updatedModel
    //         ]
    //     );
    // }

    public static function delete() {
        $id = HttpHandler::$request->params["id"] ?? null;
        if (
            !$id ||
            !is_numeric($id)
        ) {
            new ApiResponse(
                400,
                false,
                "Параметр id является обязательным и целочисленным!"
            );
        }

        try {
            $isDeleted = FilesModel::deleteById($id);
            if (!$isDeleted) {
                new ApiResponse(
                    400,
                    false,
                    "Ошибка удаления записи!"
                );
            }
        } catch (\Exception) {
            new ApiResponse(
                500,
                false,
                "Ошибка удаления записи!"
            );
        }

        new ApiResponse(
            200,
            true,
            "Сущность успешно удалена!",
            [
                "type"=>"delete/files"
            ]
        );
    }

    protected static function uploadFile(
        string $formatted_name,
        string $full_path,
        string $type,
        string $tmp_name,
        int $error,
        int $size
    ): void {
        if (
            $error != UPLOAD_ERR_OK ||
            !is_uploaded_file($tmp_name)
        ) {
            new ApiResponse(
                500,
                false,
                "Файл не загружен во временное хранилище!"
            );
        }

        $uploaddir = ConfigDirs::BASE_PROJECT.ConfigDirs::PUBLIC;
        $uploadfile = $uploaddir . "/" . $formatted_name;

        if (!move_uploaded_file($tmp_name, $uploadfile)) {
            new ApiResponse(
                500,
                false,
                "Ошибка загрузки файла!"
            );
        }
    }

    protected static function saveFileDB(string $file_name, int|string $product_id) {
        try {
            $res = FilesModel::create(
                $file_name,
                $product_id
            );
            if (!$res) {
                new ApiResponse(
                    500,
                    false,
                    "Ошибка сохранения файла в БД!"
                );
            }
        } catch (\Exception) {
            new ApiResponse(
                400,
                false,
                "Ошибка сохранения файла в БД! Возможно id введён не верно."
            );
        }
    }

    // protected static function saveFilesDB(string $file_urls, int|string $product_id) {
    //     try {
    //         $res = FilesModel::createMany(
    //             $file_urls,
    //             $product_id
    //         );
    //         if (!$res) {
    //             new ApiResponse(
    //                 500,
    //                 false,
    //                 "Ошибка сохранения файлов в БД!"
    //             );
    //         }
    //     } catch (\Exception) {
    //         new ApiResponse(
    //             500,
    //             false,
    //             "Ошибка сохранения файлов в БД!"
    //         );
    //     }
    // }


    protected static function randomHash(): string {
        return md5(random_bytes(16));
    }


    protected static function getFileExtension(string $type) {
        return ".".end(explode("/", $type));
    }

}
