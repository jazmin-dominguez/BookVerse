<?php

namespace app\controllers;

require_once __DIR__ . '/../resources/functions/session_functions.php';

class AdminController extends Controller {
    public function __construct() {
        startSession();
        requireAdmin();
    }

    public function index() {
        $this->view('admin/index');
    }

    public function books() {
        $this->view('admin/books');
    }

    public function users() {
        $this->view('admin/users');
    }

    public function formLibro() {
        $this->view('admin/formLibro');
    }

    public function createBook() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $booksModel = new \app\models\books();
            $result = $booksModel->createBook($data);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        }
    }

    public function updateBook($params = []) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $params['id'] ?? null;
            if (!$id) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            $booksModel = new \app\models\books();
            $result = $booksModel->updateBook($id, $data);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        }
    }

    public function deleteBook($params = []) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $params['id'] ?? null;
            if (!$id) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                exit;
            }
            
            $booksModel = new \app\models\books();
            $result = $booksModel->deleteBook($id);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => $result]);
            exit;
        }
    }

    public function createUser() {
        try {
            error_log('Iniciando createUser en AdminController');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $rawData = file_get_contents('php://input');
                error_log('Datos recibidos (raw): ' . $rawData);
                
                $data = json_decode($rawData, true);
                if (!$data) {
                    error_log('Error decodificando JSON: ' . json_last_error_msg());
                    throw new \Exception('Invalid JSON data: ' . json_last_error_msg());
                }
                error_log('Datos decodificados: ' . json_encode($data));

                $userModel = new \app\models\User();
                $result = $userModel->createUser($data);
                
                if ($result) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Usuario creado correctamente'
                    ]);
                } else {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'No se pudo crear el usuario'
                    ]);
                }
                exit;
            }
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function updateUser($params = []) {
        try {
            $id = $params['id'] ?? null;
            if (!$id) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                exit;
            }
            
            error_log('Iniciando updateUser en AdminController para ID: ' . $id);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $rawData = file_get_contents('php://input');
                error_log('Datos recibidos (raw): ' . $rawData);
                
                $data = json_decode($rawData, true);
                if (!$data) {
                    error_log('Error decodificando JSON: ' . json_last_error_msg());
                    throw new \Exception('Invalid JSON data: ' . json_last_error_msg());
                }
                error_log('Datos decodificados: ' . json_encode($data));

                $userModel = new \app\models\User();
                $result = $userModel->updateUser($id, $data);
                
                if ($result) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Usuario actualizado correctamente'
                    ]);
                } else {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'No se pudo actualizar el usuario'
                    ]);
                }
                exit;
            }
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function deleteUser($params = []) {
        try {
            $id = $params['id'] ?? null;
            if (!$id) {
                header('Content-Type: application/json');
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                exit;
            }
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userModel = new \app\models\User();
                $result = $userModel->deleteUser($id);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => $result]);
                exit;
            }
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    public function getAll() {
        try {
            $userModel = new \app\models\User();
            $users = $userModel->getAllUsers();
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'users' => $users]);
            exit;
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener usuarios: ' . $e->getMessage()
            ]);
            exit;
        }
    }
}