<?php declare(strict_types=1);

namespace Stefna\Logger;

/**
 * Static context facade for adding context to all log entries.
 *
 * Every key holds a single value for the current request scope. The last
 * `set()` for a key can be undone once with `undo()`.
 */
final class LogContext
{
	/** @var array<string, mixed> */
	private static array $context = [];

	public static function set(string $key, mixed $value): void
	{
		self::$context[$key] = $value;
	}

	public static function unset(string $key): void
	{
		unset(self::$context[$key]);
	}

	public static function has(string $key): bool
	{
		return array_key_exists($key, self::$context);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function getAll(): array
	{
		return self::$context;
	}

	public static function clear(): void
	{
		self::$context = [];
	}
}
