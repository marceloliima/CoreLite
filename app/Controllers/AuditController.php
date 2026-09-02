<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AuditLog;

final class AuditController extends Controller
{
    public function index(): string
    {
        $action = trim((string) ($_GET['action'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = (new AuditLog())->paginate($action, $page);

        return $this->view('audit/index', compact('result', 'action'));
    }
}
