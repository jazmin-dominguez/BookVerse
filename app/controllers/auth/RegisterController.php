<?php

    namespace app\controllers\auth;

    use app\controllers\Controller as Controller;
    use app\classes\Views as View;
    use app\models\user as user;
    use app\controllers\auth\SessionController as SC;

    class RegisterController extends Controller {
        public function __construct(){
            parent::__construct();
        }

        public function index(){
            $response = [
                'ua' => SC::sessionValidate() ?? [ 'sv' => 0 ],
                'title' => 'Registrarse',
                'code' => 200
            ];
            View::render('auth/register',$response);
        }

        public function register( $params = null ){
            $user = new user();
            $result = $user -> newUser(filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS));
            echo json_encode(["r" => $result]);
        }

    }