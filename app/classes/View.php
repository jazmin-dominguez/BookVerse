<?php
namespace app\classes;

class View {
    private static $layouts_path = __DIR__ . '/../resources/layouts/';
    private static $views_path = __DIR__ . '/../resources/views/';

    public static function render($view, $data = [], $layout = 'main') {
        // Extraer variables del array $data
        extract($data);

        // Iniciar el buffer de salida
        ob_start();
        
        // Cargar la vista
        $view_file = self::$views_path . $view . '.view.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            throw new \Exception("Vista no encontrada: {$view}");
        }
        
        // Obtener el contenido del buffer y limpiarlo
        $content = ob_get_clean();

        // Cargar el layout
        $layout_file = self::$layouts_path . $layout . '.php';
        if (file_exists($layout_file)) {
            include $layout_file;
        } else {
            echo $content;
        }
    }

    public static function renderPartial($view, $data = []) {
        extract($data);
        $view_file = self::$views_path . $view . '.view.php';
        if (file_exists($view_file)) {
            include $view_file;
        } else {
            throw new \Exception("Vista parcial no encontrada: {$view}");
        }
    }
}
