<?php

namespace Application\Routes;

use Internal\Routes\BaseRoutes;

use Internal\Middlewares\Default\Security;

class Routes extends BaseRoutes
{
	public function __construct()
	{
		// Pass true as first argument if this is root route
		parent::__construct(true);

		$this->use('auth', Security::CORS(["http://localhost:3000"]), new AuthRoutes);
	}
}
