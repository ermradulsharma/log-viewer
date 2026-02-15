<?php



namespace Skywalker\LogViewer\Tests\Utilities;

use Skywalker\LogViewer\Utilities\LogLevels;
use Skywalker\LogViewer\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


/**
 * Class     LogLevelsTest
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class LogLevelsTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Skywalker\LogViewer\Utilities\LogLevels  */
    private LogLevels $levels;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    public function setUp(): void
    {
        parent::setUp();

        $this->levels = $this->app->make(\Skywalker\LogViewer\Contracts\Utilities\LogLevels::class);
    }

    public function tearDown(): void
    {
        unset($this->levels);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    #[Test]

    public function it_can_be_instantiated(): void
    {
        static::assertInstanceOf(LogLevels::class, $this->levels);
    }

    #[Test]

    public function it_can_get_all_levels(): void
    {
        static::assertLevels($this->levels->lists());
    }

    #[Test]

    public function it_can_get_all_levels_by_static_method(): void
    {
        static::assertLevels(LogLevels::all());
    }

    #[Test]

    public function it_can_get_all_translated_levels(): void
    {
        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);

            $levels = $this->levels->names($locale);

            static::assertTranslatedLevels($locale, $levels);
        }
    }

    #[Test]

    public function it_must_choose_the_log_viewer_locale_instead_of_app_locale(): void
    {
        static::assertNotEquals('auto', $this->levels->getLocale());
        static::assertSame($this->app->getLocale(), $this->levels->getLocale());

        $this->levels->setLocale('fr');

        static::assertSame('fr', $this->levels->getLocale());
        static::assertNotEquals($this->app->getLocale(), $this->levels->getLocale());
    }

    #[Test]

    public function it_can_translate_levels_automatically(): void
    {
        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);

            static::assertTranslatedLevels(
                $this->app->getLocale(),
                $this->levels->names()
            );

            static::assertTranslatedLevels(
                $locale,
                $this->levels->names($locale)
            );
        }
    }
}
