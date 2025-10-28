<?php
namespace App\Forms;

use App\Handlers\HttpHandler;


abstract class Form {
	public string $_message = "";


	public static function check() {
		$form = new self([
			...HttpHandler::$request->body,
			...HttpHandler::$request->params
		]);

		if (
			!$form->isValidate() ||
			!$form->load()
		) {
			HttpHandler::$response->setFinished(
                new ApiResponse(
                    400,
                    false,
                    "Некоторые поля не заполнены!"
                )
            );
		}
	}

	public function __construct(array $args);
	public function isValidate();
	public function load();
}