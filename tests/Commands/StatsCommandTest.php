<?php



namespace Skywalker\LogViewer\Tests\Commands;

use Skywalker\LogViewer\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Class     StatsCommandTest
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class StatsCommandTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    #[Test]

    public function it_can_display_stats(): void
    {
        $this->artisan('log-viewer:stats')
            ->assertExitCode(0);
    }
}
