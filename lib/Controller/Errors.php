<?php

namespace OCA\BigBlueButton\Controller;

use Closure;
use Exception;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use OCA\BigBlueButton\Service\RoomNotFound;
use OCA\BigBlueButton\Service\RoomShareNotFound;

trait Errors
{
	protected function handleNotFound(Closure $callback): DataResponse
	{
		try {
			$return = $callback();
			return ($return instanceof DataResponse) ? $return : new DataResponse($return);
		} catch (Exception $e) {
			if ($e instanceof RoomNotFound ||
				$e instanceof RoomShareNotFound) {
				$message = ['message' => $e->getMessage()];
				return new DataResponse($message, Http::STATUS_NOT_FOUND);
			}

			throw $e;
		}
	}
}
