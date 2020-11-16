<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Request;
use Illuminate\View\Component;

class SidemenuSubitem extends Component
{
    public $route;
    public $title;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($route, $title)
    {
        $this->route = $route;
        $this->title = $title;
    }

    public function isActive()
    {
        if (Request::routeIs($this->route)) {
            return true;
        }

        return false;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.sidemenu-subitem');
    }
}
