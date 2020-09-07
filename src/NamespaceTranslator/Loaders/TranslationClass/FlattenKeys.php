<?php declare(strict_types = 1);

namespace Wavevision\NamespaceTranslator\Loaders\TranslationClass;

use Nette\SmartObject;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use PhpParser\PrettyPrinter\Standard;
use Wavevision\DIServiceAnnotation\DIService;

/**
 * @todo rename
 * @DIService(generateInject=true)
 */
class FlattenKeys
{

	use SmartObject;
	use InjectSerializeClassConstFetch;

	/**
	 * @return array<mixed>
	 */
	public function process(Array_ $array): array
	{
		$output = [];
		/** @var ArrayItem $item */
		foreach ($array->items as $item) {
			$output[$this->key($item)] = $this->value($item);
		}
		return $output;
	}

	/**
	 * @return array<mixed>|string
	 */
	private function value(ArrayItem $item)
	{
		$value = $item->value;
		if ($value instanceof String_) {
			return $value->value;
		} elseif ($value instanceof Array_) {
			return $this->process($value);
		} else {
			return (new Standard())->prettyPrintExpr($value);
		}
	}

	private function key(ArrayItem $item): string
	{
		$key = $item->key;
		if ($key instanceof String_) {
			return $key->value;
		}
		if ($key instanceof ClassConstFetch) {
			return $this->serializeClassConstFetch->serialize($key);
		}
		throw new \Exception('todo');
	}

}
