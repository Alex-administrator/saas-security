<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\SimulationRepository;
use App\Requests\SimulationRequest;
use App\Services\SimulationService;
use App\Support\Request;
use App\Support\Session;

final class SimulationController extends BaseController
{
    public function index(Request $request)
    {
        return $this->view('simulations/index', [
            'simulations' => (new SimulationRepository())->listByOrganization($this->organizationId()),
            'pageTitle' => 'Симуляции',
        ]);
    }

    public function create(Request $request)
    {
        return $this->view('simulations/create', ['pageTitle' => 'Новая симуляция']);
    }

    public function store(Request $request)
    {
        $payload = (new SimulationRequest())->validate($request);
        (new SimulationService())->createProgram($this->organizationId(), $this->userId(), $payload);
        Session::flash('message', 'Сценарий симуляции сохранен как черновик.');
        return $this->redirect('/simulations');
    }

    public function launch(Request $request)
    {
        (new SimulationService())->launch((int) $request->route('id'), $this->organizationId(), $this->userId());
        Session::flash('message', 'Сценарий поставлен в очередь на запуск.');
        return $this->redirect('/simulations');
    }

    public function landing(Request $request)
    {
        $target = (new SimulationService())->findTarget((string) $request->route('token'));
        if ($target === null) {
            return $this->view('dashboard/error', ['message' => 'Ссылка симуляции не найдена.', 'status' => 404], 404);
        }

        return $this->view('simulations/landing', [
            'target' => $target,
            'layout' => 'layouts/public',
            'pageTitle' => 'Security awareness training',
        ]);
    }

    public function open(Request $request)
    {
        $token = (string) $request->route('token');
        if (!(new SimulationService())->openTarget($token)) {
            return $this->view('dashboard/error', ['message' => 'Ссылка симуляции не найдена.', 'status' => 404], 404);
        }

        Session::flash('message', 'Сценарий открыт. Выберите дальнейшее действие.');
        return $this->redirect('/simulate/' . $token);
    }

    public function report(Request $request)
    {
        (new SimulationService())->reportTarget((string) $request->route('token'));
        Session::flash('message', 'Спасибо. Сценарий отмечен как корректно распознанный.');
        return $this->redirect('/simulate/' . $request->route('token'));
    }

    public function complete(Request $request)
    {
        (new SimulationService())->completeTarget((string) $request->route('token'));
        Session::flash('message', 'Обучение завершено.');
        return $this->redirect('/simulate/' . $request->route('token'));
    }
}
