<?php declare(strict_types = 1);

namespace Contributte\Middlewares\UI;

use Contributte\Middlewares\PresenterMiddleware;
use Nette\Application\IPresenterFactory;
use Nette\Routing\Router;

class PresenterMiddlewareFactory implements IPresenterMiddlewareFactory
{

	protected IPresenterFactory $presenterFactory;

	protected Router $router;

	public function __construct(IPresenterFactory $presenterFactory, Router $router)
	{
		$this->presenterFactory = $presenterFactory;
		$this->router = $router;
	}

	public function create(): PresenterMiddleware
	{
		return new PresenterMiddleware($this->presenterFactory, $this->router);
	}

}
