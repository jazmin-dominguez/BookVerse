<?php

    namespace app;

    use app\classes\Autoloader as Autoloader;
    use app\classes\Router as Router;

    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    class App {

        public function __construct () {
            $this->init();
        }

        private function init () {
            $this->initConfig();
            $this->loadFunctions();
            $this->initAutoloader();
            return $this->initRouter();
        }

        private function initConfig(){
            if(!file_exists(__DIR__ . "/config.php")){
                die("No se encontró el archivo de configuración config.php");
            }
            require_once __DIR__ . '/config.php';
            return;
        }

        private function loadFunctions(){
            if(!file_exists(FUNCTIONS . "main_functions.php")){
                die("No se encontró el archivo de funciones main_functions.php");
            }
            require_once FUNCTIONS . 'main_functions.php';
            return;
        }

        private function initAutoloader(){
            if(!file_exists(CLASSES . "Autoloader.php")){
                die("No se encontró la clase Autoloader.php");
            }
            require_once CLASSES . 'Autoloader.php';
            Autoloader::register();
            return;
        }

        private function initRouter(){
            $router = new Router();
            return $router->route();
        }

      
        public static function run(){
            try {
                $app = new self();
                return $app->initRouter();
            } catch (\Exception $e) {
                error_log('Error en App::run - ' . $e->getMessage());
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    ob_clean();
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Error interno del servidor'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                throw $e;
            }
        }
    }