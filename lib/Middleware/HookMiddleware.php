<?php

namespace OCA\BigBlueButton\Middleware;

use OCA\BigBlueButton\Controller\HookController;
use OCA\BigBlueButton\Crypto;
use OCA\BigBlueButton\NoPermissionException;
use OCA\BigBlueButton\NotFoundException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Middleware;
use OCP\IRequest;

class HookMiddleware extends Middleware {
	/** @var IRequest */
	private $request;

	/** @var Crypto */
	private $crypto;

	public function __construct(IRequest $request, Crypto $crypto) {
		$this->request = $request;
		$this->crypto = $crypto;
	}

	public function beforeController($controller, $methodName) {
		if (!($controller instanceof HookController)) {
			return;
		}

		$token = $this->request->getParam('token');
		if ($token === null) {
			throw new NotFoundException();
		}

		$mac = $this->request->getParam('mac');
		if ($mac === null) {
			throw new NoPermissionException();
		}

		if (!$this->crypto->verifyHMAC($token, $mac)) {
			throw new NoPermissionException();
		}

		$controller->setToken($token);

		if ($controller->isValidToken()) {
			return;
		}

		throw new NotFoundException();
	}

	public function afterException($controller, $methodName, \Exception $exception) {
		if (!($controller instanceof HookController)) {
			throw $exception;
		}

		if ($exception instanceof NotFoundException) {
			return new JSONResponse([], Http::STATUS_NOT_FOUND);
		}

		if ($exception instanceof NoPermissionException) {
			return new JSONResponse([], Http::STATUS_FORBIDDEN);
		}

		throw $exception;
	}
}
