<?php
    namespace app\models;

    require_once __DIR__ . '/../models/Model.php';

    class likes extends Model {
        protected $table = 'interactions';
        protected $primaryKey = 'id';
        protected $fillable = ['user_id', 'book_id', 'interaction_type'];

        public function getTotalLikes() {
            $result = $this->query("SELECT COUNT(*) as total FROM interactions WHERE interaction_type = 'like'");
            return $result[0]['total'] ?? 0;
        }

        public function getTotalUsers() {
            $result = $this->query("SELECT COUNT(DISTINCT user_id) as total FROM users");
            return $result[0]['total'] ?? 0;
        }

        public function getTotalBooks() {
            $result = $this->query("SELECT COUNT(*) as total FROM books");
            return $result[0]['total'] ?? 0;
        }

        public function getLikedBooksPercentage() {
            $result = $this->query("
                SELECT 
                    ROUND(
                        (COUNT(DISTINCT book_id) * 100.0 / (SELECT COUNT(*) FROM books))
                    , 1) as percentage
                FROM interactions 
                WHERE interaction_type = 'like'
            ");
            return $result[0]['percentage'] ?? 0;
        }

        public function getUsersWithLikesPercentage() {
            $result = $this->query("
                SELECT 
                    ROUND(
                        (COUNT(DISTINCT user_id) * 100.0 / (SELECT COUNT(*) FROM users))
                    , 1) as percentage
                FROM interactions 
                WHERE interaction_type = 'like'
            ");
            return $result[0]['percentage'] ?? 0;
        }

        public function getBooksWithLikesPercentage() {
            $result = $this->query("
                SELECT 
                    ROUND(
                        (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM books))
                    , 1) as percentage
                FROM (
                    SELECT book_id, COUNT(*) as like_count
                    FROM interactions
                    WHERE interaction_type = 'like'
                    GROUP BY book_id
                    HAVING like_count > 0
                ) liked_books
            ");
            return $result[0]['percentage'] ?? 0;
        }
    }
