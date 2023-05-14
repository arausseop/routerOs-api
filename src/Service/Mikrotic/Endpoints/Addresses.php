<?php


namespace App\Service\Mikrotic\Endpoints;

interface Addresses
{

	// public function showAddressReportExtern(array $params = []): MikroticResponse;
	public function showAddressRouter(array $params = []);

	const showAddressRouter = [
		'required' => [],
		'optional' => [],
	];
}
