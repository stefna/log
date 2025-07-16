<?php declare(strict_types=1);

namespace Stefna\Logger\Formatter;

use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

final class DataDogFormatter extends JsonFormatter
{
	protected function normalizeRecord(LogRecord $record): array
	{
		/**
		 * @var array{
		 *     context: array{
		 *         "error.kind"?: string,
		 *         "error.message": string,
		 *         "error.stack": string,
		 *         exception?: mixed,
		 *     },
		 *     ddsource?: string,
		 *     ddtags?: string,
		 *     logger?: mixed
		 * } $data
		 */
		$data = parent::normalizeRecord($record);
		/**
		 * @var array{
		 *     exception?: mixed,
		 *     "error.kind"?: string,
		 *     ddtags?: string,
		 *     traceId?: string,
		 * } $context
		 */
		$context = $record->context;

		if (isset($data['context']['error.kind'])) {
			$data['error'] = [
				'message' => $data['context']['error.message'],
				'stack' => $data['context']['error.stack'],
				'kind' => $data['context']['error.kind'],
			];
			unset($data['context']['error.kind'], $data['context']['error.message'], $data['context']['error.stack']);
		}
		elseif (isset($context['exception']) && $context['exception'] instanceof \Throwable) {
			$data['error'] = [
				'message' => $context['exception']->getMessage(),
				'stack' => $context['exception']->getTraceAsString(),
				'kind' => $context['exception']::class,
			];
			unset($data['context']['exception']);
		}

		$data['source'] = 'php';
		if (!isset($data['ddsource'])) {
			$data['ddsource'] = $data['source'];
		}
		if (!isset($data['ddtags']) && isset($context['ddtags'])) {
			$data['ddtags'] = $context['ddtags'];
		}
		if (isset($context['traceId'])) {
			$data['dd']['trace_id'] = $context['traceId'];
		}
		if (isset($record->extra['version'])) {
			$data['dd']['version'] = $record->extra['version'];
		}
		if (!isset($data['logger'])) {
			$data['logger'] = [
				'channel' => $record->channel,
				'message' => $record->message,
			];
		}

		return $data;
	}
}
