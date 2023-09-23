<?php

namespace Application\Controllers;

use Internal\Controllers\BaseController;
use Internal\Database\Database;
use Internal\Logger\Logger;
use Redis;

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

			Logger::log('error', "Insert user db error");

			return;
		}

		$redis = new Redis();

		$successRedisConnect = $redis->connect('127.0.0.1');

		if (!$successRedisConnect) {
			$this->response->send(["error" => "Internal server error"], 500);

			Logger::log('error', "Insert user redis error");

			return;
		}

		$otp = random_int(100000, 999999);

		$redis->set($body->username, $otp, 2 * 60);

		$this->response->send(["otp" => $otp], 201);
	}
}
