<?php

namespace Application\Controllers;

use Internal\Controllers\BaseController;
use Internal\Database\Database;
use Internal\Logger\Logger;
use Redis;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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

		imap_mail($email, 'Test OTP', "Your OTP is $otp", $composed_body);


		$this->response->send(["success" => true], 200);
	}

	function verify()
	{
		$body = $this->request->body;

		$redis = new Redis();

		$successRedisConnect = $redis->connect('127.0.0.1');

		if (!$successRedisConnect) {
			$this->response->send(["error" => "Internal server error"], 500);

			Logger::log('error', "Insert user redis error");

			return;
		}

		$correctOTP = $redis->get($body->username);

		if ($correctOTP != $body->otp) {
			$this->response->send(["error" => "Wrong OTP"], 400);

			return;
		}

		$success =	Database::Update('users', [
			"isVerified" => true
		], [
			"username" => $body->username,
		]);

		if (!$success) {
			$this->response->send(["error" => "Internal server error"], 500);

			Logger::log('error', "Update user db error");

			return;
		}

		$this->response->send(["success" => true], 200);
	}

	function login()
	{
		$body = $this->request->body;

		$users = Database::Get('users', ["username", "isVerified", "password"], [
			"username" => $body->username,
		]);

		$user = $users[0];


		if (!$user) {
			$this->response->send(["error" => "No user found"], 400);

			return;
		}

		if (!password_verify($body->password, $user['password'])) {
			$this->response->send(["error" => "No user found"], 400);

			return;
		}

		if (!$user['isVerified']) {
			$this->response->send(["error" => "User is not verified"], 400);

			return;
		}

		$jwt = JWT::encode([
			"username" => $user['username']
		], "TESTING", 'HS256');

		$this->response->send(["success" => true, "token" => $jwt], 200);
	}
}
