<?php

namespace app\controllers;

class Controller {
    
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

        // Construir la ruta del archivo - compatible con ambos sistemas
        $viewPath = str_replace(['views.php', '.views', '.php'], ['view.php', '.view', ''], $view);
        
        // Intentar diferentes rutas posibles
        $possiblePaths = [
            __DIR__ . '/../resources/views/' . $viewPath . '.view.php',
            __DIR__ . '/../resources/views/' . $view . '.php',
            __DIR__ . '/../resources/views/' . $view . '.view.php'
        ];
        
        $viewFile = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $viewFile = $path;
                break;
            }
        }

        // Verificar si el archivo existe
        if (!$viewFile) {
            error_log("Vista no encontrada en ninguna de estas rutas: " . implode(', ', $possiblePaths));
            if ($this->isAjax) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Vista no encontrada'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            // Mostrar error 404
            http_response_code(404);
            echo "Error 404: Vista no encontrada";
            return;
        }

        require_once $viewFile;
    }
    
    protected $isAjax = false;

    public function __construct() {
        $this->isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                       strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
}