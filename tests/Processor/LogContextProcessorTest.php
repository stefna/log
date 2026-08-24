<?php declare(strict_types=1);

namespace Stefna\Logger\Processor;

use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;
use Stefna\Logger\LogContext;

final class LogContextProcessorTest extends TestCase
{
	protected function setUp(): void
	{
		LogContext::clear();
	}

	protected function tearDown(): void
	{
		LogContext::clear();
	}

	public function testSetValuesAreMergedIntoRecordContext(): void
	{
		LogContext::set('plate', 'beef');
		$processor = new LogContextProcessor();

		$newRecord = $processor($this->createLogRecord());

		$this->assertSame([
			'plate' => 'beef',
		], $newRecord->context);
	}

	public function testSetValueAppearsInRecordContext(): void
	{
		LogContext::set('scope', 'import');
		$processor = new LogContextProcessor();

		$newRecord = $processor($this->createLogRecord());

		$this->assertSame([
			'scope' => 'import',
		], $newRecord->context);
	}

	public function testExplicitRecordContextOverridesTrackedValue(): void
	{
		LogContext::set('plate', 'beef');
		$processor = new LogContextProcessor();

		$newRecord = $processor($this->createLogRecord([
			'plate' => 'pork',
		]));

		$this->assertSame([
			'plate' => 'pork',
		], $newRecord->context);
	}

	public function testReturnsSameRecordWhenNoContextIsSet(): void
	{
		$processor = new LogContextProcessor();

		$record = $this->createLogRecord();

		$this->assertSame($record, $processor($record));
	}

	/**
	 * @param array<mixed> $context
	 */
	private function createLogRecord(array $context = []): LogRecord
	{
		return new LogRecord(
			new \DateTimeImmutable(),
			'test',
			Level::Info,
			'test',
			$context,
		);
	}
}

