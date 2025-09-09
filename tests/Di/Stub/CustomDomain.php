<?php declare(strict_types=1);

namespace Stefna\Logger\Di\Stub;

use Psr\Log\LoggerInterface;
use Stefna\Logger\Di\Attributes\LogChannel;

final class CustomDomain
{
	public function __construct(
		#[LogChannel('test-channel')]
		public LoggerInterface $logger,
	) {}
}
