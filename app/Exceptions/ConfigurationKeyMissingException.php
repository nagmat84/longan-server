<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

/**
 * ConfigurationKeyMissingException.
 *
 * Returns status code 50 (Precondition failed) to an HTTP client.
 */
class ConfigurationKeyMissingException extends LycheeBaseException
{
	public function __construct(string $msg, \Throwable $previous = null)
	{
		parent::__construct(Response::HTTP_INTERNAL_SERVER_ERROR, $msg, $previous);
	}
}
