<?php declare(strict_types=1);

namespace Stefna\Logger\PII;

use Stefna\Logger\PII\Anonymizer\Anonymizer;
use Stefna\Logger\PII\Anonymizer\CardAnonymizer;
use Stefna\Logger\PII\Anonymizer\PersonAnonymizer;

final class Processor
{
	/** @var Anonymizer[] */
	private $anonymizers;

	public function __construct(Anonymizer ...$anonymizers)
	{
		$this->anonymizers = $anonymizers;
		if (!$this->anonymizers) {
			$this->anonymizers[] = new CardAnonymizer();
			$this->anonymizers[] = new PersonAnonymizer();
		}
	}

	public function addAnonymizer(Anonymizer $anonymizer): void
	{
		$this->anonymizers[] = $anonymizer;
	}

	/**
	 * @param array{context: array<string, mixed>} $record
	 * @return array{context: array<string, mixed>}
	 */
	public function __invoke(array $record): array
	{
		$record['context'] = $this->processContext($record['context']);

		return $record;
	}

	/**
	 * @param array<string, mixed> $context
	 * @return array<string, mixed>
	 */
	private function processContext(array $context): array
	{
		foreach ($context as $key => $value) {
			if (is_array($value)) {
				$context[$key] = $this->processContext($value);
				continue;
			}
			if (!is_string($key)) {
				// skip lists and invalid key types
				continue;
			}
			foreach ($this->anonymizers as $anonymizer) {
				if (!$anonymizer->support($key)) {
					continue;
				}
				$value = $anonymizer->process($key, $value);
				if ($value === null) {
					unset($context[$key]);
					continue 2;
				}
				$context[$key] = $value;
			}
		}
		return $context;
	}
}
