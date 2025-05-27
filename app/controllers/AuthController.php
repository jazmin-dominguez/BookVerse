<?php

namespace app\controllers;

use app\controllers\Controller;

class AuthController extends Controller {

    public function __construct() {
        // Iniciar sesión en el constructor
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function getRedirectUrl($role) {
        switch ($role) {
            case 'admin':
                return '/BookVerse/admin/';
            case 'user':
                return '/BookVerse/';
            default:
                return '/BookVerse/';
        }
    }

    private function isAjaxRequest() {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    private function sendJsonResponse($data, $statusCode = 200) {
        // Limpiar cualquier salida anterior
        if (ob_get_length() > 0) {
            ob_clean();
        }

        // Establecer headers
        header_remove();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('X-Content-Type-Options: nosniff');

        // Enviar respuesta
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function index() {
        // Si ya está autenticado, redirigir según el rol
        if (isset($_SESSION['user_id'])) {
            $redirectUrl = $this->getRedirectUrl($_SESSION['user_role']);
            header('Location: ' . $redirectUrl);
            exit;
        }

        // Si no está autenticado, mostrar la vista de login
        $this->view('auth/login');
    }

    public function login() {
        try {
            // Si no es POST, mostrar la vista de login
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->view('auth/login');
                return;
            }

            // A partir de aquí es POST, establecer header JSON
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json; charset=utf-8');

            // Si ya está autenticado
            if (isset($_SESSION['user_id'])) {
                echo json_encode([
                    'success' => true,
                    'redirect' => $this->getRedirectUrl($_SESSION['user_role'])
                ]);
                return;
            }

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Por favor, complete todos los campos.'
                ]);
                return;
            }

            // Validar credenciales
            if ($email === 'admin@bookverse.com' && $password === 'admin123') {
                $_SESSION['user_id'] = 1;
                $_SESSION['full_name'] = 'Administrador';
                $_SESSION['user_role'] = 'admin';
                echo json_encode([
                    'success' => true,
                    'message' => '¡Bienvenido Administrador!',
                    'redirect' => $this->getRedirectUrl('admin')
                ]);
                return;
            } 
            
            if ($email === 'user@bookverse.com' && $password === 'user123') {
                $_SESSION['user_id'] = 2;
                $_SESSION['full_name'] = 'Usuario';
                $_SESSION['user_role'] = 'user';
                echo json_encode([
                    'success' => true,
                    'message' => '¡Bienvenido Usuario!',
                    'redirect' => $this->getRedirectUrl('user')
                ]);
                return;
            }

            echo json_encode([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view('auth/register');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!$this->isAjaxRequest()) {
                    throw new \Exception('Solicitud no válida');
                }

                $this->sendJsonResponse([
                    'success' => false,
                    'message' => 'Registro no disponible en este momento'
                ], 400);
            } catch (\Exception $e) {
                $this->sendJsonResponse([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 400);
            }
        }
    }

    public function logout() {
        session_destroy();
        header('Location: /BookVerse/auth/login');
        exit;
    }
}
