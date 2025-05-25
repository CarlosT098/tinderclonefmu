<?php

namespace App\Core;
require_once __DIR__ . '/../Controllers/HomeController.php';
use App\Controllers\HomeController;

class App
{
    public function run()
    {
        $controller = new HomeController();
        $controller->index();
    }
}
