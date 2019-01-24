<?php declare(strict_types=1);

namespace Stefna\Logger\Wrapper;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class ChainWrapper extends AbstractLogger
{
	/** @var LoggerInterface[] */
	private $loggers;

	public function __construct(LoggerInterface ...$loggers)
	{
		$this->loggers = $loggers;
	}

	public function addLogger(LoggerInterface $logger): void
	{
		$this->loggers[] = $logger;
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function log($level, $message, array $context = [])
	{
		foreach ($this->loggers as $logger) {
			$logger->log($level, $message, $context);
		}
	}
}
