<?php

namespace VTalbot\Markdown\Facades;

use Illuminate\Support\Facades\Facade;

class Markdown extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'markdown'; }
    
}