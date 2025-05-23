<?php
    namespace app\controllers;

    use app\models\books as books;

    class BooksController extends Controller {
        public function __construct(){
            parent::__construct();
        }

        public function index(){
            // Vista principal de libros
        }

        public function getBooks(){
            $books = new books();
            echo $books->getAllBooks(10);
        }

        public function getBook($params = null){
            if(isset($params[2])){
                $books = new books();
                $bookId = $params[2];
                echo $books->getBookById($bookId);
            }
        }

        public function search($params = null){
            if(isset($params[2])){
                $books = new books();
                $query = $params[2];
                echo $books->searchBooks($query);
            }
        }

        public function adminDashboard(){
            // Vista del dashboard de administración
            if($this->isAdmin()){
                $books = new books();
                $allBooks = json_decode($books->getAllBooks(100));
                $this->view('admin/books', ['books' => $allBooks]);
            } else {
                header('Location: /BookVerse/');
            }
        }

        private function isAdmin(){
            // Implementar verificación de administrador
            return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
        }
    }
