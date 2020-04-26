<?php

namespace OCA\BigBlueButton\Controller;

use Closure;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\BigBlueButton\Service\RoomNotFound;

trait Errors
{
	protected function handleNotFound(Closure $callback): DataResponse
	{
		try {
			return new DataResponse($callback());
		} catch (RoomNotFound $e) {
			$message = ['message' => $e->getMessage()];
			return new DataResponse($message, Http::STATUS_NOT_FOUND);
		}
	}
}
