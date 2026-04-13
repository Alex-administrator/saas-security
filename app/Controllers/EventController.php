<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\EventRepository;
use App\Requests\EventRequest;
use App\Services\EventService;
use App\Support\Request;
use App\Support\Session;

final class EventController extends BaseController
{
    public function index(Request $request)
    {
        return $this->view('events/index', [
            'events' => (new EventRepository())->listByOrganization($this->organizationId()),
            'pageTitle' => 'События',
        ]);
    }

    public function create(Request $request)
    {
        return $this->view('events/create', ['pageTitle' => 'Новое событие']);
    }

    public function store(Request $request)
    {
        $payload = (new EventRequest())->validate($request);
        (new EventService())->create($this->organizationId(), $this->userId(), $payload);
        Session::flash('message', 'Событие добавлено в календарь.');
        return $this->redirect('/events');
    }
}

