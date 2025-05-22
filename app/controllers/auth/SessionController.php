<?php

    namespace app\controllers\auth;

    use app\controllers\Controller as Controller;
    use app\classes\Views as View;
    use app\classes\Redirect as Redirect;
    use app\models\user as user;

    class SessionController extends Controller {
        public function __construct(){
            parent::__construct();
        }

        public function iniSession( $params = null){
            $response = [
                'ua'    => ['sv' => 0],
                'title' => 'Iniciar sesión',
                'code'  => 200
            ];
            View::render('auth/inisession',$response);
        }

        public function userAuth(){
            $datos = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);

            $user = new user();

            $result = $user -> where([["username",$datos["username"]],
                                      ["passwd",sha1($datos["passwd"])]])
                            ->get();
            if(count(json_decode($result)) > 0) {
                $user_data = json_decode($result)[0];
                
                // Verificar si es el admin
                if($datos["username"] === "admin" && $datos["passwd"] === sha1("bookverse321")) {
                    echo $this->sessionRegister($result);
                    Redirect::to('/admin');
                } else {
                    echo $this->sessionRegister($result);
                    Redirect::to('/');
                }
            } else {
                self::sessionDestroy();
                echo json_encode(["r" => false]);
            }
        }

        private function sessionRegister( $r ){
            $datos = json_decode( $r );
            session_start();
            $_SESSION['sv']       = true;
            $_SESSION['IP']       = $_SERVER['REMOTE_ADDR'];
            $_SESSION['id']       = $datos[0]->id;
            $_SESSION['username'] = $datos[0]->username;
            $_SESSION['passwd']   = $datos[0]->passwd;
            $_SESSION['tipo']     = $datos[0]->tipo;
            session_write_close();
            return json_encode( ["r" => true ]);
        }

        public static function sessionValidate(){
            $user = new user();
            session_start();
            if( isset( $_SESSION['sv']) && $_SESSION['sv'] == true){
                $datos = $_SESSION;
                $result = $user -> where([["username",$datos["username"]],
                                          ["passwd",$datos["passwd"]]])
                                ->get();
                if( count( json_decode( $result )) > 0 && $datos['IP'] == $_SERVER['REMOTE_ADDR']){
                    session_write_close();
                    return ['username' => $datos['username'],
                            'sv'   => $datos['sv'],
                            'id'   => $datos['id'],
                            'tipo' => $datos['tipo']];
                }else{
                    session_write_close();
                    self::sessionDestroy();
                    return null;
                }
            }
            session_write_close();
            self::sessionDestroy();
            return null;
        }

        public static function sessionDestroy(){
            session_start();
            $_SESSION = ['sv' => false];
            session_destroy();
            session_write_close();
            return;
        }

        public function logout(){
            self::sessionDestroy();
            Redirect::to('/');
            exit();
        }

    }