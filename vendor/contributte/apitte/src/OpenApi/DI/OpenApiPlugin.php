<?php declare(strict_types = 1);

namespace Apitte\OpenApi\DI;

use Apitte\Core\DI\Plugin\Plugin;
use Apitte\Core\Exception\Logical\InvalidArgumentException;
use Apitte\OpenApi\SchemaBuilder;
use Apitte\OpenApi\SchemaDefinition\ArrayDefinition;
use Apitte\OpenApi\SchemaDefinition\BaseDefinition;
use Apitte\OpenApi\SchemaDefinition\CoreDefinition;
use Apitte\OpenApi\SchemaDefinition\Entity\EntityAdapter;
use Apitte\OpenApi\SchemaDefinition\JsonDefinition;
use Apitte\OpenApi\SchemaDefinition\NeonDefinition;
use Apitte\OpenApi\SchemaDefinition\YamlDefinition;
use Contributte\OpenApi\Tracy\SwaggerPanel;
use Nette\DI\Definitions\Statement;
use Nette\PhpGenerator\ClassType;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;

/**
 * @property-read stdClass $config
 */
class OpenApiPlugin extends Plugin
{

	public static function getName(): string
	{
		return 'openapi';
	}

	/**
	 * Register services
	 */
	public function loadPluginConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$global = $this->compiler->getExtension()->getConfig();
		$config = $this->config;

		$builder->addDefinition($this->prefix('entityAdapter'))
			->setFactory(EntityAdapter::class);

		$coreDefinition = $builder->addDefinition($this->prefix('coreDefinition'))
			->setFactory(CoreDefinition::class);

		$schemaBuilder = $builder->addDefinition($this->prefix('schemaBuilder'))
			->setFactory(SchemaBuilder::class);

		if ($config->definitions === []) {
			$schemaBuilder
				->addSetup('addDefinition', [new Statement(BaseDefinition::class)])
				->addSetup('addDefinition', [$coreDefinition]);
			foreach ($config->files as $file) {
				if (str_ends_with($file, '.neon')) {
					$schemaBuilder->addSetup('addDefinition', [new Statement(NeonDefinition::class, [$file])]);
				} elseif (str_ends_with($file, '.yaml') || str_ends_with($file, '.yml')) {
					$schemaBuilder->addSetup('addDefinition', [new Statement(YamlDefinition::class, [$file])]);
				} elseif (str_ends_with($file, '.json')) {
					$schemaBuilder->addSetup('addDefinition', [new Statement(JsonDefinition::class, [$file])]);
				} else {
					throw new InvalidArgumentException(sprintf(
						'We cant parse file "%s" - unsupported file type',
						$file
					));
				}
			}

			$schemaBuilder->addSetup('addDefinition', [new Statement(ArrayDefinition::class, [$config->definition])]);
		} else {
			foreach ($config->definitions as $definitionName => $definitionConfig) {
				$definitionDef = $builder->addDefinition($this->prefix('definition.' . $definitionName))
					->setFactory($definitionConfig)
					->setAutowired(false);

				$schemaBuilder->addSetup('addDefinition', [$definitionDef]);
			}
		}

		if (!$global->debug) {
			return;
		}

		if (!$config->swaggerUi->panel) {
			return;
		}

		$builder->addDefinition($this->prefix('swaggerUi.panel'))
			->setFactory(SwaggerPanel::class)
			->addSetup('setUrl', [$config->swaggerUi->url])
			->addSetup('?->setSpecCallback(fn() => ?)', ['@self', new Statement('@' . $this->prefix('schemaBuilder') . '::build')])
			->addSetup('setExpansion', [$config->swaggerUi->expansion])
			->addSetup('setFilter', [$config->swaggerUi->filter])
			->addSetup('setTitle', [$config->swaggerUi->title])
			->setAutowired(false);
	}

	public function afterPluginCompile(ClassType $class): void
	{
		$global = $this->compiler->getExtension()->getConfig();
		if (!$global->debug) {
			return;
		}

		$config = $this->config;

		$initialize = $class->getMethod('initialize');
		if (!$config->swaggerUi->panel) {
			return;
		}

		$initialize->addBody('$this->getService(?)->addPanel($this->getService(?));', [
			'tracy.bar',
			$this->prefix('swaggerUi.panel'),
		]);
	}

	protected function getConfigSchema(): Schema
	{
		return Expect::structure([
			'definitions' => Expect::arrayOf(Expect::anyOf(Expect::string(), Expect::type(Statement::class))),
			'definition' => Expect::array(),
			'files' => Expect::arrayOf('string'),
			'swaggerUi' => Expect::structure([
				'url' => Expect::string()->nullable(),
				'expansion' => Expect::anyOf(...SwaggerPanel::EXPANSIONS)->default(SwaggerPanel::EXPANSION_LIST),
				'filter' => Expect::bool(true),
				'title' => Expect::string('OpenAPI'),
				'panel' => Expect::bool(false),
			]),
		]);
	}

}
