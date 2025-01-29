<?php declare(strict_types = 1);

namespace Apitte\Core\Exception\Api;

use Throwable;

class MessageException extends ClientErrorException
{

	public function __construct(string $errmessage = '', int $code = 400, ?Throwable $previous = null, ?string $message = null)
	{
		parent::__construct($errmessage, $code, $previous, $message);
	}

	/**
	 * @param string|string[] $message
	 * @return static
	 */
	public function withMessage(string|array $message): static
	{
		parent::withMessage($message);

		$message = is_array($message) ? $message : [$message];

		return $this->withTypedContext('message', $message);
	}

}
