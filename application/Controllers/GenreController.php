<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Genre;

use function App\route;

class GenreController extends Controller
{
    private mixed $auth_user;
    private Genre $genre;

    public function __construct()
    {
        parent::__construct();
        $this->auth_user = UserController::getAuthUser();
        $this->genre = new Genre();
    }

    public function view(mixed $args): void
    {
        // проверка прав администратора
        $authUser = UserController::isAuthAdmin();
        if (!$authUser) {
            $mainControl = new MainController();
            $mainControl->error('Доступ запрещен');
        }

        // данные
        $data['header_button_url'] = route('logout');
        $data['header_button_name'] = 'Выйти';
        $data['auth_user_name'] = $this->auth_user['user_name'];
        $data['auth_user_page'] = route('show');

        $csrf = Controller::createCSRFToken();
        $data['csrf'] = $csrf;
        $data['genres'] = $this->genre->get();

        // роуты
        $routes = [
            'show' => route('show'),
        ];

        // доп.заголовки
        $csrf_meta = "<meta name='csrf' content=$csrf>";

        $this->view->generate(
            page_name: "{$this->site_name} - жанры",
            template_view: 'template_view.php',
            content_view: 'admin/genre_view.php',
            content_css: ['context_menu.css', 'table.css', 'form-add.css'],
            content_js: [
                'Classes/ServerRequest.js',
                'Classes/ContextMenu.js',
                'ClientControllers/ClientController.js',
                'ClientControllers/GenreClientController.js',
                'genre.js',
            ],
            data: $data,
            routes: $routes,
            add_head: $csrf_meta,
        );
    }

    public function store($args)
    {
        $isExisted = $this->genre->exists($args['name']);
        if ($isExisted) {
            $response['is_added'] = 0;
            $response['description'] = 'Указанный автор существует';
        } else {
            $id = $this->genre->add($args['name']);
            $response['is_added'] = $id;
        }
        echo json_encode($response);
    }

    public function destroy($args)
    {
        $isRemoved = $this->genre->remove($args['genre_name']);
        $response['is_removed'] = $isRemoved;

        echo json_encode($response);
    }
}
