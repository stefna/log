<?php declare(strict_types=1);

namespace Stefna\Logger\Formatter;

use Monolog\Formatter\JsonFormatter;

final class CloudWatchFormatter extends JsonFormatter
{
	/**
	 * @param array{
	 * 		datetime?: \DateTimeInterface|null,
	 * 		level_name: string,
	 * 		context: array{requestId?: string}
	 * } $record
	 */
	public function format(array $record): string
	{
		$dateTime = $record['datetime'] ?? null;
		if (!$dateTime instanceof \DateTimeInterface) {
			// this should be impossible
			$dateTime = new \DateTimeImmutable();
		}
		$record['datetime'] = $dateTime->format('Y-m-d\TH:i:s.up');
		$line = parent::format($record);
		$requestId = $record['context']['requestId'] ?? 'empty';

		$dateFormat = 'Y-m-d\TH:i:s.v';
		if (PHP_VERSION_ID > 80000) {
			$dateFormat .= 'p';
		}
		else {
			// for php < 8 just hard code timezone to utc
			$dateFormat .= '\Z';
		}

		$line = sprintf(
			"%s\t%s\t%s\t%s",
			$dateTime->format($dateFormat),
			$requestId,
			$record['level_name'],
			$line
		);

		return $line;
	}
}
