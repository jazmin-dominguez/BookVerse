<?php
namespace app\models;

require_once __DIR__ . '/Model.php';

class User extends Model {
    protected $tableName = 'users';
    protected $fillable = [
        'username',
        'full_name',
        'email',
        'password',
        'role',
        'status',
        'imagen',
        'created_at',
        'updated_at'
    ];

    public function __construct(){
        parent::__construct();
    }

    public $values = [];

    public function getAllUsers($limit = null){
        try {
            error_log('Iniciando getAllUsers en User model');
            
            $query = $this->select([
                'id', 'username', 'full_name', 'email', 
                'role', 'status', 'imagen', 'created_at', 'updated_at'
            ]) ->where([['status', 'active']]);

            // Ordenar por nombre completo
            $query->orderBy([['full_name', 'asc']]);

            // Aplicar límite solo si se especifica
            if ($limit !== null) {
                $query->limit($limit);
            }

            $result = $query->get();
            
            error_log('Usuarios encontrados: ' . ($result ? count($result) : 0));
            if (!$result) {
                error_log('No se encontraron usuarios o hubo un error en la consulta');
            }

            return $result;

        } catch (\Exception $e) {
            error_log('Error en getAllUsers: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getRecentUsers($limit = 3) {
        return $this->select(['id', 'full_name', 'email', 'role', 'created_at'])
                    ->where([['status', 'active']])
                    ->orderBy([['created_at', 'desc']])
                    ->limit($limit)
                    ->get();
    }

    public function getTotalUsers() {
        $result = $this->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
        return $result[0]['total'] ?? 0;
    }

    public function getUserById($userId){
        $result = $this -> select(['id', 'username', 'full_name', 'email', 'role', 'status', 'imagen',
                                 'created_at', 'updated_at'])
                       -> where([['id', $userId]])
                       -> get();
        return $result;
    }

    public function getUserBorrowedBooks($userId){
        $result = $this -> select(['b.id', 'b.title', 'b.author', 
                                 'date_format(bb.borrow_date,"%d/%m/%Y") as borrow_date',
                                 'date_format(bb.return_date,"%d/%m/%Y") as return_date',
                                 'bb.status as borrow_status'])
                       -> join('borrowed_books bb', 'bb.user_id = users.id')
                       -> join('books b', 'b.id = bb.book_id')
                       -> where([['users.id', $userId]])
                       -> orderBy([['bb.borrow_date', 'desc']])
                       -> get();
        return $result;
    }

    public function searchUsers($query){
        $result = $this -> select(['id', 'username', 'full_name', 'email', 'role'])
                       -> where([['full_name', "LIKE", "%$query%", "OR"],
                               ['username', "LIKE", "%$query%", "OR"],
                               ['email', "LIKE", "%$query%"]])
                       -> where([['status', 'active']])
                       -> orderBy([['full_name', 'asc']])
                       -> get();
        return $result;
    }

    public function insert($data) {
        try {
            error_log('Iniciando insert en User model con datos: ' . json_encode($data));
            
            // Validar campos requeridos
            $requiredFields = ['username', 'full_name', 'email', 'password', 'role'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new \Exception("El campo {$field} es requerido");
                }
            }

            // Verificar si el email ya existe
            if (!empty($data['email'])) {
                $existingEmail = $this->select(['id'])
                    ->where([['email', $data['email']], ['status', 'active']])
                    ->get();
                if ($existingEmail) {
                    throw new \Exception("El correo electrónico ya está registrado");
                }
            }

            // Verificar si el username ya existe
            if (!empty($data['username'])) {
                $existingUsername = $this->select(['id'])
                    ->where([['username', $data['username']], ['status', 'active']])
                    ->get();
                if ($existingUsername) {
                    throw new \Exception("El nombre de usuario ya está en uso");
                }
            }

            // Hashear la contraseña
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Establecer valores por defecto
            $data['status'] = 'active';
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
            
            error_log('Usuario insertado con ID: ' . $id);
            return $id;
            
        } catch (\mysqli_sql_exception $e) {
            throw new \Exception("Error de base de datos: " . $e->getMessage());
        } catch (\Exception $e) {
            error_log("Error al insertar usuario: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateUser($id, $data) {
        try {
            error_log('Iniciando updateUser para ID: ' . $id);
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

            // Si hay contraseña, la hasheamos
            if (isset($validData['password']) && !empty($validData['password'])) {
                $validData['password'] = password_hash($validData['password'], PASSWORD_DEFAULT);
            } else {
                unset($validData['password']);
            }
            
            $validData['updated_at'] = date('Y-m-d H:i:s');
            
            // Verificar que el usuario existe
            $user = $this->select(['id'])
                ->where([['id', $id]])
                ->get();

            if (empty($user)) {
                error_log('Usuario no encontrado con ID: ' . $id);
                throw new \Exception('Usuario no encontrado');
            }

            // Verificar email duplicado si se está actualizando
            if (isset($validData['email']) && !empty($validData['email'])) {
                $existingEmail = $this->select(['id'])
                    ->where([['email', $validData['email']], ['id', '!=', $id], ['status', 'active']])
                    ->get();
                if ($existingEmail) {
                    throw new \Exception("El email ya está registrado en otro usuario");
                }
            }

            // Verificar username duplicado si se está actualizando
            if (isset($validData['username']) && !empty($validData['username'])) {
                $existingUsername = $this->select(['id'])
                    ->where([['username', $validData['username']], ['id', '!=', $id], ['status', 'active']])
                    ->get();
                if ($existingUsername) {
                    throw new \Exception("El username ya está registrado en otro usuario");
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
            error_log('Error en updateUser: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteUser($id) {
        try {
            error_log("Intentando eliminar usuario con ID: {$id}");
            
            // Verificar que el usuario existe
            $user = $this->select(['id', 'status'])
                ->where([['id', $id]])
                ->get();

            if (!$user) {
                throw new \Exception('Usuario no encontrado');
            }

            // Verificar si el usuario tiene libros prestados actualmente
            $borrowed = $this->query("SELECT COUNT(*) as count FROM borrowed_books 
                                    WHERE user_id = ? AND status = 'borrowed'", [$id]);
            
            if ($borrowed && $borrowed[0]['count'] > 0) {
                throw new \Exception('No se puede eliminar un usuario que tiene libros prestados actualmente');
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
                throw new \Exception('No se pudo actualizar el usuario');
            }

            error_log("Usuario {$id} eliminado con éxito (soft delete)");
            return true;

        } catch (\Exception $e) {
            error_log("Error en deleteUser: " . $e->getMessage());
            throw $e;
        }
    }

    public function getActiveUsers($limit = null) {
        $query = $this->select([
            'id', 'username', 'full_name', 'email', 
            'role', 'status', 'imagen'
        ])
        ->where([['status', 'active']]);

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->orderBy([['full_name', 'asc']])->get();
    }

    public function authenticateUser($username, $password) {
        try {
            $user = $this->select(['id', 'username', 'full_name', 'email', 'password', 'role', 'status'])
                ->where([['username', $username], ['status', 'active']])
                ->get();

            if (!$user || !password_verify($password, $user[0]['password'])) {
                return false;
            }

            // No devolver la contraseña
            unset($user[0]['password']);
            return $user[0];

        } catch (\Exception $e) {
            error_log("Error en authenticateUser: " . $e->getMessage());
            return false;
        }
    }
}