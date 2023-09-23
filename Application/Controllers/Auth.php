<?php

namespace Application\Controllers;

use Internal\Controllers\BaseController;
use Internal\Database\Database;
use Internal\Logger\Logger;

final class Auth extends BaseController
{
	function register()
	{
		$body = $this->request->body;

		$success =	Database::Insert('users', [
			"email" => $body->email,
			"username" => $body->username,
			"password" => password_hash($body->password, PASSWORD_DEFAULT)
		]);

		if (!$success) {
			$this->response->send(["error" => "Internal server error"], 500);

			Logger::log('error', "Insert user error");

			return;
		}

		$this->response->send([], 201);
	}
}
