<?php



namespace Ermradulsharma\LogViewer\Tests\Commands;

use Ermradulsharma\LogViewer\Tests\TestCase;

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

    /** @test */
    public function it_can_display_stats(): void
    {
        $this->artisan('log-viewer:stats')
            ->assertExitCode(0);
    }
}
