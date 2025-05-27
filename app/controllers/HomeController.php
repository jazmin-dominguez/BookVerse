<?php 

    namespace app\controllers;
    use app\classes\Views as View;
    use app\controllers\auth\SessionController as SC;
    class HomeController extends Controller {

        public function index($params = null){
            $response = [
                        'ua' => SC::sessionValidate() ?? [ 'sv' => 0 ],
                        'code'   => 200,
                        'title'  => 'BookVerse 2025'
                        ];
            View::render('home/home',$response);
        }

    }