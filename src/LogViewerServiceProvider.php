<?php



namespace Skywalker\LogViewer;

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
    protected $vendor = 'skywalker';

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
        parent::register();

        $this->registerConfig();

        $this->registerProvider(Providers\RouteServiceProvider::class);

        $this->registerCommands([
            \Skywalker\LogViewer\Commands\PublishCommand::class,
            \Skywalker\LogViewer\Commands\StatsCommand::class,
            \Skywalker\LogViewer\Commands\CheckCommand::class,
            \Skywalker\LogViewer\Commands\ClearCommand::class,
            \Skywalker\LogViewer\Commands\AlertCommand::class,
            \Skywalker\LogViewer\Commands\PruneCommand::class,
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
            $this->publishAll();
        }
    }
}
