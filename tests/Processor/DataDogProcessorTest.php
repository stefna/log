<?php declare(strict_types=1);

namespace Stefna\Logger\Processor;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Stefna\Logger\Processor\DatadogProcessor;

final class DataDogProcessorTest extends TestCase
{
	public function testSimpleException(): void
	{
		$processor = new DataDogProcessor();
		$exception = new \RuntimeException('test exception');
		$logRecord = new LogRecord(
			new \DateTimeImmutable(),
			'test',
			Level::Info,
			'test',
			[
				'exception' => $exception,
			],
		);
		$newLogRecord = $processor($logRecord);

		$this->assertNotSame($logRecord, $newLogRecord);

		$this->assertArrayHasKey('error.message', $newLogRecord->context);
		$this->assertArrayHasKey('error.stack', $newLogRecord->context);
		$this->assertArrayHasKey('error.kind', $newLogRecord->context);

		$this->assertSame('test exception', $newLogRecord->context['error.message']);
		$this->assertSame('RuntimeException', $newLogRecord->context['error.kind']);;
	}

	public function testNoChangeIfContextDontHaveException(): void
	{
		$processor = new DataDogProcessor();
		$logRecord = new LogRecord(
			new \DateTimeImmutable(),
			'test',
			Level::Info,
			'test',
			[
				'test' => 1,
			],
		);
		$newLogRecord = $processor($logRecord);

		$this->assertSame($logRecord, $newLogRecord);
	}

	public function testNoChangeIfContextHaveStrangeExceptionType(): void
	{
		$processor = new DataDogProcessor();
		$logRecord = new LogRecord(
			new \DateTimeImmutable(),
			'test',
			Level::Info,
			'test',
			[
				'exception' => 1,
			],
		);
		$newLogRecord = $processor($logRecord);

		$this->assertSame($logRecord, $newLogRecord);
	}

	public function testExcludeFrames(): void
	{
		$processor = new DataDogProcessor([
			[
				'file' => 'vendor/phpunit/phpunit/src/Framework/TestSuite.php',
			]
		]);
		$exception = new \RuntimeException('test exception');
		$logRecord = new LogRecord(
			new \DateTimeImmutable(),
			'test',
			Level::Info,
			'test',
			[
				'exception' => $exception,
			],
		);
		$newLogRecord = $processor($logRecord);

		$this->assertNotSame($logRecord, $newLogRecord);

		$this->assertArrayHasKey('error.message', $newLogRecord->context);
		$this->assertArrayHasKey('error.stack', $newLogRecord->context);
		$this->assertArrayHasKey('error.kind', $newLogRecord->context);

		$this->assertSame('test exception', $newLogRecord->context['error.message']);
		$this->assertSame('RuntimeException', $newLogRecord->context['error.kind']);;

		$this->assertStringNotContainsString('TestSuite.php', $newLogRecord->context['error.stack']);
	}
}
