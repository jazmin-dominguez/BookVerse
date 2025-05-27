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
            'imagen'
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
                ]) ->where([['status', 'inactive']]);

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
            return $this->select(['id', 'full_name', 'email', 'created_at'])
                        ->orderBy([['created_at', 'desc']])
                        ->limit($limit)
                        ->get();
        }

        public function getTotalUsers() {
            $result = $this->query("SELECT COUNT(*) as total FROM users");
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
                                     'date_format(bb.return_date,"%d/%m/%Y") as return_date'])
                           -> join('borrowed_books bb', 'bb.user_id = users.id')
                           -> join('books b', 'b.id = bb.book_id')
                           -> where([['users.id', $userId]])
                           -> orderBy([['bb.borrow_date', 'desc']])
                           -> get();
            return $result;
        }

        public function insert($data) {
            try {
                // Validar campos requeridos
                $requiredFields = ['username', 'full_name', 'email', 'password', 'role'];
                foreach ($requiredFields as $field) {
                    if (empty($data[$field])) {
                        throw new \Exception("El campo {$field} es requerido");
                    }
                }

                // Verificar si el email ya existe
                $existingEmail = $this->select(['id'])
                    ->where([['email', $data['email']]])
                    ->get();
                if ($existingEmail) {
                    throw new \Exception("El correo electrónico ya está registrado");
                }

                // Verificar si el username ya existe
                $existingUsername = $this->select(['id'])
                    ->where([['username', $data['username']]])
                    ->get();
                if ($existingUsername) {
                    throw new \Exception("El nombre de usuario ya está en uso");
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
                $types = '';
                
                foreach ($this->fillable as $field) {
                    if (isset($data[$field])) {
                        $fields[] = $field;
                        $values[] = $data[$field];
                        $types .= 's';
                    }
                }
                
                if (empty($fields)) {
                    throw new \Exception("No hay campos válidos para insertar");
                }

                $sql = "INSERT INTO " . $this->tableName .
                       " (" . implode(",", $fields) . ") VALUES (" .
                       rtrim(str_repeat("?,", count($fields)), ",") . ")";

                $stmt = $this->table->prepare($sql);
                $stmt->bind_param($types, ...$values);
                
                try {
                    $stmt->execute();
                    $id = $stmt->insert_id;
                    $stmt->close();
                    return $id;
                } catch (\mysqli_sql_exception $e) {
                    if ($e->getCode() == 1062) { // Error de duplicado
                        if (strpos($e->getMessage(), 'email') !== false) {
                            throw new \Exception("El correo electrónico ya está registrado");
                        } elseif (strpos($e->getMessage(), 'username') !== false) {
                            throw new \Exception("El nombre de usuario ya está en uso");
                        }
                    }
                    throw $e;
                }
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
                    if (isset($data[$field])) {
                        $validData[$field] = $data[$field];
                    }
                }
                error_log('Campos permitidos: ' . json_encode($this->fillable));
                error_log('Datos validados: ' . json_encode($validData));

                // Si hay contraseña, la hasheamos
                if (!empty($validData['password'])) {
                    $validData['password'] = password_hash($validData['password'], PASSWORD_DEFAULT);
                    error_log('Contraseña hasheada');
                } else {
                    unset($validData['password']);
                    error_log('Sin cambios en la contraseña');
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

                // Actualizar el usuario
                error_log('Ejecutando update con datos: ' . json_encode($validData));
                $result = $this->where([['id', $id]])->update($validData);
                error_log('Resultado del update: ' . var_export($result, true));
                
                // Si result es 0, puede significar que los datos son iguales
                if ($result === false) {
                    error_log('Error al actualizar el usuario');
                    throw new \Exception('Error al actualizar el usuario');
                }
                
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

                // Soft delete: actualizar status a 'inactive'
                $result = $this->where([['id', $id]])
                    ->update([
                        'status' => 'active',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                if ($result === false) {
                    throw new \Exception('Error al actualizar el estado del usuario');
                }

                error_log("Usuario {$id} eliminado con éxito (soft delete)");
                return true;

            } catch (\Exception $e) {
                error_log("Error en deleteUser: " . $e->getMessage());
                throw $e;
            }
        }
    }
