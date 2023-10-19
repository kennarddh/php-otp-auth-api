<?php

namespace Application\Controllers;

use Exception as GlobalException;
use Internal\Controllers\BaseController;
use Internal\Database\Database;
use Internal\Logger\Logger;
use Redis;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


use League\OAuth2\Client\Provider\Google;

final class Auth extends BaseController
{
	function register()
	{
		$body = $this->request->body;

		$success =	Database::Insert('users', [
			"email" => $body->email,
			"username" => $body->username,
			"password" => password_hash($body->password, PASSWORD_DEFAULT),
			"isVerified" => false
		]);

		if (!$success) {
			$this->response->send(["error" => "Internal server error"], 500);

			Logger::log('error', "Insert user db error");

			return;
		}

		$this->response->send(["username" => $body->username], 201);
	}

	function verifySend()
	{
		$body = $this->request->body;

		$users = Database::Get('users', ["email", "isVerified"], [
			"username" => $body->username,
		]);

		$user = $users[0];

		if (!$user) {
			$this->response->send(["error" => "No user found with the username"], 400);

			return;
		}

		if ($user["isVerified"]) {
			$this->response->send(["error" => "User is already verified"], 400);

			return;
		}

		$email = $user["email"];

		$redis = new Redis();

		$successRedisConnect = $redis->connect('127.0.0.1');

		if (!$successRedisConnect) {
			$this->response->send(["error" => "Internal server error"], 500);

			Logger::log('error', "Insert user redis error");

			return;
		}

		$otp = random_int(100000, 999999);

		$redis->set($body->username, $otp, 2 * 60);

		$envelope["from"] = "No Reply <no-reply@localhost>";

		$part["type"] = TYPETEXT;
		$part["subtype"] = "plain";
		$part["description"] = "description3";

		$composed_body = imap_mail_compose($envelope, [$part]);

		imap_mail('kennarddh@localhost', 'Test OTP', "Your OTP is $otp", $composed_body);


		$this->response->send(["success" => true], 200);
	}

	public function redirect()
	{
		$this->response->send(["status" => "Redirected"], 200);
	}
}
