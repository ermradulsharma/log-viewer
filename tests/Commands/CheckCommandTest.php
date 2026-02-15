<?php



namespace Skywalker\LogViewer\Tests\Commands;

use Skywalker\LogViewer\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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

    #[Test]

    public function it_can_check(): void
    {
        $this->artisan('log-viewer:check')
            ->assertExitCode(0);
    }
}
