<?php

function getAllBooks($limit = 10) {
    $db = new \app\classes\DB();
    return $db->setTable('books')
             ->select(['id', 'title', 'author', 'genre', 'publication_year', 'isbn', 'cover_image'])
             ->orderBy([['title', 'ASC']])
             ->limit($limit)
             ->get();
}

function getBookById($id) {
    $db = new \app\classes\DB();
    $result = $db->setTable('books')
                 ->where([['id', '=', $id]])
                 ->get();
    return $result ? $result[0] : null;
}

function searchBooks($query, $filters = []) {
    $db = new \app\classes\DB(); 
    $conditions = [];
    
    // Búsqueda por texto
    if (!empty($query)) {
        $conditions[] = ['title', 'LIKE', "%$query%", 'OR'];
        $conditions[] = ['author', 'LIKE', "%$query%", 'OR'];
        $conditions[] = ['genre', 'LIKE', "%$query%"];
    }
    
    // Aplicar filtros adicionales
    if (!empty($filters['author'])) {
        $conditions[] = ['author', '=', $filters['author']];
    }
    if (!empty($filters['genre'])) {
        $conditions[] = ['genre', '=', $filters['genre']];
    }
    
    $query = $db->setTable('books');
    if (!empty($conditions)) {
        $query = $query->where($conditions);
    }
    
    return $query->orderBy([['title', 'ASC']])->get();
}

function createBook($data) {
    $db = new \app\classes\DB();
    
    // Validar datos requeridos
    $required = ['title', 'author', 'genre', 'publication_year'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new \Exception("El campo $field es requerido");
        }
    }
    
    // Manejar la imagen de portada si se proporciona
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../public/uploads/covers/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['cover_image']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadFile)) {
            $data['cover_image'] = 'uploads/covers/' . $fileName;
        }
    }
    
    return $db->setTable('books')
             ->setFillable(array_keys($data))
             ->setValues(array_values($data))
             ->create();
}

function updateBook($id, $data) {
    $db = new \app\classes\DB();
    
    // Manejar la imagen de portada si se proporciona
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        // Eliminar imagen anterior si existe
        $currentBook = getBookById($id);
        if ($currentBook && !empty($currentBook['cover_image'])) {
            $oldImage = __DIR__ . '/../../public/' . $currentBook['cover_image'];
            if (file_exists($oldImage)) {
                unlink($oldImage);
            }
        }
        
        $uploadDir = __DIR__ . '/../../public/uploads/covers/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['cover_image']['name']);
        $uploadFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadFile)) {
            $data['cover_image'] = 'uploads/covers/' . $fileName;
        }
    }
    
    return $db->setTable('books')
             ->where([['id', '=', $id]])
             ->update($data);
}

function deleteBook($id) {
    $db = new \app\classes\DB();
    
    // Primero obtenemos el libro para eliminar su imagen
    $book = getBookById($id);
    if ($book && !empty($book['cover_image'])) {
        $imagePath = __DIR__ . '/../../public/' . $book['cover_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    return $db->setTable('books')
             ->where([['id', '=', $id]])
             ->delete();
}

function getAuthors() {
    $db = new \app\classes\DB();
    return $db->setTable('books')
             ->select(['DISTINCT author'])
             ->orderBy([['author', 'ASC']])
             ->get();
}

function getGenres() {
    $db = new \app\classes\DB();
    return $db->setTable('books')
             ->select(['DISTINCT genre'])
             ->orderBy([['genre', 'ASC']])
             ->get();
}
