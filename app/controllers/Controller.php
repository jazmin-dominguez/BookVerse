<?php

    namespace app\controllers;

    require_once __DIR__ . '/../config.php';

    class Controller {
        protected $isAjax = false;

        public function __construct() {
            $this->isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        }

        protected function view($view, $data = []) {
            // Si es una petición AJAX, devolver error en JSON
            if ($this->isAjax) {
                ob_clean();
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Operación no válida'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            if (is_array($data)) {
                extract($data);
            }

            // Construir la ruta del archivo
            $viewPath = str_replace(['views.php', '.views', '.php'], ['view.php', '.view', ''], $view);
            $viewFile = VIEWS . $viewPath . '.view.php';

            // Verificar si el archivo existe
            if (!file_exists($viewFile)) {
                error_log("Vista no encontrada: {$viewFile}");
                if ($this->isAjax) {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Vista no encontrada'
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                $controller = new ErrorController();
                $controller->error404();
                return;
            }

            require_once $viewFile;
        }
    }