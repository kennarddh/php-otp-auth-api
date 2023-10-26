<?php

namespace Application\Routes;

use Internal\Routes\BaseRoutes;

class AuthRoutes extends BaseRoutes
{
	public function __construct()
	{
		parent::__construct();

		$this->post('register', 'Auth::register');
		$this->post('verify/send', 'Auth::verifySend');
		$this->post('verify', 'Auth::verify');
		$this->post('redirect', 'Auth::redirect');
		$this->post('login', 'Auth::login');
	}
}
