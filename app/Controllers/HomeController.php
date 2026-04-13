<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ArticleRepository;
use App\Repositories\EventRepository;
use App\Services\DashboardService;
use App\Support\Auth;
use App\Support\Request;

final class HomeController extends BaseController
{
    public function index(Request $request)
    {
        if (Auth::check()) {
            return $this->redirect('/dashboard');
        }

        return $this->view('dashboard/home', [
            'articles' => (new ArticleRepository())->listPublic(),
            'events' => (new EventRepository())->upcomingPublic(),
            'pageTitle' => 'Security Awareness Platform',
        ]);
    }

    public function dashboard(Request $request)
    {
        return $this->view('dashboard/index', [
            'overview' => (new DashboardService())->overview($this->organizationId()),
            'subscription' => Auth::subscription(),
            'pageTitle' => 'Dashboard',
        ]);
    }
}

