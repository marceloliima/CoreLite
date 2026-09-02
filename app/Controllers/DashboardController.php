<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

final class DashboardController extends Controller
{
    public function index(): string
    {
        $stats = (new User())->stats();
        return $this->view('dashboard/index', ['stats' => $stats]);
    }
}
