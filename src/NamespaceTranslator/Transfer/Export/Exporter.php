<?php declare(strict_types = 1);

namespace Wavevision\NamespaceTranslator\Transfer\Export;

use Nette\SmartObject;
use Wavevision\DIServiceAnnotation\DIService;
use Wavevision\NamespaceTranslator\Transfer\Export\Writters\InjectCsv;
use Wavevision\NamespaceTranslator\Transfer\Export\Writters\InjectGoogleSheet;
use Wavevision\NamespaceTranslator\Transfer\InjectTransferWalker;
use Wavevision\NamespaceTranslator\Transfer\Storages\Google\Config;

/**
 * @DIService(generateInject=true)
 */
class Exporter
{

	use InjectCsv;
	use InjectGoogleSheet;
	use InjectTransferWalker;
	use SmartObject;

	public function export(): void
	{
		$this->transferWalker->execute(
			function (string $directory, string $filename): void {
				$this->csv->write($directory, $filename);
			},
			function (Config $config, string $directory): void {
				$this->googleSheet->write($config, $directory);
			}
		);
	}

}
