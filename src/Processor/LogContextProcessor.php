<?php declare(strict_types=1);

namespace Stefna\Logger\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Stefna\Logger\LogContext;

/**
 * Processor that merges the current {@see LogContext} into the context of
 * every log record processed.
 */
final class LogContextProcessor implements ProcessorInterface
{
	public function __invoke(LogRecord $record): LogRecord
	{
		$context = LogContext::getAll();
		if ($context === []) {
			return $record;
		}

		return $record->with(
			context: array_merge($context, $record->context),
		);
	}
}
