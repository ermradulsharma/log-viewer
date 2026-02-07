<?php

namespace Ermradulsharma\LogViewer\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class     LogViewer
 *
 * @author   Mradul Sharma <skywalkerlknw@gmail.com>
 *
 * @see \Ermradulsharma\LogViewer\LogViewer
 */
class LogViewer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Ermradulsharma\LogViewer\Contracts\LogViewer::class;
    }
}
