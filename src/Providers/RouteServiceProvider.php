<?php



namespace Ermradulsharma\LogViewer\Providers;

use Ermradulsharma\LogViewer\Http\Routes\LogViewerRoute;
use Skywalker\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Class     RouteServiceProvider
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 */
class RouteServiceProvider extends ServiceProvider
{
    /* -----------------------------------------------------------------
     |  Getters & Setters
     | -----------------------------------------------------------------
     */

    /**
     * Check if routes is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) $this->config('enabled', false);
    }

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    /**
     * Boot the service provider.
     */
    public function boot(): void
    {
        if ($this->isEnabled()) {
            $this->routes(function () {
                static::mapRouteClasses([LogViewerRoute::class]);
            });
        }
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get config value by key
     *
     * @param  string      $key
     * @param  mixed|null  $default
     *
     * @return mixed
     */
    private function config($key, $default = null)
    {
        return $this->app['config']->get("log-viewer.route.$key", $default);
    }
}
