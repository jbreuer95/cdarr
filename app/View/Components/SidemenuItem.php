<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Request;
use Illuminate\View\Component;

class SidemenuItem extends Component
{
    public $route;
    public $title;
    public $icon;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($route, $title, $icon)
    {
        $this->route = $route;
        $this->title = $title;
        $this->icon = $icon;
    }

    public function isActive()
    {
        if (Request::routeIs($this->route)) {
            return true;
        }

        $path = parse_url(route($this->route), PHP_URL_PATH);
        $path = ltrim($path, '/');

        if (Request::is("$path/*")) {
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
        return view('components.sidemenu-item');
    }
}
