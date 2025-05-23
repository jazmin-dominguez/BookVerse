<?php

    namespace app\classes;

    class Views {

        public static function render($view, $data = [], $useLayout = true){
            // Convertir datos a objeto
            $d = as_object($data);
            
            if ($useLayout) {
                // Iniciar buffer de salida para capturar el contenido de la vista
                ob_start();
                require_once VIEWS . $view . '.view.php';
                $content = ob_get_clean();

                // Determinar qué layout usar basado en el tipo de usuario
                $layout = 'main';
                if (isset($_SESSION['user'])) {
                    $userType = $_SESSION['user']['role'] ?? 'user';
                    $layout = $userType === 'admin' ? 'admin' : 'user';
                }

                // Cargar el layout con el contenido
                require_once LAYOUTS . $layout . '.php';
            } else {
                // Cargar la vista directamente sin layout
                require_once VIEWS . $view . '.view.php';
            }
            exit();
        }

        public static function renderPartial($view, $data = []) {
            $d = as_object($data);
            require_once VIEWS . $view . '.view.php';
        }
    }