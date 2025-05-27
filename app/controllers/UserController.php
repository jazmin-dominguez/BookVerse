<?php

namespace app\controllers;

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/User.php';

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        
        // Verificar si la sesión ya está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            if ($this->isAjax) {
                ob_clean();
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'No autorizado. Por favor, inicie sesión.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                header('Location: /BookVerse/auth/login');
                exit;
            }
        }

        $this->userModel = new \app\models\User();
    }

    public function index() {
        $users = $this->userModel->getAllUsers();
        $this->view('admin/users', ['users' => $users]);
    }

    public function getAll() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        try {
            error_log('Iniciando getAll en UserController');

            // Verificar si es una petición AJAX
            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                throw new \Exception('Se requiere una petición AJAX');
            }

            // Verificar si la sesión está activa
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode([
                    'success' => false, 
                    'error' => 'No autorizado'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            // Verificar si es administrador
            if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                http_response_code(403);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Acceso denegado'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            error_log('Obteniendo usuarios de la base de datos...');
            $users = $this->userModel->getAllUsers();
            
            if ($users === false) {
                throw new \Exception('Error al obtener usuarios de la base de datos');
            }

            error_log('Número de usuarios obtenidos: ' . count($users));
            
            echo json_encode([
                'success' => true,
                'users' => $users
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            error_log('Error en UserController::getAll - ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error del servidor: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    public function profile() {
        $this->view('user/profile');
    }

    public function books() {
        $this->view('user/books');
    }

    public function reviews() {
        $this->view('user/reviews');
    }

    public function favorites() {
        $this->view('user/favorites');
    }

    public function readingList() {
        $this->view('user/reading_list');
    }

    private function processImage($imageData) {
        try {
            if (empty($imageData)) {
                return '/BookVerse/app/public/images/default-avatar.png';
            }

            // Extraer la información de la imagen base64
            if (strpos($imageData, ';base64,') === false) {
                throw new \Exception('Formato de imagen inválido');
            }

            $imageInfo = explode(';base64,', $imageData);
            $imageData = isset($imageInfo[1]) ? $imageInfo[1] : '';

            if (empty($imageData)) {
                throw new \Exception('Datos de imagen vacíos');
            }

            // Decodificar la imagen
            $imageDecoded = base64_decode($imageData, true);
            if ($imageDecoded === false) {
                throw new \Exception('Error al decodificar la imagen');
            }

            // Crear directorio si no existe
            $uploadDir = __DIR__ . '/../public/images/users/';
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new \Exception('Error al crear el directorio para imágenes');
                }
            }

            // Generar nombre único
            $fileName = uniqid('user_') . '.png';
            $filePath = $uploadDir . $fileName;

            // Guardar imagen
            if (file_put_contents($filePath, $imageDecoded) === false) {
                throw new \Exception('Error al guardar la imagen');
            }

            return '/BookVerse/app/public/images/users/' . $fileName;

        } catch (\Exception $e) {
            error_log('Error procesando imagen: ' . $e->getMessage());
            return '/BookVerse/app/public/images/default-avatar.png';
        }
    }

    public function create() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            $input = file_get_contents('php://input');
            error_log('Datos recibidos: ' . $input);
            
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Datos JSON inválidos: ' . json_last_error_msg());
            }
            
            if (empty($data)) {
                throw new \Exception('No se recibieron datos');
            }

            // Validar campos requeridos
            $requiredFields = ['username', 'full_name', 'email', 'password', 'role'];
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                throw new \Exception('Campos requeridos faltantes: ' . implode(', ', $missingFields));
            }

            // Procesar imagen si existe
            if (isset($data['imagen'])) {
                $data['imagen'] = $this->processImage($data['imagen']);
            }

            // Crear el usuario
            $userId = $this->userModel->insert($data);
            
            if (!$userId) {
                throw new \Exception('Error al crear el usuario en la base de datos');
            }
            
            $user = $this->userModel->getUserById($userId);
            if (empty($user)) {
                throw new \Exception('Error al recuperar el usuario creado');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'user' => $user[0]
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            error_log('Error en UserController::create - ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        
        exit;
    }

    public function update($params) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            error_log('Parámetros recibidos en update: ' . json_encode($params));
            
            if (empty($params['id'])) {
                throw new \Exception('ID de usuario requerido');
            }
    
            // Verificar autenticación
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                throw new \Exception('No autorizado');
            }
    
            // Verificar permisos de administrador
            if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                http_response_code(403);
                throw new \Exception('Acceso denegado');
            }
    
            $input = file_get_contents('php://input');
            error_log('Datos recibidos en update: ' . $input);
            
            if (empty($input)) {
                throw new \Exception('No se recibieron datos');
            }
            
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Datos JSON inválidos: ' . json_last_error_msg());
            }
            
            if (empty($data)) {
                throw new \Exception('No se recibieron datos para actualizar');
            }
    
            // Procesar imagen si existe
            if (!empty($data['imagen']) && strpos($data['imagen'], 'data:image') === 0) {
                $data['imagen'] = $this->processImage($data['imagen']);
            }
    
            // Actualizar usuario
            $success = $this->userModel->updateUser($params['id'], $data);
            
            if ($success) {
                $user = $this->userModel->getUserById($params['id']);
                if (!empty($user)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Usuario actualizado correctamente',
                        'user' => $user[0]
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    throw new \Exception('Error al recuperar el usuario actualizado');
                }
            } else {
                throw new \Exception('Error al actualizar el usuario');
            }
        } catch (\Exception $e) {
            error_log('Error en UserController::update - ' . $e->getMessage());
            if (http_response_code() === 200) {
                http_response_code(500);
            }
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    public function delete($params = []) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        
        try {
            // Verificar si es una petición AJAX
            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
                throw new \Exception('Se requiere una petición AJAX');
            }

            // Verificar si es administrador
            if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                http_response_code(403);
                throw new \Exception('No tiene permisos para realizar esta acción');
            }

            if (empty($params['id'])) {
                http_response_code(400);
                throw new \Exception('ID de usuario requerido');
            }

            error_log("Intentando eliminar usuario con ID: {$params['id']}");
            
            $success = $this->userModel->deleteUser($params['id']);
            
            if ($success) {
                error_log("Usuario {$params['id']} eliminado exitosamente");
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Usuario eliminado correctamente'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(500);
                throw new \Exception('Error al eliminar el usuario');
            }

        } catch (\Exception $e) {
            error_log("Error en delete: " . $e->getMessage());
            if (http_response_code() === 200) {
                http_response_code(500);
            }
            echo json_encode([
                'success' => false, 
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        
        exit;
    }
}
