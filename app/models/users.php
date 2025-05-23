<?php
    namespace app\models;

    class users extends Model {
        protected $table;
        protected $fillable = [
            'name',
            'email',
            'password',
            'role',
            'active',
            'profile_image'
        ];

        public function __construct(){
            parent::__construct();
            $this->table = $this->connect();
        }

        public $values = [];

        public function getAllUsers($limit = 10){
            $result = $this -> select(['id', 'name', 'email', 'role', 'active', 'profile_image',
                                     'date_format(created_at,"%d/%m/%Y") as fecha'])
                           -> where([['active', 1]])
                           -> orderBy([['name', 'asc']])
                           -> limit($limit)
                           -> get();
            return $result;
        }

        public function getUserById($userId){
            $result = $this -> select(['id', 'name', 'email', 'role', 'active', 'profile_image',
                                     'date_format(created_at,"%d/%m/%Y") as fecha'])
                           -> where([['id', $userId]])
                           -> get();
            return $result;
        }

        public function getUserBorrowedBooks($userId){
            $result = $this -> select(['b.id', 'b.title', 'b.author', 
                                     'date_format(bb.borrow_date,"%d/%m/%Y") as borrow_date',
                                     'date_format(bb.return_date,"%d/%m/%Y") as return_date'])
                           -> join('borrowed_books bb', 'bb.user_id = users.id')
                           -> join('books b', 'b.id = bb.book_id')
                           -> where([['users.id', $userId]])
                           -> orderBy([['bb.borrow_date', 'desc']])
                           -> get();
            return $result;
        }
    }
