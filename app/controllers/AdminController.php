<?php

namespace app\controllers;

require_once __DIR__ . '/../resources/functions/session_functions.php';

class AdminController extends Controller {
    public function __construct() {
        parent::__construct();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /BookVerse');
            exit;
        }
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

    /**
     * Maneja la subida de archivos de imagen
     */
    private function handleImageUpload($file) {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new \Exception('Tipo de archivo no válido. Solo se permiten JPG, PNG y GIF');
        }

        // Validar tamaño (máximo 5MB)
        if ($file['size'] > 5000000) {
            throw new \Exception('El archivo es muy grande. Máximo 5MB');
        }

        // Crear directorio si no existe
        $uploadDir = __DIR__ . '/../../public/images/books/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generar nombre único para el archivo
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('book_', true) . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;

        // Mover archivo
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new \Exception('Error al subir la imagen');
        }

        // Retornar la ruta relativa para guardar en BD
        return '/BookVerse/app/public/images/books/' . $fileName;
    }

    /**
     * Elimina una imagen del servidor
     */
    private function deleteImage($imagePath) {
        if ($imagePath && !strpos($imagePath, 'default-book.png')) {
            $fullPath = __DIR__ . '/../../public' . str_replace('/BookVerse/app/public', '', $imagePath);
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    public function createBook() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Procesar datos del formulario (FormData en lugar de JSON)
                $data = [
                    'title' => $_POST['title'] ?? '',
                    'author' => $_POST['author'] ?? '',
                    'genre' => $_POST['genre'] ?? '',
                    'isbn' => $_POST['isbn'] ?? '',
                    'publication_year' => $_POST['publication_year'] ?? '',
                    'pages' => $_POST['pages'] ?? null,
                    'stock' => $_POST['stock'] ?? 1,
                    'description' => $_POST['description'] ?? ''
                ];

                // Validar campos requeridos
                $requiredFields = ['title', 'author', 'genre', 'isbn', 'publication_year'];
                foreach ($requiredFields as $field) {
                    if (empty($data[$field])) {
                        throw new \Exception("El campo {$field} es requerido");
                    }
                }

                // Manejar subida de imagen
                $coverImage = null;
                if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    $coverImage = $this->handleImageUpload($_FILES['cover_image']);
                    $data['cover_image'] = $coverImage;
                }

                error_log('Datos recibidos para crear libro: ' . json_encode($data));
                
                $booksModel = new \app\models\Books();
                $bookId = $booksModel->insert($data);
                
                if ($bookId) {
                    $newBook = $booksModel->getBookById($bookId);
                    
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'book' => $newBook ? $newBook[0] : null,
                        'message' => 'Libro creado correctamente'
                    ]);
                } else {
                    // Si falló la inserción y subimos imagen, eliminarla
                    if ($coverImage) {
                        $this->deleteImage($coverImage);
                    }
                    throw new \Exception('No se pudo crear el libro');
                }
                
            } catch (\Exception $e) {
                error_log('Error en createBook: ' . $e->getMessage());
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            exit;
        }
    }

    public function updateBook($params = []) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $params['id'] ?? null;
                if (!$id) {
                    throw new \Exception('ID requerido');
                }
                
                // Procesar datos del formulario
                $data = [
                    'title' => $_POST['title'] ?? '',
                    'author' => $_POST['author'] ?? '',
                    'genre' => $_POST['genre'] ?? '',
                    'isbn' => $_POST['isbn'] ?? '',
                    'publication_year' => $_POST['publication_year'] ?? '',
                    'pages' => $_POST['pages'] ?? null,
                    'stock' => $_POST['stock'] ?? 1,
                    'description' => $_POST['description'] ?? ''
                ];

                // Validar campos requeridos
                $requiredFields = ['title', 'author', 'genre', 'isbn', 'publication_year'];
                foreach ($requiredFields as $field) {
                    if (empty($data[$field])) {
                        throw new \Exception("El campo {$field} es requerido");
                    }
                }

                $booksModel = new \app\models\Books();
                
                // Obtener libro actual para manejar imagen anterior
                $currentBook = $booksModel->getBookById($id);
                $oldCoverImage = null;
                if ($currentBook && isset($currentBook[0]['cover_image'])) {
                    $oldCoverImage = $currentBook[0]['cover_image'];
                }

                // Manejar nueva imagen si se subió
                if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                    $newCoverImage = $this->handleImageUpload($_FILES['cover_image']);
                    $data['cover_image'] = $newCoverImage;
                    
                    // Eliminar imagen anterior si existe y no es la por defecto
                    if ($oldCoverImage) {
                        $this->deleteImage($oldCoverImage);
                    }
                }

                error_log('Datos recibidos para actualizar libro ' . $id . ': ' . json_encode($data));
                
                $result = $booksModel->updateBook($id, $data);
                
                if ($result) {
                    $updatedBook = $booksModel->getBookById($id);
                    
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'book' => $updatedBook ? $updatedBook[0] : null,
                        'message' => 'Libro actualizado correctamente'
                    ]);
                } else {
                    // Si falló la actualización y subimos nueva imagen, eliminarla
                    if (isset($data['cover_image'])) {
                        $this->deleteImage($data['cover_image']);
                    }
                    throw new \Exception('No se pudo actualizar el libro');
                }
                
            } catch (\Exception $e) {
                error_log('Error en updateBook: ' . $e->getMessage());
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            exit;
        }
    }

    public function deleteBook($params = []) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $id = $params['id'] ?? null;
                if (!$id) {
                    throw new \Exception('ID requerido');
                }
                
                error_log('Intentando eliminar libro con ID: ' . $id);
                
                $booksModel = new \app\models\Books();
                
                // Obtener libro para eliminar su imagen
                $book = $booksModel->getBookById($id);
                $coverImage = null;
                if ($book && isset($book[0]['cover_image'])) {
                    $coverImage = $book[0]['cover_image'];
                }
                
                $result = $booksModel->deleteBook($id);
                
                if ($result) {
                    // Eliminar imagen del servidor
                    if ($coverImage) {
                        $this->deleteImage($coverImage);
                    }
                    
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Libro eliminado correctamente'
                    ]);
                } else {
                    throw new \Exception('No se pudo eliminar el libro');
                }
                
            } catch (\Exception $e) {
                error_log('Error en deleteBook: ' . $e->getMessage());
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage()
                ]);
            }
            exit;
        }
    }

    // Método para obtener todos los libros (útil para cargar la tabla)
    public function getAllBooks() {
        try {
            $booksModel = new \app\models\Books();
            $books = $booksModel->getAllBooks();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'books' => $books
            ]);
            
        } catch (\Exception $e) {
            error_log('Error en getAllBooks: ' . $e->getMessage());
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
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