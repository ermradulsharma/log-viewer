<?php



namespace Ermradulsharma\LogViewer;

use Skywalker\Support\Providers\PackageServiceProvider;

/**
 * Class     LogViewerServiceProvider
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class LogViewerServiceProvider extends PackageServiceProvider
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /**
     * Vendor name.
     *
     * @var string
     */
    protected $vendor = 'ermradulsharma';

    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'log-viewer';

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerConfig();

        $this->app->booted(function () {
            $this->registerProvider(Providers\RouteServiceProvider::class);
        });

        $this->registerCommands([
            \Ermradulsharma\LogViewer\Commands\PublishCommand::class,
            \Ermradulsharma\LogViewer\Commands\StatsCommand::class,
            \Ermradulsharma\LogViewer\Commands\CheckCommand::class,
            \Ermradulsharma\LogViewer\Commands\ClearCommand::class,
            \Ermradulsharma\LogViewer\Commands\AlertCommand::class,
            \Ermradulsharma\LogViewer\Commands\PruneCommand::class,
        ]);
    }

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        parent::boot();

        $this->loadTranslations();
        $this->loadViews();

        if ($this->app->runningInConsole()) {
            $this->publishConfig();
            $this->publishTranslations();
            $this->publishViews();
        }
    }
}
