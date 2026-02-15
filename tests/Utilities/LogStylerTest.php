<?php



namespace Skywalker\LogViewer\Tests\Utilities;

use Skywalker\LogViewer\Tests\TestCase;
use Skywalker\LogViewer\Utilities\LogStyler;
use Illuminate\Support\HtmlString;
use PHPUnit\Framework\Attributes\Test;


/**
 * Class     LogStylerTest
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class LogStylerTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Skywalker\LogViewer\Utilities\LogStyler */
    private LogStyler $styler;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->styler = $this->app->make(\Skywalker\LogViewer\Contracts\Utilities\LogStyler::class);
    }

    protected function tearDown(): void
    {
        unset($this->styler);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    #[Test]

    public function it_can_ben_instantiated()
    {
        static::assertInstanceOf(LogStyler::class, $this->styler);
    }

    #[Test]

    public function it_can_get_icon()
    {
        foreach (self::$logLevels as $level) {
            static::assertMatchesRegExp(
                '/^<i class="fa fa-fw fa-(.*)"><\/i>$/',
                $this->styler->icon($level)->toHtml()
            );
        }
    }

    #[Test]

    public function it_can_get_default_when_icon_not_found()
    {
        $icon = $this->styler->icon('danger', $default = 'fa fa-fw fa-danger');

        static::assertInstanceOf(HtmlString::class, $icon);
        static::assertSame('<i class="' . $default . '"></i>', $icon->toHtml());
    }

    #[Test]

    public function it_can_get_color()
    {
        foreach (self::$logLevels as $level) {
            static::assertHexColor($this->styler->color($level));
        }
    }

    #[Test]

    public function it_can_get_default_when_color_not_found()
    {
        $color = $this->styler->color('danger', $default = '#BADA55');

        static::assertHexColor($color);
        static::assertSame($default, $color);
    }

    #[Test]

    public function it_can_use_helper_to_get_icon()
    {
        foreach (self::$logLevels as $level) {
            static::assertMatchesRegExp(
                '/^<i class="fa fa-fw fa-(.*)"><\/i>$/',
                log_styler()->icon($level)->toHtml()
            );
        }
    }

    #[Test]

    public function it_can_use_helper_get_color()
    {
        foreach (self::$logLevels as $level) {
            static::assertHexColor(log_styler()->color($level));
        }
    }

    #[Test]

    public function it_can_get_string_to_highlight()
    {
        $expected = [
            '^#\d+',
            '^Stack trace:',
        ];

        static::assertSame($expected, $this->styler->toHighlight());
    }
}
