<?php

namespace OCA\BigBlueButton\Middleware;

use OCA\BigBlueButton\Controller\JoinController;
use OCA\BigBlueButton\NoPermissionException;
use OCA\BigBlueButton\NoPermissionResponse;
use OCA\BigBlueButton\NotFoundException;
use OCA\BigBlueButton\NotFoundResponse;
use OCP\AppFramework\Middleware;
use OCP\IRequest;

class JoinMiddleware extends Middleware {
	/** @var IRequest */
	private $request;

	public function __construct(IRequest $request) {
		$this->request = $request;
	}

	/**
	 * @return void
	 */
	public function beforeController($controller, $methodName) {
		if (!($controller instanceof JoinController)) {
			return;
		}

		$token = $this->request->getParam('token');
		if ($token === null) {
			throw new NotFoundException();
		}

		$controller->setToken($token);

		if ($controller->isValidToken()) {
			return;
		}

		throw new NotFoundException();
	}

	public function afterException($controller, $methodName, \Exception $exception) {
		if (!($controller instanceof JoinController)) {
			throw $exception;
		}

		if ($exception instanceof NotFoundException) {
			return new NotFoundResponse();
		}

		if ($exception instanceof NoPermissionException) {
			return new NoPermissionResponse();
		}

		throw $exception;
	}
}
