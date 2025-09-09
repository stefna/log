<?php declare(strict_types=1);

namespace Stefna\Logger\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Stefna\Logger\Handler\DatadogHandler;

final class DatadogProcessor implements ProcessorInterface
{
	public function __construct(
		/** @var list<array{file: string, line?: int}> */
		private readonly array $framesToExclude = [],
	) {}

	public function __invoke(LogRecord $record): LogRecord
	{
		$context = $record->context;
		if (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
			$context['error.message'] = $context['exception']->getMessage();
			$context['error.stack'] = $this->buildStackTrace($context['exception']->getTrace());
			$context['error.kind'] = $context['exception']::class;
			unset($context['exception']);

			return $record->with(context: $context);
		}

		return $record;
	}

	/**
	 * @param list<array{
	 *      function: string,
	 *      type?: "::"|"->",
	 *      line?: int,
	 *      file?: string,
	 *      class?: string,
	 *      args?: array<mixed>,
	 * }> $stackTrace
	 */
	private function buildStackTrace(array $stackTrace): string
	{
		$filteredFrames = [];

		foreach ($stackTrace as $trace) {
			$excludeFrame = false;
			foreach ($this->framesToExclude as $exclude) {
				if (!isset($trace['file'])) {
					break;
				}
				if (!str_ends_with($trace['file'], $exclude['file'])) {
					continue;
				}
				if (isset($trace['line'], $exclude['line']) && $trace['line'] !== $exclude['line']) {
					continue;
				}
				$excludeFrame = true;
				break;
			}
			if (!$excludeFrame) {
				$filteredFrames[] = $trace;
			}
		}

		return $this->renderStackTrace($filteredFrames);
	}

	/**
	 * @param list<array{
	 *     function: string,
	 *     type?: "::"|"->",
	 *     line?: int,
	 *     file?: string,
	 *     class?: string,
	 *     args?: array<mixed>,
	 * }> $frames
	 */
	private function renderStackTrace(array $frames): string
	{
		$rtn = '';
		foreach ($frames as $index => $frame) {
			$file = '[internal function]';
			if (isset($frame['file'], $frame['line'])) {
				$file = sprintf('%s(%d)', $frame['file'], $frame['line']);
			}
			$renderedArguments = '';
			if (isset($frame['args'])) {
				$args = [];
				foreach ($frame['args'] as $arg) {
					$args[] = match (true) {
						is_string($arg) => "'" . $arg . "'",
						is_array($arg) => 'Array',
						is_null($arg) => 'NULL',
						is_bool($arg) => $arg ? 'true' : 'false',
						is_object($arg) => get_class($arg),
						is_resource($arg) => get_resource_type($arg),
						default => $arg,
					};
				}
				$renderedArguments = implode(', ', $args);
			}
			$rtn .= sprintf(
				"#%d %s: %s%s%s(%s)\n",
				$index,
				$file,
				$frame['class'] ?? '',
				$frame['type'] ?? '',
				$frame['function'],
				$renderedArguments,
			);
		}
		return $rtn;
	}
}
