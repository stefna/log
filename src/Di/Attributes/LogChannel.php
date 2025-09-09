<?php declare(strict_types=1);

namespace Stefna\Logger\Di\Attributes;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Stefna\DependencyInjection\Helper\Attribute\ConfigureAttribute;
use Stefna\DependencyInjection\Helper\Attribute\ResolverAttribute;
use Stefna\Logger\ManagerInterface;
use Stefna\Logger\Wrapper\ChannelWrapper;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class LogChannel implements ResolverAttribute
{
	public function __construct(
		private readonly string $channel,
	) {}

	public function resolve(string $type, ContainerInterface $container): mixed
	{
		if ($container->has(ManagerInterface::class)) {
			return $container->get(ManagerInterface::class)->getLogger($this->channel);
		}
		$object = $container->get($type);
		if ($object instanceof LoggerInterface && class_exists(ChannelWrapper::class)) {
			return new ChannelWrapper($object, $this->channel);
		}

		// don't know how to add the channel just return the incoming logger
		return $object;
	}
}
