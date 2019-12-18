<?php declare(strict_types=1);

namespace Stefna\Logger\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;

class ConsoleLogger extends AbstractLogger
{
	public const INFO = 'info';
	public const ERROR = 'error';

	private $output;
	private $verbosityLevelMap = [
		LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
		LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL,
		LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
		LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL,
		LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
		LogLevel::NOTICE => OutputInterface::VERBOSITY_VERBOSE,
		LogLevel::INFO => OutputInterface::VERBOSITY_VERY_VERBOSE,
		LogLevel::DEBUG => OutputInterface::VERBOSITY_DEBUG,
	];
	private $formatLevelMap = [
		LogLevel::EMERGENCY => self::ERROR,
		LogLevel::ALERT => self::ERROR,
		LogLevel::CRITICAL => self::ERROR,
		LogLevel::ERROR => self::ERROR,
		LogLevel::WARNING => self::INFO,
		LogLevel::NOTICE => self::INFO,
		LogLevel::INFO => self::INFO,
		LogLevel::DEBUG => self::INFO,
	];
	private $errored = false;

	public function __construct(OutputInterface $output, array $verbosityLevelMap = [], array $formatLevelMap = [])
	{
		$this->output = $output;
		$this->verbosityLevelMap = $verbosityLevelMap + $this->verbosityLevelMap;
		$this->formatLevelMap = $formatLevelMap + $this->formatLevelMap;
	}

	/**
	 * {@inheritdoc}
	 */
	public function log($level, $message, array $context = []): void
	{
		if (!isset($this->verbosityLevelMap[$level])) {
			throw new InvalidArgumentException(sprintf('The log level "%s" does not exist.', $level));
		}

		$output = $this->output;

		// Write to the error output if necessary and available
		if (self::ERROR === $this->formatLevelMap[$level]) {
			if ($output instanceof ConsoleOutputInterface) {
				$output = $output->getErrorOutput();
			}
			$this->errored = true;
		}

		// the if condition check isn't necessary -- it's the same one that $output will do internally anyway.
		// We only do it for efficiency here as the message formatting is relatively expensive.
		if ($output->getVerbosity() >= $this->verbosityLevelMap[$level]) {
			$output->writeln(sprintf(
				'<%1$s>[%2$s] %3$s</%1$s>',
				$this->formatLevelMap[$level],
				$level,
				$this->interpolate($message, $context)
			), $this->verbosityLevelMap[$level]);
		}
	}

	/**
	 * Returns true when any messages have been logged at error levels.
	 *
	 * @return bool
	 */
	public function hasErrored(): bool
	{
		return $this->errored;
	}

	/**
	 * Interpolates context values into the message placeholders.
	 *
	 * @author PHP Framework Interoperability Group
	 */
	private function interpolate(string $message, array $context): string
	{
		if (!$context) {
			return $message;
		}

		return $message . ': ' . json_encode($context, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	}

	public function setOutput(OutputInterface $output): void
	{
		$this->output = $output;
	}

	public function getOutput(): OutputInterface
	{
		return $this->output;
	}

	private $disabledOutput;
	public function disableOutput(): void
	{
		if (!$this->disabledOutput) {
			$this->disabledOutput = $this->output;
			$this->output = new NullOutput();
		}
	}

	public function restoreOutput(): void
	{
		$this->output = $this->disabledOutput;
		$this->disabledOutput = null;
	}
}
