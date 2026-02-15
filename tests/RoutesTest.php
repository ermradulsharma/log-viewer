<?php



namespace Skywalker\LogViewer\Tests;

use PHPUnit\Framework\Attributes\Test;


/**
 * Class     RoutesTest
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class RoutesTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    #[Test]

    public function it_can_see_dashboard_page(): void
    {
        $this->get(route('log-viewer::dashboard'))
            ->assertSuccessful()
            ->assertSee('Enterprise Dashboard')
            ->assertSee('Log Level Distribution');
    }

    #[Test]

    public function it_can_see_logs_page(): void
    {
        $this->get(route('log-viewer::logs.list'))
            ->assertSuccessful()
            ->assertSee('Log Files');
    }

    #[Test]

    public function it_can_show_a_log_page(): void
    {
        $date = '2015-01-01';
        $this->createDummyLog($date, $this->config()->get('log-viewer.storage-path'));

        $this->get(route('log-viewer::logs.show', [$date]))
            ->assertSuccessful()
            ->assertSee($date)
            ->assertSee('Log Viewer');
    }

    #[Test]

    public function it_can_see_a_filtered_log_entries_page(): void
    {
        $date = '2015-01-01';
        $this->createDummyLog($date, $this->config()->get('log-viewer.storage-path'));

        $this->get(route('log-viewer::logs.filter', [$date, 'error']))
            ->assertSuccessful()
            ->assertSee($date)
            ->assertSee('Log Viewer');
    }

    #[Test]

    public function it_can_search_logs_page(): void
    {
        $date = '2015-01-01';
        $this->createDummyLog($date, $this->config()->get('log-viewer.storage-path'));

        $this->get(route('log-viewer::logs.search', [$date, 'all', 'query' => 'This is an error log.']))
            ->assertSuccessful()
            ->assertSee($date)
            ->assertSee('Log Viewer');
    }

    #[Test]

    public function it_can_download_a_log_file(): void
    {
        $date = '2023-01-01';
        $this->createDummyLog($date, $this->config()->get('log-viewer.storage-path'));

        $this->get(route('log-viewer::logs.download', [$date]))
            ->assertSuccessful();
    }

    #[Test]

    public function it_can_delete_a_log_file(): void
    {
        $date = date('Y-m-d');
        $this->createDummyLog($date, $this->config()->get('log-viewer.storage-path'));

        $this->delete(route('log-viewer::logs.delete'), ['date' => $date], ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->assertSuccessful()
            ->assertJson(['result' => 'success']);
    }

    #[Test]

    public function it_can_store_a_note(): void
    {
        $this->post(route('log-viewer::notes.store'), [
            'hash' => 'test-hash',
            'note' => 'This is a test note'
        ], ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertFileExists(storage_path('logs/log-viewer-notes.json'));
    }

    #[Test]

    public function it_can_save_a_search(): void
    {
        $this->post(route('log-viewer::searches.save'), [
            'label' => 'Test Search',
            'query' => 'level:error'
        ])
            ->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertFileExists(storage_path('logs/log-viewer-searches.json'));
    }

    #[Test]

    public function it_can_save_notification_settings(): void
    {
        $this->post(route('log-viewer::notifications.save'), [
            'slack_webhook'   => 'https://hooks.slack.com/services/test',
            'discord_webhook' => 'https://discord.com/api/webhooks/test',
            'email_alerts'    => 'admin@example.com',
            'alert_level'     => 'critical'
        ])
            ->assertSuccessful()
            ->assertJson(['success' => true]);

        $this->assertFileExists(storage_path('logs/log-viewer-notifications.json'));
    }

    #[Test]

    public function it_can_cleanup_logs(): void
    {
        $this->post(route('log-viewer::cleanup-logs'), [], ['HTTP_X-Requested-With' => 'XMLHttpRequest'])
            ->assertSuccessful()
            ->assertJson(['success' => true]);
    }

    #[Test]

    public function it_can_explain_error(): void
    {
        $this->get(route('log-viewer::ai-explain', ['message' => 'Class "Missing" not found']))
            ->assertSuccessful()
            ->assertJson(['success' => true]);
    }

    #[Test]

    public function it_can_push_to_tracker(): void
    {
        $this->post(route('log-viewer::push-to-tracker'), [
            'header'  => 'Test Error',
            'type'    => 'jira',
            'summary' => 'This is a test summary'
        ])
            ->assertSuccessful()
            ->assertJson(['success' => true]);
    }

    #[Test]

    public function it_must_throw_log_not_found_exception_on_show(): void
    {
        $response = $this->get(route('log-viewer::logs.show', ['0000-00-00']));
        $response->assertStatus(404);
    }
}
