<?php

    namespace app\controllers;

    use app\models\posts as posts;
    use app\models\comments as comments;
    use app\models\interactions as inter;
    use app\controllers\auth\SessionController as SC;
    use app\classes\Views as View;
    use app\classes\Csrf as Csrf;

    class PostsController extends Controller {
        public function __construct(){
            parent::__construct();
        }

        public function index(){
            $ua = SC::sessionValidate();
            if (is_null($ua)){
                View::render('home', ['ua'=>['sv'=>0], 'title'=>'Foro FIe']);
                exit();
            }
            View::render('myposts', ['ua'=>$ua, 'title'=>'Mis Publicaciones']);
        }

        public function newpost(){
            $csrf = new Csrf();
            $ua = SC::sessionValidate() ?? [ 'sv' => 0 ];
            View::render('newpost', ['ua'=>$ua, 'csrf'=>$csrf->get_token(), 'title'=>'Nueva Publicacion']);
        }
    }