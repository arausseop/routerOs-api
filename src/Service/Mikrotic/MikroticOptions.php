<?php

namespace App\Service\Mikrotic;

class MikroticOptions extends Container
{

	/**
	 * User name within the account that is allowed to access the service
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * Password for the user name
	 *
	 * @var string
	 */
	protected $password;
}
