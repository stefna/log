<?php declare(strict_types=1);

namespace Stefna\Logger;

use PHPUnit\Framework\TestCase;

final class LogContextTest extends TestCase
{
	protected function setUp(): void
	{
		LogContext::clear();
	}

	protected function tearDown(): void
	{
		LogContext::clear();
	}

	public function testSetStoresValue(): void
	{
		LogContext::set('plate', 'beef');

		$this->assertTrue(LogContext::has('plate'));
		$this->assertSame(['plate' => 'beef'], LogContext::getAll());
	}

	public function testSetReplacesPreviousValue(): void
	{
		LogContext::set('plate', 'beef');
		LogContext::set('plate', 'pork');

		$this->assertSame(['plate' => 'pork'], LogContext::getAll());
	}

	public function testHas(): void
	{
		$this->assertFalse(LogContext::has('plate'));

		LogContext::set('plate', 'beef');

		$this->assertTrue(LogContext::has('plate'));
	}

	public function testUnsetRemovesKey(): void
	{
		LogContext::set('plate', 'beef');

		LogContext::unset('plate');

		$this->assertFalse(LogContext::has('plate'));
		$this->assertSame([], LogContext::getAll());
	}

	public function testUnsetUnknownKeyIsNoop(): void
	{
		LogContext::set('plate', 'beef');

		LogContext::unset('unknown');

		$this->assertSame(['plate' => 'beef'], LogContext::getAll());
	}

	public function testUnsetOnlyAffectsSingleKey(): void
	{
		LogContext::set('plate', 'beef');
		LogContext::set('scope', 'import');

		LogContext::unset('plate');

		$this->assertFalse(LogContext::has('plate'));
		$this->assertSame(['scope' => 'import'], LogContext::getAll());
	}

	public function testClearResetsEverything(): void
	{
		LogContext::set('plate', 'beef');
		LogContext::set('scope', 'import');

		LogContext::clear();

		$this->assertSame([], LogContext::getAll());
		$this->assertFalse(LogContext::has('plate'));
		$this->assertFalse(LogContext::has('scope'));
	}

	public function testGetAllReturnsAllValues(): void
	{
		LogContext::set('plate', 'beef');
		LogContext::set('scope', 'import');

		$this->assertSame([
			'plate' => 'beef',
			'scope' => 'import',
		], LogContext::getAll());
	}
}
