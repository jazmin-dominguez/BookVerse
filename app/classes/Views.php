<?php

    namespace app\classes;

    class Views {

        public static function render($view, $data = [], $useLayout = true){
            // Convertir datos a objeto
            $d = as_object($data);
            
            // Normalizar la ruta de la vista
            $viewPath = str_replace('views.php', 'view.php', $view);
            $viewPath = str_replace('.views', '.view', $viewPath);
            $viewPath = rtrim($viewPath, '.php');
            $viewFile = VIEWS . $viewPath . '.view.php';

            // Verificar si el archivo existe
            if (!file_exists($viewFile)) {
                throw new \Exception("Vista no encontrada: {$viewFile}");
            }

            if ($useLayout) {
                // Iniciar buffer de salida para capturar el contenido de la vista
                ob_start();
                require_once $viewFile;
                $content = ob_get_clean();

                // Determinar qué layout usar basado en el tipo de usuario
                $layout = 'main';
                if (isset($_SESSION['user_role'])) {
                    $layout = $_SESSION['user_role'] === 'admin' ? 'admin' : 'user';
                }

                // Cargar el layout con el contenido
                require_once LAYOUTS . $layout . '.view.php';
            } else {
                // Cargar la vista directamente sin layout
                require_once $viewFile;
            }
            exit();
        }

        public static function renderPartial($view, $data = []) {
            $d = as_object($data);
            
            // Normalizar la ruta de la vista
            $viewPath = str_replace('views.php', 'view.php', $view);
            $viewPath = str_replace('.views', '.view', $viewPath);
            $viewPath = rtrim($viewPath, '.php');
            $viewFile = VIEWS . $viewPath . '.view.php';

            // Verificar si el archivo existe
            if (!file_exists($viewFile)) {
                throw new \Exception("Vista no encontrada: {$viewFile}");
            }

            require_once $viewFile;
        }
    }