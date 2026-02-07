<?php



namespace Ermradulsharma\LogViewer\Providers;

use Ermradulsharma\LogViewer\Contracts\LogViewer as LogViewerContract;
use Ermradulsharma\LogViewer\Contracts\Utilities\Factory as FactoryContract;
use Ermradulsharma\LogViewer\Contracts\Utilities\Filesystem as FilesystemContract;
use Ermradulsharma\LogViewer\Contracts\Utilities\LogChecker as LogCheckerContract;
use Ermradulsharma\LogViewer\Contracts\Utilities\LogLevels as LogLevelsContract;
use Ermradulsharma\LogViewer\Contracts\Utilities\LogMenu as LogMenuContract;
use Ermradulsharma\LogViewer\Contracts\Utilities\LogStyler as LogStylerContract;
use Ermradulsharma\LogViewer\LogViewer;
use Ermradulsharma\LogViewer\Utilities;
use Skywalker\Support\Providers\ServiceProvider;

if (interface_exists('Illuminate\Contracts\Support\DeferrableProvider')) {
    class_alias('Illuminate\Contracts\Support\DeferrableProvider', 'Ermradulsharma\LogViewer\Providers\DeferrableProvider');
} else {
    interface DeferrableProvider {}
}

/**
 * Class     DeferredServicesProvider
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class DeferredServicesProvider extends ServiceProvider implements DeferrableProvider
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->registerLogViewer();
        $this->registerLogLevels();
        $this->registerStyler();
        $this->registerLogMenu();
        $this->registerFilesystem();
        $this->registerFactory();
        $this->registerChecker();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            LogViewerContract::class,
            LogLevelsContract::class,
            LogStylerContract::class,
            LogMenuContract::class,
            FilesystemContract::class,
            FactoryContract::class,
            LogCheckerContract::class,
        ];
    }

    /* -----------------------------------------------------------------
     |  LogViewer Utilities
     | -----------------------------------------------------------------
     */

    /**
     * Register the log viewer service.
     */
    private function registerLogViewer()
    {
        $this->singleton(LogViewerContract::class, LogViewer::class);
    }

    /**
     * Register the log levels.
     */
    private function registerLogLevels()
    {
        $this->singleton(LogLevelsContract::class, function ($app) {
            return new Utilities\LogLevels(
                $app['translator'],
                $app['config']->get('log-viewer.locale')
            );
        });
    }

    /**
     * Register the log styler.
     */
    private function registerStyler()
    {
        $this->singleton(LogStylerContract::class, Utilities\LogStyler::class);
    }

    /**
     * Register the log menu builder.
     */
    private function registerLogMenu()
    {
        $this->singleton(LogMenuContract::class, Utilities\LogMenu::class);
    }

    /**
     * Register the log filesystem.
     */
    private function registerFilesystem()
    {
        $this->singleton(FilesystemContract::class, function ($app) {
            /** @var  \Illuminate\Config\Repository  $config */
            $config     = $app['config'];
            $filesystem = new Utilities\Filesystem($app['files'], $config->get('log-viewer.storage-path'));

            return $filesystem->setPattern(
                $config->get('log-viewer.pattern.prefix', FilesystemContract::PATTERN_PREFIX),
                $config->get('log-viewer.pattern.date', FilesystemContract::PATTERN_DATE),
                $config->get('log-viewer.pattern.extension', FilesystemContract::PATTERN_EXTENSION)
            );
        });
    }

    /**
     * Register the log factory class.
     */
    private function registerFactory()
    {
        $this->singleton(FactoryContract::class, Utilities\Factory::class);
    }

    /**
     * Register the log checker service.
     */
    private function registerChecker()
    {
        $this->singleton(LogCheckerContract::class, Utilities\LogChecker::class);
    }
}
