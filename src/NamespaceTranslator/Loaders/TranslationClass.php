<?php declare (strict_types = 1);

namespace Wavevision\NamespaceTranslator\Loaders;

use Nette\SmartObject;
use ReflectionClass;
use Wavevision\NamespaceTranslator\Exceptions\InvalidState;
use Wavevision\NamespaceTranslator\Exceptions\MissingResource;
use Wavevision\NamespaceTranslator\Exceptions\SkipResource;
use Wavevision\NamespaceTranslator\Loaders\TranslationClass\InjectLoadExport;
use Wavevision\NamespaceTranslator\Loaders\TranslationClass\InjectSaveResource;
use Wavevision\NamespaceTranslator\Resources\LocalePrefixPair;
use Wavevision\NamespaceTranslator\Resources\Translation;
use Wavevision\NamespaceTranslator\Transfer\InjectLocales;
use Wavevision\Utils\Arrays;
use Wavevision\Utils\Strings;
use Wavevision\Utils\Tokenizer\Tokenizer;
use Wavevision\Utils\Tokenizer\TokenizeResult;
use function class_exists;
use function class_implements;
use function in_array;
use function is_file;
use function sprintf;
use function ucfirst;
use const T_CLASS;

class TranslationClass implements Loader
{

	use InjectHelpers;
	use InjectLoadExport;
	use InjectLocales;
	use InjectSaveResource;
	use SmartObject;

	public const FORMAT = 'php';

	private Tokenizer $tokenizer;

	public function __construct()
	{
		$this->tokenizer = new Tokenizer();
	}

	/**
	 * @return array<mixed>
	 */
	public function load(string $resource): array
	{
		$class = $this->getClass($resource);
		/** @var Translation $class */
		return $class::define();
	}

	/**
	 * @inheritDoc
	 */
	public function getLocalePrefixPair(string $resourceName): LocalePrefixPair
	{
		$parts = Strings::split($resourceName, '/(?=[A-Z])/');
		return new LocalePrefixPair(Arrays::pop($parts), Arrays::implode($parts, ''));
	}

	public function fileSuffix(string $locale): string
	{
		return ucfirst($locale) . '.' . $this->getFileExtension();
	}

	/**
	 * @inheritDoc
	 */
	public function save(string $resource, array $content, ?string $referenceResource = null): void
	{
		$this->saveResource->save($resource, $content, $this->getFileExtension(), $referenceResource);
	}

	/**
	 * @inheritDoc
	 */
	public function loadExport(string $resource): array
	{
		$this->getClass($resource);
		return $this->loadExport->process($resource);
	}

	/**
	 * @inheritDoc
	 */
	public function saveKeyValue($key, string $value, array &$content): void
	{
		$this->helpers->buildTree($key, $value, $content);
	}

	public function getFileExtension(): string
	{
		return 'php';
	}

	private function tokenizerResult(string $resource): TokenizeResult
	{
		if (!is_file($resource)) {
			throw new MissingResource("Unable to read file '$resource'.");
		}
		$result = $this->tokenizer->getStructureNameFromFile($resource, [T_CLASS]);
		if ($result === null) {
			throw new InvalidState("Unable to get translation class from '$resource'.");
		}
		return $result;
	}

	private function getClass(string $resource): string
	{
		$result = $this->tokenizerResult($resource);
		$class = $result->getFullyQualifiedName();
		if (!class_exists($class)) {
			throw new InvalidState("Translation class '$class' does not exist.");
		}
		if (!in_array(Translation::class, class_implements($class))) {
			throw new InvalidState(sprintf("Translation class '%s' must implement '%s'.", $class, Translation::class));
		}
		if ((new ReflectionClass($class))->isAbstract()) {
			throw new SkipResource();
		}
		return $class;
	}

}
