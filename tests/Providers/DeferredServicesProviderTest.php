<?php



namespace Skywalker\LogViewer\Tests\Providers;

use Skywalker\LogViewer\Contracts;
use Skywalker\LogViewer\Providers\DeferredServicesProvider;
use Skywalker\LogViewer\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;


/**
 * Class     DeferredServicesProviderTest
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class DeferredServicesProviderTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Skywalker\LogViewer\Providers\DeferredServicesProvider */
    private DeferredServicesProvider $provider;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = $this->app->getProvider(DeferredServicesProvider::class);
    }

    protected function tearDown(): void
    {
        unset($this->provider);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    #[Test]
    public function it_can_be_instantiated(): void
    {
        $expectations = [
            \Illuminate\Support\ServiceProvider::class,
            \Illuminate\Contracts\Support\DeferrableProvider::class,
            \Skywalker\Support\Providers\ServiceProvider::class,
            DeferredServicesProvider::class,
        ];

        foreach ($expectations as $expected) {
            static::assertInstanceOf($expected, $this->provider);
        }
    }

    #[Test]

    public function it_can_provides(): void
    {
        $expected = [
            Contracts\LogViewer::class,
            Contracts\Utilities\LogLevels::class,
            Contracts\Utilities\LogStyler::class,
            Contracts\Utilities\LogMenu::class,
            Contracts\Utilities\Filesystem::class,
            Contracts\Utilities\Factory::class,
            Contracts\Utilities\LogChecker::class,
        ];

        static::assertSame($expected, $this->provider->provides());
    }
}
