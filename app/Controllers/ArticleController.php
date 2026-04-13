<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ArticleRepository;
use App\Requests\ArticleRequest;
use App\Services\ArticleService;
use App\Support\Request;
use App\Support\Session;

final class ArticleController extends BaseController
{
    public function index(Request $request)
    {
        return $this->view('articles/index', [
            'articles' => (new ArticleRepository())->listByOrganization($this->organizationId()),
            'pageTitle' => 'Статьи',
        ]);
    }

    public function create(Request $request)
    {
        return $this->view('articles/create', ['pageTitle' => 'Новая статья']);
    }

    public function store(Request $request)
    {
        $payload = (new ArticleRequest())->validate($request);
        (new ArticleService())->create($this->organizationId(), $this->userId(), $payload);
        Session::flash('message', 'Черновик статьи создан.');
        return $this->redirect('/articles');
    }

    public function publish(Request $request)
    {
        (new ArticleService())->publish((int) $request->route('id'), $this->organizationId(), $this->userId());
        Session::flash('message', 'Статья опубликована.');
        return $this->redirect('/articles');
    }

    public function show(Request $request)
    {
        $article = (new ArticleRepository())->findPublicBySlug((string) $request->route('slug'));
        if ($article === null) {
            return $this->view('dashboard/error', ['message' => 'Статья не найдена.', 'status' => 404], 404);
        }

        return $this->view('articles/show', [
            'article' => $article,
            'pageTitle' => $article['title'],
        ]);
    }
}

