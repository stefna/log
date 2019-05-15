<?php declare(strict_types=1);

namespace Stefna\Logger\Logger;

use Psr\Log\AbstractLogger;

class SystemErrorLogger extends AbstractLogger
{
	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function log($level, $message, array $context = []): void
	{
		$messageFormat = "[%s] %s: %s %s\n";
		$message = sprintf($messageFormat, date('Y-m-d H:i:s:v'), $level, $message, json_encode($context));

		/** @noinspection ForgottenDebugOutputInspection */
		error_log($message);
	}
}
