<?php declare(strict_types=1);

namespace Stefna\Logger\Di;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Stefna\DependencyInjection\Container;
use Stefna\DependencyInjection\Definition\DefinitionArray;
use Stefna\DependencyInjection\Helper\Autowire;
use Stefna\Logger\Di\Stub\CustomDomain;
use Stefna\Logger\Logger\TestLogger;

final class LogChannelTest extends TestCase
{
	public function testLogChannel(): void
	{
		$logger = new TestLogger();
		$container = new Container(new DefinitionArray([
			LoggerInterface::class => fn () => $logger,
			CustomDomain::class => Autowire::cls(),
		]));

		$domain = $container->get(CustomDomain::class);
		$domain->logger->info('test');

		$logEntry = $logger->getLogAt(0);
		$this->assertArrayHasKey('channel', $logEntry['context']);
		$this->assertSame('test-channel', $logEntry['context']['channel']);
	}

	public function testLogChannelDontOverrideManuallySetChannel(): void
	{
		$logger = new TestLogger();
		$container = new Container(new DefinitionArray([
			LoggerInterface::class => fn () => $logger,
			CustomDomain::class => Autowire::cls(),
		]));

		$domain = $container->get(CustomDomain::class);
		$domain->logger->info('test', [
			'channel' => 'manual-channel',
		]);

		$logEntry = $logger->getLogAt(0);
		$this->assertArrayHasKey('channel', $logEntry['context']);
		$this->assertSame('manual-channel', $logEntry['context']['channel']);
	}
}
