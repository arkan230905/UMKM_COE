<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DashboardCard extends Component
{
    public $title;
    public $count;
    public $icon;

    public function __construct($title, $count, $icon = null)
    {
        $this->title = $title;
        $this->count = $count;
        $this->icon = $icon;
    }
    public function render()
    {
        return view('components.dashboard-card');
    }
}
