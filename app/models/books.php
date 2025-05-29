<?php
namespace app\models;

require_once __DIR__ . '/Model.php';

class Books extends Model {
    protected $tableName = 'books';
    protected $fillable = [
        'title',
        'author',
        'description',
        'genre',
        'publication_year',
        'isbn',
        'cover_image',
        'pages',
        'stock',
        'status',
        'created_at',
        'updated_at'
    ];

    public function __construct(){
        parent::__construct();
    }

    public $values = [];

    public function getAllBooks($limit = null){
        try {
            error_log('Iniciando getAllBooks en Book model');
            
            $query = $this->select([
                'id', 'title', 'author', 'description', 'genre', 
                'publication_year', 'isbn', 'cover_image', 'pages', 'stock', 
                'status', 'created_at', 'updated_at'
            ]) ->where([['status', 'active']]);

            // Ordenar por título
            $query->orderBy([['title', 'asc']]);

            // Aplicar límite solo si se especifica
            if ($limit !== null) {
                $query->limit($limit);
            }

            $result = $query->get();
            
            error_log('Libros encontrados: ' . ($result ? count($result) : 0));
            if (!$result) {
                error_log('No se encontraron libros o hubo un error en la consulta');
            }

            return $result;

        } catch (\Exception $e) {
            error_log('Error en getAllBooks: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getRecentBooks($limit = 5) {
        return $this->select(['id', 'title', 'author', 'genre', 'publication_year', 'created_at'])
                    ->orderBy([['created_at', 'desc']])
                    ->limit($limit)
                    ->get();
    }

    public function getTotalBooks() {
        $result = $this->query("SELECT COUNT(*) as total FROM books WHERE status = 'active'");
        return $result[0]['total'] ?? 0;
    }

    public function getBookById($bookId){
        $result = $this -> select(['id', 'title', 'author', 'description', 'genre', 
                                 'publication_year', 'isbn', 'cover_image', 'pages', 'stock', 'status',
                                 'created_at', 'updated_at'])
                       -> where([['id', $bookId]])
                       -> get();
        return $result;
    }

    public function getBookBorrowHistory($bookId){
        $result = $this -> select(['u.id', 'u.full_name', 'u.email', 
                                 'date_format(bb.borrow_date,"%d/%m/%Y") as borrow_date',
                                 'date_format(bb.return_date,"%d/%m/%Y") as return_date',
                                 'bb.status as borrow_status'])
                       -> join('borrowed_books bb', 'bb.book_id = books.id')
                       -> join('users u', 'u.id = bb.user_id')
                       -> where([['books.id', $bookId]])
                       -> orderBy([['bb.borrow_date', 'desc']])
                       -> get();
        return $result;
    }

    public function searchBooks($query){
        $result = $this -> select(['id', 'title', 'author', 'genre', 'publication_year', 'stock'])
                       -> where([['title', "LIKE", "%$query%", "OR"],
                               ['author', "LIKE", "%$query%", "OR"],
                               ['genre', "LIKE", "%$query%", "OR"],
                               ['isbn', "LIKE", "%$query%"]])
                       -> where([['status', 'active']])
                       -> orderBy([['title', 'asc']])
                       -> get();
        return $result;
    }

    public function insert($data) {
        try {
            error_log('Iniciando insert en Books model con datos: ' . json_encode($data));
            
            // Validar campos requeridos
            $requiredFields = ['title', 'author', 'genre', 'isbn', 'publication_year'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new \Exception("El campo {$field} es requerido");
                }
            }

            // Verificar si el ISBN ya existe (si se proporciona)
            if (!empty($data['isbn'])) {
                $existingIsbn = $this->select(['id'])
                    ->where([['isbn', $data['isbn']], ['status', 'active']])
                    ->get();
                if ($existingIsbn) {
                    throw new \Exception("El ISBN ya está registrado");
                }
            }

            // Establecer valores por defecto
            $data['status'] = 'active';
            $data['stock'] = $data['stock'] ?? 1;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Filtrar solo los campos permitidos
            $fields = [];
            $values = [];
            $placeholders = [];
            
            foreach ($this->fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $fields[] = $field;
                    $values[] = $data[$field];
                    $placeholders[] = '?';
                }
            }
            
            if (empty($fields)) {
                throw new \Exception("No hay campos válidos para insertar");
            }

            $sql = "INSERT INTO " . $this->tableName . 
                   " (" . implode(",", $fields) . ") VALUES (" . 
                   implode(",", $placeholders) . ")";

            error_log('SQL: ' . $sql);
            error_log('Values: ' . json_encode($values));

            $stmt = $this->table->prepare($sql);
            if (!$stmt) {
                throw new \Exception("Error preparando la consulta: " . $this->table->error);
            }

            // Crear string de tipos para bind_param
            $types = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);
            
            if (!$stmt->execute()) {
                throw new \Exception("Error ejecutando la consulta: " . $stmt->error);
            }
            
            $id = $stmt->insert_id;
            $stmt->close();
            
            error_log('Libro insertado con ID: ' . $id);
            return $id;
            
        } catch (\mysqli_sql_exception $e) {
            throw new \Exception("Error de base de datos: " . $e->getMessage());
        } catch (\Exception $e) {
            error_log("Error al insertar libro: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateBook($id, $data) {
        try {
            error_log('Iniciando updateBook para ID: ' . $id);
            error_log('Datos recibidos: ' . json_encode($data));

            // Validar que haya datos para actualizar
            if (empty($data)) {
                throw new \Exception('No hay datos para actualizar');
            }

            // Filtrar solo los campos permitidos
            $validData = [];
            foreach ($this->fillable as $field) {
                if (array_key_exists($field, $data)) {
                    $validData[$field] = $data[$field];
                }
            }
            
            error_log('Datos validados: ' . json_encode($validData));
            
            if (empty($validData)) {
                throw new \Exception('No hay campos válidos para actualizar');
            }
            
            $validData['updated_at'] = date('Y-m-d H:i:s');
            
            // Verificar que el libro existe
            $book = $this->select(['id'])
                ->where([['id', $id]])
                ->get();

            if (empty($book)) {
                error_log('Libro no encontrado con ID: ' . $id);
                throw new \Exception('Libro no encontrado');
            }

            // Verificar ISBN duplicado si se está actualizando
            if (isset($validData['isbn']) && !empty($validData['isbn'])) {
                $existingIsbn = $this->select(['id'])
                    ->where([['isbn', $validData['isbn']], ['id', '!=', $id], ['status', 'active']])
                    ->get();
                if ($existingIsbn) {
                    throw new \Exception("El ISBN ya está registrado en otro libro");
                }
            }

            // Preparar la consulta de actualización
            $fields = [];
            $values = [];
            foreach ($validData as $field => $value) {
                $fields[] = $field . " = ?";
                $values[] = $value;
            }
            $values[] = $id; // Para el WHERE

            $sql = "UPDATE " . $this->tableName . " SET " . implode(", ", $fields) . " WHERE id = ?";
            error_log('SQL de actualización: ' . $sql);
            error_log('Valores: ' . json_encode($values));

            $stmt = $this->table->prepare($sql);
            if (!$stmt) {
                throw new \Exception("Error preparando la consulta: " . $this->table->error);
            }

            $types = str_repeat('s', count($values) - 1) . 'i'; // Todos string excepto el ID que es int
            $stmt->bind_param($types, ...$values);
            
            if (!$stmt->execute()) {
                throw new \Exception("Error ejecutando la consulta: " . $stmt->error);
            }
            
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            
            error_log('Filas afectadas: ' . $affectedRows);
            return true;
            
        } catch (\Exception $e) {
            error_log('Error en updateBook: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteBook($id) {
        try {
            error_log("Intentando eliminar libro con ID: {$id}");
            
            // Verificar que el libro existe
            $book = $this->select(['id', 'status'])
                ->where([['id', $id]])
                ->get();

            if (!$book) {
                throw new \Exception('Libro no encontrado');
            }

            // Verificar si el libro está prestado actualmente
            $borrowed = $this->query("SELECT COUNT(*) as count FROM borrowed_books 
                                    WHERE book_id = ? AND status = 'borrowed'", [$id]);
            
            if ($borrowed && $borrowed[0]['count'] > 0) {
                throw new \Exception('No se puede eliminar un libro que está actualmente prestado');
            }

            // Soft delete: actualizar status a 'inactive'
            $sql = "UPDATE " . $this->tableName . " SET status = 'inactive', updated_at = ? WHERE id = ?";
            $stmt = $this->table->prepare($sql);
            
            if (!$stmt) {
                throw new \Exception("Error preparando la consulta: " . $this->table->error);
            }

            $updatedAt = date('Y-m-d H:i:s');
            $stmt->bind_param('si', $updatedAt, $id);
            
            if (!$stmt->execute()) {
                throw new \Exception("Error ejecutando la consulta: " . $stmt->error);
            }
            
            $affectedRows = $stmt->affected_rows;
            $stmt->close();

            if ($affectedRows === 0) {
                throw new \Exception('No se pudo actualizar el libro');
            }

            error_log("Libro {$id} eliminado con éxito (soft delete)");
            return true;

        } catch (\Exception $e) {
            error_log("Error en deleteBook: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAvailableBooks($limit = null) {
        $query = $this->select([
            'id', 'title', 'author', 'genre', 
            'publication_year', 'isbn', 'cover_image', 'stock'
        ])
        ->where([['status', 'active'], ['stock', '>', 0]]);

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->orderBy([['title', 'asc']])->get();
    }

    public function updateStock($bookId, $increment = -1) {
        try {
            // Obtener stock actual
            $book = $this->select(['stock'])
                ->where([['id', $bookId]])
                ->get();

            if (!$book) {
                throw new \Exception('Libro no encontrado');
            }

            $currentStock = $book[0]['stock'];
            $newStock = $currentStock + $increment;

            if ($newStock < 0) {
                throw new \Exception('No hay suficiente stock disponible');
            }

            // Actualizar stock
            $sql = "UPDATE " . $this->tableName . " SET stock = ?, updated_at = ? WHERE id = ?";
            $stmt = $this->table->prepare($sql);
            
            if (!$stmt) {
                throw new \Exception("Error preparando la consulta: " . $this->table->error);
            }

            $updatedAt = date('Y-m-d H:i:s');
            $stmt->bind_param('isi', $newStock, $updatedAt, $bookId);
            
            if (!$stmt->execute()) {
                throw new \Exception("Error ejecutando la consulta: " . $stmt->error);
            }
            
            $stmt->close();
            return $newStock;

        } catch (\Exception $e) {
            error_log("Error en updateStock: " . $e->getMessage());
            throw $e;
        }
    }
}