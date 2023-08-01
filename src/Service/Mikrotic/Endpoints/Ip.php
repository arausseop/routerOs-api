<?php


namespace App\Service\Mikrotic\Endpoints;

use App\Service\Mikrotic\MikroticResponse;

interface Ip
{
	const API_ENDPOINT = 'ip';

	public function pool(array $params = []): MikroticResponse;

	const pool = [
		'required' => [],
		'optional' => [
			'comment'
		],
		'method' => 'GET'
	];

	public function getIpPool(array $params): MikroticResponse;

	const getIpPool = [
		'required' => ['id'],
		'optional' => [],
		'method' => 'GET'
	];
}
