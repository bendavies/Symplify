<?php declare(strict_types=1);

namespace Symplify\MonorepoBuilder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\MonorepoBuilder\Contract\ComposerJsonDecoratorInterface;
use Symplify\MonorepoBuilder\FileSystem\JsonFileManager;
use Symplify\MonorepoBuilder\Package\PackageComposerJsonMerger;
use Symplify\MonorepoBuilder\PackageComposerFinder;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class MergeCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ComposerJsonDecoratorInterface[]
     */
    private $composerJsonDecorators = [];

    /**
     * @var PackageComposerJsonMerger
     */
    private $packageComposerJsonMerger;

    /**
     * @var string[]
     */
    private $mergeSections = [];

    /**
     * @var PackageComposerFinder
     */
    private $packageComposerFinder;

    /**
     * @var JsonFileManager
     */
    private $jsonFileManager;

    /**
     * @param string[] $mergeSections
     */
    public function __construct(
        array $mergeSections,
        SymfonyStyle $symfonyStyle,
        PackageComposerJsonMerger $packageComposerJsonMerger,
        PackageComposerFinder $packageComposerFinder,
        JsonFileManager $jsonFileManager
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->packageComposerJsonMerger = $packageComposerJsonMerger;
        $this->mergeSections = $mergeSections;
        $this->packageComposerFinder = $packageComposerFinder;
        $this->jsonFileManager = $jsonFileManager;

        parent::__construct();
    }

    public function addComposerJsonDecorator(ComposerJsonDecoratorInterface $composerJsonDecorator): void
    {
        $this->composerJsonDecorators[] = $composerJsonDecorator;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Merge "composer.json" from all found packages to root one');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerPackageFiles = $this->packageComposerFinder->getPackageComposerFiles();
        if (! count($composerPackageFiles)) {
            $this->symfonyStyle->error('No "composer.json" were found in packages.');
            return 1;
        }

        $rootComposerJson = $this->jsonFileManager->loadFromFilePath(getcwd() . DIRECTORY_SEPARATOR . 'composer.json');
        $merged = $this->packageComposerJsonMerger->mergeFileInfos($composerPackageFiles, $this->mergeSections);

        foreach ($this->mergeSections as $sectionToMerge) {
            // nothing collected to merge
            if (! isset($merged[$sectionToMerge]) || empty($merged[$sectionToMerge])) {
                continue;
            }

            // section in root composer.json is empty, just set and go
            if (! isset($rootComposerJson[$sectionToMerge])) {
                $rootComposerJson[$sectionToMerge] = $merged[$sectionToMerge];
                break;
            }

            $rootComposerJson[$sectionToMerge] = $merged[$sectionToMerge];
        }

        foreach ($this->composerJsonDecorators as $composerJsonDecorator) {
            $rootComposerJson = $composerJsonDecorator->decorate($rootComposerJson);
        }

        $this->jsonFileManager->saveJsonWithFilePath(
            $rootComposerJson,
            getcwd() . DIRECTORY_SEPARATOR . 'composer.json'
        );

        $this->symfonyStyle->success('Main "composer.json" was updated.');

        // success
        return 0;
    }
}