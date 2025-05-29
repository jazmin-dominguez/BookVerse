<?php

namespace app\controllers;

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/books.php';

class BooksController extends Controller {
    private $booksModel;

    public function __construct() {
        parent::__construct();
        
        // Verificar si la sesión ya está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->booksModel = new \app\models\Books();
    }

    public function index() {
        // Vista principal de libros (acceso público)
        $allBooks = $this->booksModel->getAllBooks(12);
        $this->view('books/index', ['books' => $allBooks]);
    }

    public function books() {
        // Vista de administración de libros (requiere autenticación)
        if (!$this->isAuthenticated()) {
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

        if (!$this->isAdmin()) {
            if ($this->isAjax) {
                ob_clean();
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Acceso denegado. Se requieren permisos de administrador.'
                ], JSON_UNESCAPED_UNICODE);
                exit;
            } else {
                header('Location: /BookVerse/');
                exit;
            }
        }

        $allBooks = $this->booksModel->getAllBooks(100);
        $this->view('admin/books', ['books' => $allBooks]);
    }

    public function getAll() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        try {
            error_log('Iniciando getAll en BooksController');

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

            error_log('Obteniendo libros de la base de datos...');
            $books = $this->booksModel->getAllBooks(1000);
            
            if ($books === false) {
                throw new \Exception('Error al obtener libros de la base de datos');
            }

            // Si getAllBooks devuelve JSON, decodificarlo
            if (is_string($books)) {
                $books = json_decode($books, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Error al decodificar datos de libros');
                }
            }

            error_log('Número de libros obtenidos: ' . count($books));
            
            echo json_encode([
                'success' => true,
                'books' => $books
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            error_log('Error en BooksController::getAll - ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error del servidor: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    public function getBook($params = null) {
        if (isset($params[0])) {
            $bookId = $params[0];
            $book = $this->booksModel->getBookById($bookId);
            
            if ($book && !empty($book)) {
                $this->view('user/vistalibro/bookdetails', ['book' => $book[0]]);
            } else {
                header('Location: /BookVerse/');
                exit;
            }
        } else {
            header('Location: /BookVerse/');
            exit;
        }
    }

    public function search($params = null) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            if (!isset($params[0]) || empty($params[0])) {
                throw new \Exception('Parámetro de búsqueda requerido');
            }

            $query = $params[0];
            $results = $this->booksModel->searchBooks($query);
            
            // Si searchBooks devuelve JSON, decodificarlo para validar
            if (is_string($results)) {
                $decoded = json_decode($results, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Error en los resultados de búsqueda');
                }
                echo $results; // Devolver el JSON original
            } else {
                echo json_encode([
                    'success' => true,
                    'books' => $results
                ], JSON_UNESCAPED_UNICODE);
            }

        } catch (\Exception $e) {
            error_log('Error en BooksController::search - ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    private function processBookImage($imageData) {
        try {
            if (empty($imageData)) {
                return '/BookVerse/app/public/images/default-book.png';
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
            $uploadDir = __DIR__ . '/../public/images/books/';
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new \Exception('Error al crear el directorio para imágenes');
                }
            }

            // Generar nombre único
            $fileName = uniqid('book_') . '.png';
            $filePath = $uploadDir . $fileName;

            // Guardar imagen
            if (file_put_contents($filePath, $imageDecoded) === false) {
                throw new \Exception('Error al guardar la imagen');
            }

            return '/BookVerse/app/public/images/books/' . $fileName;

        } catch (\Exception $e) {
            error_log('Error procesando imagen de libro: ' . $e->getMessage());
            return '/BookVerse/app/public/images/default-book.png';
        }
    }

    public function create() {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Verificar autenticación y permisos
            if (!$this->isAuthenticated()) {
                http_response_code(401);
                throw new \Exception('No autorizado');
            }

            if (!$this->isAdmin()) {
                http_response_code(403);
                throw new \Exception('Se requieren permisos de administrador');
            }

            $input = file_get_contents('php://input');
            error_log('Datos recibidos: ' . $input);
            
            $data = json_decode($input, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Datos JSON inválidos: ' . json_last_error_msg());
            }
            
            if (empty($data)) {
                throw new \Exception('No se recibieron datos');
            }

            // Validar campos requeridos (ajusta según tu modelo)
            $requiredFields = ['title', 'author', 'isbn'];
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
            if (isset($data['cover_image'])) {
                $data['cover_image'] = $this->processBookImage($data['cover_image']);
            }

            // Crear el libro usando las funciones existentes
            require_once __DIR__ . '/../resources/functions/books_functions.php';
            $bookId = createBook($data);
            
            if (!$bookId) {
                throw new \Exception('Error al crear el libro en la base de datos');
            }
            
            $book = $this->booksModel->getBookById($bookId);
            if (empty($book)) {
                throw new \Exception('Error al recuperar el libro creado');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Libro creado correctamente',
                'book' => $book[0]
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            error_log('Error en BooksController::create - ' . $e->getMessage());
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

    public function update($params) {
        ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            error_log('Parámetros recibidos en update: ' . json_encode($params));
            
            if (empty($params['id'])) {
                throw new \Exception('ID de libro requerido');
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
            if (!empty($data['cover_image']) && strpos($data['cover_image'], 'data:image') === 0) {
                $data['cover_image'] = $this->processBookImage($data['cover_image']);
            }

            // Actualizar libro usando las funciones existentes
            require_once __DIR__ . '/../resources/functions/books_functions.php';
            $success = updateBook($params['id'], $data);
            
            if ($success) {
                $book = $this->booksModel->getBookById($params['id']);
                if (!empty($book)) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Libro actualizado correctamente',
                        'book' => $book[0]
                    ], JSON_UNESCAPED_UNICODE);
                } else {
                    throw new \Exception('Error al recuperar el libro actualizado');
                }
            } else {
                throw new \Exception('Error al actualizar el libro');
            }
        } catch (\Exception $e) {
            error_log('Error en BooksController::update - ' . $e->getMessage());
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
                throw new \Exception('ID de libro requerido');
            }

            error_log("Intentando eliminar libro con ID: {$params['id']}");
            
            // Usar las funciones existentes
            require_once __DIR__ . '/../resources/functions/books_functions.php';
            $success = deleteBook($params['id']);
            
            if ($success) {
                error_log("Libro {$params['id']} eliminado exitosamente");
                http_response_code(200);
                echo json_encode([
                    'success' => true, 
                    'message' => 'Libro eliminado correctamente'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(500);
                throw new \Exception('Error al eliminar el libro');
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

    // Métodos auxiliares
    private function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    private function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    // Métodos adicionales para compatibilidad
    public function store() {
        // Redirigir al método create para mantener compatibilidad
        $this->create();
    }

    public function edit($params = null) {
        if (!$this->isAdmin() || !isset($params[0])) {
            header('Location: /BookVerse/auth/login');
            exit;
        }

        require_once __DIR__ . '/../resources/functions/books_functions.php';
        $book = getBookById($params[0]);
        if (!$book) {
            header('Location: /BookVerse/admin/books');
            exit;
        }

        $this->view('admin/books/form', ['book' => $book]);
    }
}