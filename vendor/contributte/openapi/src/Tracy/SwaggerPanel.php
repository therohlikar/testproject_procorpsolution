<?php declare(strict_types = 1);

namespace Contributte\OpenApi\Tracy;

use Contributte\OpenApi\Schema\OpenApi;
use Tracy\IBarPanel;

class SwaggerPanel implements IBarPanel
{

	public const EXPANSION_FULL = 'full';
	public const EXPANSION_LIST = 'list';
	public const EXPANSION_NONE = 'none';

	public const EXPANSIONS = [
		self::EXPANSION_FULL,
		self::EXPANSION_LIST,
		self::EXPANSION_NONE,
	];

	protected ?string $url = null;

	protected string $expansion = self::EXPANSION_LIST;

	protected string|bool $filter = false;

	protected string $title = 'OpenAPI';

	/** @var array<mixed> */
	protected array $spec = [];

	/** @var callable|null */
	protected $specCallback = null;

	/**
	 * @param array<mixed> $spec
	 */
	public function setSpec(array $spec): void
	{
		$this->spec = $spec;
	}

	public function setSpecCallback(callable $callback): void
	{
		$this->specCallback = $callback;
	}

	public function setUrl(?string $url): void
	{
		$this->url = $url;
	}

	public function setExpansion(string $expansion): void
	{
		$this->expansion = $expansion;
	}

	public function setFilter(string|bool $filter): void
	{
		$this->filter = $filter;
	}

	public function setTitle(string $title): void
	{
		$this->title = $title;
	}

	/**
	 * Renders HTML code for custom tab.
	 */
	public function getTab(): string
	{
		return '<span title="Swagger UI"><img style="height: 16px; width: auto; display: inline;" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAB4AAAAeCAYAAAA7MK6iAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAqRJREFUeNrEVz1s00AUfnGXii5maMXoEUEHVwIpEkPNgkBdMnQoU5ytiKHJwpp2Q2JIO8DCUDOxIJFIVOoWZyJSh3pp1Q2PVVlcCVBH3ufeVZZ9Zye1Ay86nXV+ue/9fO/lheg/Se02X1rvksmbnTiKvuxQMBNgBnN4a/LCbmnUAP6JV58NCUsBC8CuAJxGPF47OgNqBaA93tolUhnx6jC4NxGwyOEwlccyAs+3kwdzKq0HDn2vEBTi8J2XpyMaywNDE157BhXUE3zJhlq8GKq+Zd2zaWHepPA8oN9XkfLmRdOiJV4XUUg/IyWncLjCYY/SHndV2u7zHr3bPKZtdxgboJOnthvrfGj/oMf3G0r7JVmNlLfKklmrt2MvvcNO7LFOhoFHfuAJI5o6ta10jpt5CQLgwXhXG2YIwvu+34qf78ybOjWTnWwkgR36d7JqJOrW0hHmNrKg9xhiS4+1jFmrxymh03B0w+6kURIAu3yHtOD5oaUNojMnGgbcctNvwdAnyxvxRR+/vaJnjzbpzcZX+nN1SdGv85i9eH8w3qPO+mdm/y4dnQ1iI8Fq6Nf4cxL6GWSjiFDSs0VRnxC5g0xSB2cgHpaseTxfqOv5uoHkNQ6Ha/N1Yz9mNMppEkEkYKj79q6uCq4bCHcSX3fJ0Vk/k9siASjCm1N6gZH6Ec9IXt2WkFES2K/ixoIyktJPAu/ptOA1SgO5zqtr6KASJPF0nMV8dgMsRhRPOcMwqQAOoi0VAIMLAEWJ6YYC1c8ibj1GP51RqwzYwZVMHQuvOzMCBUtb2tGHx5NAdLKqp5AX7Ng4d+Zi8AGDI9z1ijx9yaCH04y3GCP2S+QcvaGl+pcxyUBvinFlawoDQjHSelX8hQEoIrAq8p/mgC88HOS1YCl/BRgAmiD/1gn6Nu8AAAAASUVORK5CYII="/> ' . $this->title . ' </span>';
	}

	/**
	 * Renders HTML code for custom panel.
	 */
	public function getPanel(): string
	{
		ob_start();
		// @codingStandardsIgnoreStart
		if ($this->specCallback !== null) {
			$spec = call_user_func($this->specCallback);
			if ($spec instanceof OpenApi) {
				$spec = $spec->toArray();
			}
		} else {
			$spec = $this->spec;
		}
		$url = $this->url;
		$expansion = $this->expansion;
		$filter = $this->filter;
		// @codingStandardsIgnoreEnd
		require __DIR__ . '/templates/panel.phtml';

		return (string) ob_get_clean();
	}

}
