<?php declare(strict_types = 1);

namespace Contributte\Psr7\Nette;

use Nette\Application\Request as ApplicationRequest;
use Nette\Http\IRequest as HttpRequest;

trait NetteRequestTrait
{

	protected HttpRequest $httpRequest;

	protected ApplicationRequest $applicationRequest;

	public function getHttpRequest(): HttpRequest
	{
		return $this->httpRequest;
	}

	public function withHttpRequest(HttpRequest $request): static
	{
		$new = clone $this;
		$new->httpRequest = $request;

		return $new;
	}

	public function getApplicationRequest(): ApplicationRequest
	{
		return $this->applicationRequest;
	}

	public function withApplicationRequest(ApplicationRequest $request): static
	{
		$new = clone $this;
		$new->applicationRequest = $request;

		return $new;
	}

}
