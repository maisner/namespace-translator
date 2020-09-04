<?php declare(strict_types = 1);

namespace Wavevision\NamespaceTranslator\Loaders\TranslationClass;

use Nette\SmartObject;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Wavevision\DIServiceAnnotation\DIService;

/**
 * @DIService(generateInject=true)
 */
class SerializeClassConstFetch
{

	use SmartObject;

	private const C = 'c:';

	private const D_CLASS_PART_SEPARATOR = '\\';

	public const D_SEPARATOR = '-';

	public function serialize(ClassConstFetch $classConstFetch): string
	{
		return self::C . implode(
				self::D_CLASS_PART_SEPARATOR,
				$classConstFetch->class->parts
			) . self::D_SEPARATOR . $classConstFetch->name->name;
	}

	public function isSerialized(string $string): bool
	{
		return strpos($string, self::C) === 0;
	}

	public function deserialize(string $string): ClassConstFetch
	{
		$string = substr($string,  strlen(self::C), strlen($string));
		[$class, $name] = explode(self::D_SEPARATOR, $string);
		return new ClassConstFetch(new Name(explode(self::D_CLASS_PART_SEPARATOR, $class)), new Identifier($name));
	}

}