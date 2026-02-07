<?php



namespace Ermradulsharma\LogViewer\Tests\Commands;

use Ermradulsharma\LogViewer\Tests\TestCase;

/**
 * Class     CheckCommandTest
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class CheckCommandTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_check(): void
    {
        $this->artisan('log-viewer:check')
            ->assertExitCode(0);
    }
}
