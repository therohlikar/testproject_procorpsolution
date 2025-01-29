<?php declare(strict_types = 1);

namespace Contributte\Middlewares\DI;

use Contributte\Middlewares\Exception\InvalidStateException;
use Contributte\Middlewares\Tracy\DebugChainBuilder;
use Contributte\Middlewares\Tracy\MiddlewaresPanel;
use Contributte\Middlewares\Utils\ChainBuilder;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @method stdClass getConfig()
 */
abstract class AbstractMiddlewaresExtension extends CompilerExtension
{

	public const MIDDLEWARE_TAG = 'middleware';

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'middlewares' => Expect::arrayOf(
				Expect::anyOf(Expect::string(), Expect::type(Statement::class))
			),
			'debug' => Expect::bool(false),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		// Register middleware chain builder
		$chain = $builder->addDefinition($this->prefix('chain'))
			->setAutowired(false);

		if (!$config->debug) {
			$chain->setFactory(ChainBuilder::class);
		} else {
			$chain->setFactory(DebugChainBuilder::class);

			$builder->addDefinition($this->prefix('middlewaresPanel'))
				->setFactory(MiddlewaresPanel::class, [$chain]);
		}
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		// Compile defined middlewares
		if ($config->middlewares !== []) {
			$this->compileDefinedMiddlewares();

			return;
		}

		// Compile tagged middlewares
		if ($builder->findByTag(self::MIDDLEWARE_TAG) !== []) {
			$this->compileTaggedMiddlewares();

			return;
		}

		throw new InvalidStateException('There must be at least one middleware registered or added by tag.');
	}

	public function afterCompile(ClassType $class): void
	{
		$config = $this->getConfig();

		if (!$config->debug) {
			return;
		}

		$initialize = $class->getMethod('initialize');
		$initialize->addBody(
			'$this->getService(?)->addPanel($this->getService(?));',
			['tracy.bar', $this->prefix('middlewaresPanel')]
		);
	}

	private function compileDefinedMiddlewares(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig();

		// Obtain middleware chain builder
		$chain = $builder->getDefinition($this->prefix('chain'));
		assert($chain instanceof ServiceDefinition);

		// Add middleware services to chain
		$counter = 0;
		foreach ($config->middlewares as $service) {
			if (is_string($service) && str_starts_with($service, '@')) {
				// Re-use existing service
				$middlewareDef = $builder->getDefinition(substr($service, 1));
			} elseif ($service instanceof Statement || is_string($service)) {
				// Create middleware as service
				$middlewareDef = $builder->addDefinition($this->prefix('middleware' . $counter++))
					->setFactory($service);
			} else {
				throw new InvalidStateException('Unsupported middleware definition');
			}

			// Append to chain of middlewares
			$chain->addSetup('add', [$middlewareDef]);
		}
	}

	private function compileTaggedMiddlewares(): void
	{
		$builder = $this->getContainerBuilder();

		// Find all definitions by tag
		$definitions = $builder->findByTag(self::MIDDLEWARE_TAG);

		// Ensure we have at least 1 service
		if ($definitions === []) {
			throw new InvalidStateException(sprintf('No services with tag "%s"', self::MIDDLEWARE_TAG));
		}

		// Sort by priority
		uasort($definitions, function (array $a, array $b) {
			$p1 = $a['priority'] ?? 10;
			$p2 = $b['priority'] ?? 10;

			if ($p1 === $p2) {
				return 0;
			}

			return ($p1 < $p2) ? -1 : 1;
		});

		// Obtain middleware chain builder
		$chain = $builder->getDefinition($this->prefix('chain'));
		assert($chain instanceof ServiceDefinition);

		// Add middleware services to chain
		foreach ($definitions as $name => $tag) {
			// Append to chain of middlewares
			$chain->addSetup('add', [$builder->getDefinition($name)]);
		}
	}

}
