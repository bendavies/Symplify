<?php declare(strict_types=1);

namespace Symplify\Statie\Renderable;

use Nette\Utils\Strings;
use Symplify\Statie\Configuration\Configuration;
use Symplify\Statie\Contract\Renderable\FileDecoratorInterface;
use Symplify\Statie\Generator\Configuration\GeneratorElement;
use Symplify\Statie\Renderable\File\AbstractFile;
use Symplify\Statie\Utils\PathNormalizer;

final class RouteFileDecorator implements FileDecoratorInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var PathNormalizer
     */
    private $pathNormalizer;

    public function __construct(Configuration $configuration, PathNormalizer $pathNormalizer)
    {
        $this->configuration = $configuration;
        $this->pathNormalizer = $pathNormalizer;
    }

    /**
     * @param AbstractFile[] $files
     * @return AbstractFile[]
     */
    public function decorateFiles(array $files): array
    {
        foreach ($files as $file) {
            $this->decorateFile($file);
        }

        return $files;
    }

    /**
     * @param AbstractFile[] $files
     * @return AbstractFile[]
     */
    public function decorateFilesWithGeneratorElement(array $files, GeneratorElement $generatorElement): array
    {
        foreach ($files as $file) {
            $outputPath = $generatorElement->getRoutePrefix()
                ? $generatorElement->getRoutePrefix() . DIRECTORY_SEPARATOR
                : '';
            $outputPath = $this->prefixWithDateIfFound($file, $outputPath);
            $outputPath .= $file->getFilenameWithoutDate();
            $outputPath = $this->pathNormalizer->normalize($outputPath);

            $file->setRelativeUrl($outputPath);
            $file->setOutputPath($outputPath . DIRECTORY_SEPARATOR . 'index.html');
        }

        return $files;
    }

    public function getPriority(): int
    {
        return 900;
    }

    private function decorateFile(AbstractFile $file): void
    {
        // manual config override has preference
        if ($file->getOption('outputPath')) {
            $file->setOutputPath((string) $file->getOption('outputPath'));
            $file->setRelativeUrl((string) $file->getOption('outputPath'));
            return;
        }

        if ($this->isRootIndex($file)) {
            $file->setOutputPath('index.html');
            $file->setRelativeUrl('/');
            return;
        }

        // special files
        if (in_array($file->getPrimaryExtension(), ['xml', 'rss', 'json', 'atom', 'css', 'js'], true)) {
            $outputPath = $file->getBaseName();
            // trim file.xml.latte => file.xml
            if ($file->getExtension() !== 'latte') {
                $outputPath .= '.' . $file->getPrimaryExtension();
            }

            $file->setOutputPath($outputPath);
            $file->setRelativeUrl($outputPath);
            return;
        }

        // fallback
        $relativeDirectory = $this->getRelativeDirectory($file);
        $relativeOutputDirectory = $relativeDirectory . DIRECTORY_SEPARATOR . $file->getBaseName();

        if ($file->getBaseName() === 'index') { // index.* file
            $outputPath = $relativeOutputDirectory . '.html';
        } else {
            $outputPath = $relativeOutputDirectory . DIRECTORY_SEPARATOR . 'index.html';
        }

        $file->setOutputPath($outputPath);
        $file->setRelativeUrl($relativeDirectory . DIRECTORY_SEPARATOR . $file->getBaseName());
    }

    /**
     * Only if the date is part of file name
     */
    private function prefixWithDateIfFound(AbstractFile $file, string $outputPath): string
    {
        if ($file->getDate() === null) {
            return $outputPath;
        }

        return str_replace(
            [':year', ':month', ':day'],
            [$file->getDateInFormat('Y'), $file->getDateInFormat('m'), $file->getDateInFormat('d')],
            $outputPath
        );
    }

    private function isRootIndex(AbstractFile $file): bool
    {
        return Strings::contains(
            $file->getFilePath(),
            $this->configuration->getSourceDirectory() . DIRECTORY_SEPARATOR . 'index'
        );
    }

    private function getRelativeDirectory(AbstractFile $file): string
    {
        $sourceParts = explode(DIRECTORY_SEPARATOR, $this->configuration->getSourceDirectory());
        $sourceDirectory = array_pop($sourceParts);

        $relativeParts = explode($sourceDirectory, $file->getRelativeDirectory());

        return array_pop($relativeParts);
    }
}
