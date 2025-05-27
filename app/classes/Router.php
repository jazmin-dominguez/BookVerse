<?php 

    namespace app\classes;

    use app\controllers\ErrorController as ErrorController;

    class Router{
        private $uri = [];
        
        public function __construct(){}

        public function route() {
            try {
                $this->filterRequest();
                $controller = $this->getController();
                $method     = $this->getMethod();
                $params     = $this->getParams();

                error_log('Router - Controller: ' . $controller);
                error_log('Router - Method: ' . $method);
                error_log('Router - Params: ' . json_encode($params));

                if (!class_exists($controller)) {
                    throw new \Exception('Controlador no encontrado: ' . $controller);
                }

                $controllerInstance = new $controller();

                if (!method_exists($controllerInstance, $method)) {
                    throw new \Exception('Método no encontrado: ' . $method . ' en ' . $controller);
                }

                return $controllerInstance->$method($params);

            } catch (\Exception $e) {
                error_log('Router Error: ' . $e->getMessage());
                // Si es una petición AJAX, devolver error en JSON
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    http_response_code(404);
                    echo json_encode(['error' => $e->getMessage()]);
                    exit;
                }
                
                // Si no es AJAX, mostrar página de error
                $errorController = new ErrorController();
                return $errorController->error404();
            }
        }

        private function filterRequest(){
            $this->uri = [];
            $request = filter_input_array(INPUT_GET);
            
            if(isset($request['uri'])){
                $uri = $request['uri'];
                $uri = rtrim($uri,'/');
                $uri = filter_var($uri, FILTER_SANITIZE_URL);
                $this->uri = explode('/', $uri);
            }
        }

        private function getController(){
            $controller = 'app\\controllers\\HomeController';
            
            if (!empty($this->uri)) {
                // Manejar rutas de autenticación
                if ($this->uri[0] === 'auth') {
                    return 'app\\controllers\\AuthController';
                }
                
                // Manejar TODAS las rutas admin con AdminController
                if ($this->uri[0] === 'admin') {
                    return 'app\\controllers\\AdminController';
                }
                
                // Manejar otras rutas (no admin)
                switch (strtolower($this->uri[0])) {
                    case 'users':
                        return 'app\\controllers\\UserController';
                    case 'books':
                        return 'app\\controllers\\BookController';
                    case 'reviews':
                        return 'app\\controllers\\ReviewController';
                }  
            }
            
            return $controller;
        }

        private function getMethod(){
            $method = 'index';
            $segments = $this->uri;
            $totalSegments = count($segments);
            
            error_log('getMethod - Total segments: ' . $totalSegments);
            error_log('getMethod - Segments: ' . json_encode($segments));
            
            if(!empty($this->uri) && $segments[0] === 'admin'){
                // Rutas admin específicas
                
                // /admin -> AdminController::index (dashboard)
                if ($totalSegments === 1) {
                    return 'index';
                }
                
                // /admin/users -> AdminController::users (vista de usuarios)  
                if ($totalSegments === 2 && $segments[1] === 'users') {
                    return 'users';
                }
                
                // /admin/books -> AdminController::books (vista de libros)
                if ($totalSegments === 2 && $segments[1] === 'books') {
                    return 'books';
                }
                
                // /admin/users/getall -> AdminController::getAll
                if ($totalSegments === 3 && $segments[1] === 'users' && $segments[2] === 'getall') {
                    return 'getAll';
                }
                
                // /admin/users/create -> AdminController::createUser (necesitas agregarlo)
                if ($totalSegments === 3 && $segments[1] === 'users' && $segments[2] === 'create') {
                    return 'createUser';
                }
                
                // /admin/users/{id}/update -> AdminController::updateUser
                if ($totalSegments === 4 && $segments[1] === 'users' && is_numeric($segments[2]) && $segments[3] === 'update') {
                    $this->uri['stored_id'] = $segments[2];
                    return 'updateUser';
                }
                
                // /admin/users/{id}/delete -> AdminController::deleteUser
                if ($totalSegments === 4 && $segments[1] === 'users' && is_numeric($segments[2]) && $segments[3] === 'delete') {
                    $this->uri['stored_id'] = $segments[2];
                    return 'deleteUser';
                }
                
                // /admin/books/create -> AdminController::createBook
                if ($totalSegments === 3 && $segments[1] === 'books' && $segments[2] === 'create') {
                    return 'createBook';
                }
                
                // /admin/books/{id}/update -> AdminController::updateBook
                if ($totalSegments === 4 && $segments[1] === 'books' && is_numeric($segments[2]) && $segments[3] === 'update') {
                    $this->uri['stored_id'] = $segments[2];
                    return 'updateBook';
                }
                
                // /admin/books/{id}/delete -> AdminController::deleteBook
                if ($totalSegments === 4 && $segments[1] === 'books' && is_numeric($segments[2]) && $segments[3] === 'delete') {
                    $this->uri['stored_id'] = $segments[2];
                    return 'deleteBook';
                }
                
                // Fallback para otras rutas admin
                if ($totalSegments >= 2) {
                    return $segments[1];
                }
            }
            
            // Rutas no-admin
            if(!empty($this->uri) && $segments[0] !== 'admin'){
                if(isset($this->uri[1])){
                    if(is_numeric($this->uri[1])) {
                        $this->uri['stored_id'] = $this->uri[1];
                        $method = 'view';
                    } else {
                        $method = $this->uri[1];
                    }
                }
            }
            
            error_log('Método final: ' . $method);
            return $method;
        }

        private function getParams(){
            $params = [];
            
            // Si tenemos un ID almacenado, usarlo
            if(isset($this->uri['stored_id'])){
                $params['id'] = filter_var($this->uri['stored_id'], FILTER_SANITIZE_NUMBER_INT);
                error_log('Usando ID almacenado: ' . $params['id']);
            }
            // Fallback: buscar ID en los segmentos
            else if(!empty($this->uri)){
                // Buscar un ID numérico en los segmentos
                foreach($this->uri as $segment) {
                    if(is_numeric($segment)) {
                        $params['id'] = filter_var($segment, FILTER_SANITIZE_NUMBER_INT);
                        error_log('ID encontrado en segmentos: ' . $params['id']);
                        break;
                    }
                }
            }
            
            error_log('Params finales: ' . json_encode($params));
            return $params;
        }
    }