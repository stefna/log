<?php declare(strict_types=1);

namespace Stefna\Logger\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Stefna\Logger\Handler\DatadogHandler;

final class DatadogProcessor implements ProcessorInterface
{
	public function __invoke(LogRecord $record): LogRecord
	{
		$context = $record->context;
		if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
			$context['error.message'] = $context['exception']->getMessage();
			$context['error.stack'] = $context['exception']->getTraceAsString();
			$context['error.kind'] = $context['exception']::class;
			unset($context['exception']);
		}

		return $record->with(context: $context);
	}
}
