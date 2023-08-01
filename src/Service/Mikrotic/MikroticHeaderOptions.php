<?php

namespace App\Service\Mikrotic;

class MikroticHeaderOptions extends Container
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

	/**
	 * Ip address for the router
	 *
	 * @var string
	 */
	protected $ipAddress;

	/**
	 * Verification Peer for the request
	 *
	 * @var bool
	 */
	protected $verificationPeer;
}
