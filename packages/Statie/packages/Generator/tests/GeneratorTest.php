<?php declare(strict_types=1);

namespace Symplify\Statie\Generator\Tests;

use DateTimeInterface;

/**
 * @covers \Symplify\Statie\Generator\Generator
 */
final class GeneratorTest extends AbstractGeneratorTest
{
    public function testIdsAreKeys(): void
    {
        $generatorFilesByType = $this->generator->run();

        foreach ($generatorFilesByType as $generatorFiles) {
            foreach ($generatorFiles as $key => $generatorFile) {
                $this->assertSame($key, $generatorFile->getId());
            }
        }
    }

    public function testPosts(): void
    {
        $generatorFilesByType = $this->generator->run();
        $postFiles = $generatorFilesByType['posts'];

        $this->assertCount(6, $postFiles);

        $this->fileSystemWriter->copyRenderableFiles($postFiles);

        // posts
        $this->assertFileExists($this->outputDirectory . '/blog/2016/10/10/title/index.html');
        $this->assertFileExists($this->outputDirectory . '/blog/2016/01/02/second-title/index.html');

        $this->assertFileExists($this->outputDirectory . '/blog/2017/01/01/some-post/index.html');
        $this->assertFileExists($this->outputDirectory . '/blog/2017/01/05/another-related-post/index.html');
        $this->assertFileExists($this->outputDirectory . '/blog/2017/01/05/some-related-post/index.html');

        $this->assertFileExists($this->outputDirectory . '/blog/2017/02/05/offtopic-post/index.html');
    }

    public function testLatteBlocks(): void
    {
        $generatorFilesByType = $this->generator->run();
        $postFiles = $generatorFilesByType['posts'];

        $this->fileSystemWriter->copyRenderableFiles($postFiles);

        $this->assertFileEquals(
            __DIR__ . '/GeneratorSource/expected/post-with-latte-blocks-expected.html',
            $this->outputDirectory . '/blog/2016/01/02/second-title/index.html'
        );
    }

    public function testLectures(): void
    {
        $generatorFilesByType = $this->generator->run();
        $lectureFiles = $generatorFilesByType['lectures'];

        $this->assertCount(1, $lectureFiles);

        $this->fileSystemWriter->copyRenderableFiles($lectureFiles);

        // lectures
        $this->assertFileExists($this->outputDirectory . '/lecture/open-source-lecture/index.html');
    }

    public function testConfiguration(): void
    {
        $this->assertArrayNotHasKey('posts', $this->configuration->getOptions());
        $this->assertArrayNotHasKey('lectures', $this->configuration->getOptions());

        $this->generator->run();

        $this->assertArrayHasKey('posts', $this->configuration->getOptions());
        $this->assertArrayHasKey('lectures', $this->configuration->getOptions());

        $posts = $this->configuration->getOption('posts');
        $this->assertCount(6, $posts);

        $lectures = $this->configuration->getOption('lectures');
        $this->assertCount(1, $lectures);

        // detect date correctly from name
        $firstPost = array_pop($posts);
        $this->assertInstanceOf(DateTimeInterface::class, $firstPost['date']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/GeneratorSource/statie.yml';
    }
}
