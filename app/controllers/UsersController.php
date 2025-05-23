<?php
    namespace app\controllers;

    use app\models\users as users;

    class UsersController extends Controller {
        public function __construct(){
            parent::__construct();
        }

        public function index(){
            // Vista principal de usuarios
        }

        public function adminDashboard(){
            if($this->isAdmin()){
                $users = new users();
                $allUsers = json_decode($users->getAllUsers(100));
                $this->view('admin/users', ['users' => $allUsers]);
            } else {
                header('Location: /BookVerse/');
            }
        }

        public function getUsers(){
            if($this->isAdmin()){
                $users = new users();
                echo $users->getAllUsers();
            }
        }

        public function getUser($params = null){
            if($this->isAdmin() && isset($params[2])){
                $users = new users();
                $userId = $params[2];
                $userData = json_decode($users->getUserById($userId));
                $userBooks = json_decode($users->getUserBorrowedBooks($userId));
                echo json_encode([
                    'user' => $userData,
                    'borrowed_books' => $userBooks
                ]);
            }
        }

        private function isAdmin(){
            return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
        }
    }
