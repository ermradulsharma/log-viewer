<?php



namespace Skywalker\LogViewer\Tests\Utilities;

use Skywalker\LogViewer\Tests\TestCase;
use Skywalker\LogViewer\Utilities\Filesystem;
use PHPUnit\Framework\Attributes\Test;


/**
 * Class     FilesystemTest
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class FilesystemTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Skywalker\LogViewer\Utilities\Filesystem */
    private Filesystem $filesystem;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = $this->filesystem();
    }

    protected function tearDown(): void
    {
        unset($this->filesystem);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    #[Test]

    public function it_can_be_instantiated(): void
    {
        static::assertInstanceOf(Filesystem::class, $this->filesystem);
    }

    #[Test]

    public function it_can_get_filesystem_instance(): void
    {
        static::assertInstanceOf(
            \Illuminate\Filesystem\Filesystem::class,
            $this->filesystem->getInstance()
        );
    }

    #[Test]

    public function it_can_get_all_valid_log_files(): void
    {
        static::assertCount(2, $this->filesystem->logs());
    }

    #[Test]

    public function it_can_get_all_custom_log_files(): void
    {
        $files = $this->filesystem
            ->setPrefixPattern('laravel-cli-')
            ->logs();

        static::assertCount(1, $files);
    }

    #[Test]

    public function it_can_get_all_log_files(): void
    {
        $files = $this->filesystem->all();

        static::assertCount(5, $files);

        foreach ($files as $file) {
            static::assertStringEndsWith('.log', $file);
        }
    }

    #[Test]

    public function it_can_read_file(): void
    {
        $file = $this->filesystem->read($date = '2015-01-01');

        static::assertNotEmpty($file);
        static::assertStringStartsWith('[' . $date, $file);
    }

    #[Test]

    public function it_can_delete_file(): void
    {
        static::createDummyLog(
            $date = date('Y-m-d'),
            $path = storage_path('logs')
        );

        $this->filesystem->setPath($path);

        // Assert log exists
        $file = $this->filesystem->read($date);

        static::assertNotEmpty($file);

        // Assert log deletion
        try {
            $deleted = $this->filesystem->delete($date);
            $message = '';
        } catch (\Exception $e) {
            $deleted = false;
            $message = $e->getMessage();
        }

        static::assertTrue($deleted, $message);
    }

    #[Test]

    public function it_can_get_files(): void
    {
        $files = $this->filesystem->logs();

        static::assertCount(2, $files);

        foreach ($files as $file) {
            static::assertFileExists($file);
        }
    }

    #[Test]

    public function it_can_set_a_custom_path(): void
    {
        $this->filesystem->setPath(static::fixturePath('custom-path-logs'));

        $files = $this->filesystem->logs();

        static::assertCount(1, $files);

        foreach ($files as $file) {
            static::assertFileExists($file);
        }
    }


    #[Test]

    public function it_can_get_file_path_by_date(): void
    {
        static::assertFileExists(
            $this->filesystem->path('2015-01-01')
        );
    }

    #[Test]

    public function it_can_get_dates_from_log_files(): void
    {
        static::assertDates(
            $this->filesystem->dates()
        );
    }

    #[Test]

    public function it_can_get_dates_with_paths_from_log_files(): void
    {
        foreach ($this->filesystem->dates(true) as $date => $path) {
            static::assertDate($date);
            static::assertFileExists($path);
        }
    }

    #[Test]

    public function it_must_throw_a_filesystem_exception_on_read(): void
    {
        $this->expectException(\Skywalker\LogViewer\Exceptions\FilesystemException::class);

        $this->filesystem->read('2222-11-11'); // Future FTW
    }

    #[Test]

    public function it_must_throw_a_filesystem_exception_on_delete(): void
    {
        $this->expectException(\Skywalker\LogViewer\Exceptions\FilesystemException::class);

        $this->filesystem->delete('2222-11-11'); // Future FTW
    }

    #[Test]

    public function it_can_set_and_get_pattern(): void
    {
        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setExtension('');

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPrefixPattern('laravel-cli-');

        static::assertSame(
            'laravel-cli-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setDatePattern('[0-9][0-9][0-9][0-9]');

        static::assertSame(
            'laravel-cli-[0-9][0-9][0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPattern();

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPattern(
            'laravel-',
            '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]',
            '.log'
        );

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );
    }
}
