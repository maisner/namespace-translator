<?php declare (strict_types = 1);

namespace Wavevision\NamespaceTranslatorTests\App\Models\Translated\Translations;

class Cs extends Translation
{

	/**
	 * @inheritDoc
	 */
	public static function define(): array
	{
		return [
			self::SOME_KEY => 'My chceme modele!',
			self::SUB => [
				self::NESTED => 'Zanořené',
			],
		];
	}

}
