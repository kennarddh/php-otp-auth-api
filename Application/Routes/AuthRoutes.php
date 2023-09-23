<?php

namespace Application\Routes;

use Internal\Routes\BaseRoutes;

class AuthRoutes extends BaseRoutes
{
	public function __construct()
	{
		parent::__construct();

		$this->post('register', 'Auth::register');
	}
}
