<?php
    namespace app\models;

    class books extends Model {
        protected $table;
        protected $fillable = [
            'title',
            'author',
            'description',
            'genre',
            'publication_year',
            'isbn',
            'cover_image',
            'stock',
            'active'
        ];

        public function __construct(){
            parent::__construct();
            $this->table = $this->connect();
        }
        
        public $values = [];

        public function getAllBooks($limit = 10){
            $result = $this -> select(['id', 'title', 'author', 'genre', 
                                     'publication_year', 'isbn', 'cover_image'])
                           -> orderBy([['title', 'asc']])
                           -> limit($limit)
                           -> get();
            return $result;
        }

        public function getRecentBooks($limit = 5) {
            return $this->select(['id', 'title', 'author', 'genre', 'publication_year'])
                        ->orderBy([['created_at', 'desc']])
                        ->limit($limit)
                        ->get();
        }

        public function getTotalBooks() {
            $result = $this->query("SELECT COUNT(*) as total FROM books");
            return $result[0]['total'] ?? 0;
        }

        public function getBookById($bookId){
            $result = $this -> select(['id', 'title', 'author', 'genre', 
                                     'publication_year', 'isbn', 'cover_image'])
                           -> where([['id', $bookId]])
                           -> get();
            return $result;
        }

        public function searchBooks($query){
            $result = $this -> select(['id', 'title', 'author', 'genre'])
                           -> where([['title', "LIKE", "%$query%", "OR"],
                                   ['author', "LIKE", "%$query%", "OR"],
                                   ['genre', "LIKE", "%$query%"]])
                           -> orderBy([['title', 'asc']])
                           -> get();
            return $result;
        }
    }
