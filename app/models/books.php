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
            $result = $this -> select(['id', 'title', 'author', 'description', 'genre', 
                                     'publication_year', 'isbn', 'cover_image', 'stock'])
                           -> where([['active', 1]])
                           -> orderBy([['title', 'asc']])
                           -> limit($limit)
                           -> get();
            return $result;
        }

        public function getBookById($bookId){
            $result = $this -> select(['id', 'title', 'author', 'description', 'genre', 
                                     'publication_year', 'isbn', 'cover_image', 'stock'])
                           -> where([['id', $bookId], ['active', 1]])
                           -> get();
            return $result;
        }

        public function searchBooks($query){
            $result = $this -> select(['id', 'title', 'author', 'description', 'genre'])
                           -> where([['active', 1], 
                                   ['title', "LIKE", "%$query%", "OR"],
                                   ['author', "LIKE", "%$query%", "OR"],
                                   ['description', "LIKE", "%$query%"]])
                           -> orderBy([['title', 'asc']])
                           -> get();
            return $result;
        }
    }
