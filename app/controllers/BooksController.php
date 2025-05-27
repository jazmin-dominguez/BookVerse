<?php
    namespace app\controllers;

    use app\models\books as books;
    require_once __DIR__ . '/Controller.php';

    class BooksController extends Controller {
        public function __construct(){
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }

        public function index(){
            // Vista principal de libros
            $books = new books();
            $allBooks = $books->getAllBooks(12);
            $this->view('books/index', ['books' => $allBooks]);
        }

        public function books() {
            if(!$this->isAdmin()) {
                header('Location: /BookVerse/auth/login');
                exit;
            }
            $books = new books();
            $allBooks = $books->getAllBooks(100);
            $this->view('admin/books', ['books' => $allBooks]);
        }

        public function getBooks(){
            $books = new books();
            echo $books->getAllBooks(10);
        }

        public function getBook($params = null){
            if(isset($params[0])){
                $books = new books();
                $bookId = $params[0];
                $book = $books->getBookById($bookId);
                
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

        public function search($params = null){
            if(isset($params[0])){
                $books = new books();
                $query = $params[0];
                echo $books->searchBooks($query);
            }
        }

        private function isAdmin(){
            return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
        }

        public function create() {
            if(!$this->isAdmin()) {
                header('Location: /BookVerse/auth/login');
                exit;
            }
            require_once VIEWS . 'admin/books/form.view.php';
        }

        public function store() {
            if(!$this->isAdmin()) {
                echo json_encode(['success' => false, 'message' => 'No autorizado']);
                exit;
            }

            try {
                require_once __DIR__ . '/../resources/functions/books_functions.php';
                createBook($_POST);
                echo json_encode(['success' => true]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        public function edit($params = null) {
            if(!$this->isAdmin() || !isset($params[0])) {
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

        public function update($params = null) {
            if(!$this->isAdmin() || !isset($params[0])) {
                echo json_encode(['success' => false, 'message' => 'No autorizado']);
                exit;
            }

            try {
                require_once __DIR__ . '/../resources/functions/books_functions.php';
                updateBook($params[0], $_POST);
                echo json_encode(['success' => true]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }

        public function delete($params = null) {
            if(!$this->isAdmin() || !isset($params[0])) {
                echo json_encode(['success' => false, 'message' => 'No autorizado']);
                exit;
            }

            try {
                require_once __DIR__ . '/../resources/functions/books_functions.php';
                deleteBook($params[0]);
                echo json_encode(['success' => true]);
            } catch (\Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        }
    }
