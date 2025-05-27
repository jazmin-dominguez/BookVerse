<?php

    namespace app\classes;

    class DB { 
        protected $db_host;
        protected $db_name;
        protected $db_user;
        protected $db_passwd;

        public $conex; // Conexión a la base de datos (para compatibilidad)
        protected $table; // Conexión a la base de datos
        protected $tableName; // Nombre de la tabla actual
        protected $fillable = []; // Campos que se pueden insertar/actualizar
        protected $values = []; // Valores para insertar/actualizar

        // Atributos de control para las consultas
        protected $select = " * ";
        protected $count = "";
        protected $joins = "";
        protected $where = " 1 ";
        protected $orderBy = "";
        protected $limit = "";
        protected $params = []; 
        protected $paramTypes = '';  

        //Constructor de la clase DB
        public function __construct($dbh = DB_HOST, $dbn = DB_NAME, $dbu = DB_USER, $dbp = DB_PASS) {
            $this->db_host = $dbh;
            $this->db_name = $dbn;
            $this->db_user = $dbu;
            $this->db_passwd = $dbp;
            $this->connect();
        }

        //Establece la conexión con la base de datos 
        public function connect() {
            try {
                $this->table = new \mysqli($this->db_host, $this->db_user, $this->db_passwd, $this->db_name);
                if ($this->table->connect_errno) {
                    throw new \Exception("Error de conexión: " . $this->table->connect_error);
                }
                $this->table->set_charset("utf8mb4");
                $this->conex = $this->table; 

                // Auto-detectar nombre de tabla del modelo si no está definido
                if (empty($this->tableName)) {
                    $className = get_class($this);
                    $this->tableName = strtolower(substr($className, strrpos($className, '\\') + 1));
                }
                
                return $this->table;
            } catch (\Exception $e) {
                die("Error de conexión: " . $e->getMessage());
            }
        }

        //Establece la tabla a utilizar 
        public function setTable($tableName) {
            $this->tableName = $tableName;
            return $this;
        }

        //Establece los campos permitidos para insertar/actualizar 
        public function setFillable(array $fillable) {
            $this->fillable = $fillable;
            return $this;
        }

        //Establece los valores para insertar/actualizar 
        public function setValues(array $values) {
            $this->values = $values;
            return $this;
        }

        //Retorna todos los registros 
        public function all() {
            return $this;
        }

        //Selecciona campos específicos 
        public function select($columns = []) {
            if (count($columns) > 0) {
                $this->select = implode(",", $columns);
            }
            return $this;
        }

        //Cuenta registros 
        public function count($column = "*") {
            $this->count = ", COUNT(" . $column . ") as total";
            return $this;
        }

        //Realiza un JOIN con otra tabla 
        public function join($table, $on, $type = 'INNER') {
            if ($table && $on) {
                $this->joins .= " " . $type . " JOIN " . $table . " ON " . $on;
            }
            return $this;
        }

        //Establece las condiciones WHERE 
        public function where($conditions = []) {
            $this->where = "";
            $this->params = [];
            $this->paramTypes = '';

            if (count($conditions) > 0) {
                foreach ($conditions as $condition) {
                    $field = $condition[0];
                    $operator = isset($condition[1]) ? $condition[1] : '=';
                    $value = isset($condition[2]) ? $condition[2] : $condition[1];

                    // Sanitizar el operador
                    $operator = in_array($operator, ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'IN']) ? $operator : '=';

                    if ($operator === 'IN' && is_array($value)) {
                        $placeholders = str_repeat('?,', count($value) - 1) . '?';
                        $this->where .= $field . " IN (" . $placeholders . ") AND ";
                        foreach ($value as $val) {
                            $this->params[] = $val;
                            $this->paramTypes .= $this->getParamType($val);
                        }
                    } else {
                        $this->where .= $field . " " . $operator . " ? AND ";
                        $this->params[] = $value;
                        $this->paramTypes .= $this->getParamType($value);
                    }
                }
            }
            $this->where .= ' 1';
            $this->where = '(' . $this->where . ')';
            return $this;
        }

        //Establece el orden de los resultados 
        public function orderBy($orders = []) {
            $this->orderBy = "";
            if (count($orders) > 0) {
                $orderClauses = [];
                foreach ($orders as $order) {
                    $field = $order[0];
                    $direction = isset($order[1]) && strtoupper($order[1]) === 'DESC' ? 'DESC' : 'ASC';
                    $orderClauses[] = $field . ' ' . $direction;
                }
                $this->orderBy = ' ORDER BY ' . implode(',', $orderClauses);
            }
            return $this;
        }

        //Establece el límite de resultados 
        public function limit($limit = "") {
            $this->limit = $limit ? ' LIMIT ' . (int)$limit : '';
            return $this;
        }

        //Ejecuta la consulta y retorna los resultados 
        public function get() {
            try { 
                $sql = "SELECT " . ($this->select ?: '*');
                if ($this->count) {
                    $sql .= ", " . $this->count;
                }
                $sql .= " FROM " . $this->tableName;
                 
                if ($this->joins) {
                    $sql .= $this->joins;
                }
                 
                if ($this->where) {
                    $sql .= " WHERE " . $this->where;
                }
                 
                if ($this->orderBy) {
                    $sql .= $this->orderBy;
                }
                 
                if ($this->limit) {
                    $sql .= $this->limit;
                }
 
                error_log("SQL Query: " . $sql);
                if ($this->params) {
                    error_log("Params: " . print_r($this->params, true));
                    error_log("Types: " . $this->paramTypes);
                }

                // Preparar y ejecutar la consulta
                if (!($stmt = $this->table->prepare($sql))) {
                    throw new \Exception("Error preparando la consulta: " . $this->table->error);
                }
                
                if ($this->params && $this->paramTypes) {
                    if (!$stmt->bind_param($this->paramTypes, ...$this->params)) {
                        throw new \Exception("Error vinculando parámetros: " . $stmt->error);
                    }
                }
                
                if (!$stmt->execute()) {
                    throw new \Exception("Error ejecutando la consulta: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                $data = [];
                
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
                
                $stmt->close();
                return $data;
            } catch (\Exception $e) {
                error_log("Error en la consulta SQL: " . $e->getMessage() . "\nSQL: " . $sql);
                die("Error en la consulta: " . $e->getMessage());
            }
        }

        //Inserta un nuevo registro 
        public function create() {
            try {
                if (empty($this->fillable) || empty($this->values)) {
                    throw new \Exception("Faltan campos o valores para insertar");
                }

                $sql = "INSERT INTO " . $this->tableName .
                       " (" . implode(",", $this->fillable) . ") VALUES (" .
                       rtrim(str_repeat("?,", count($this->fillable)), ",") . ")";

                $stmt = $this->table->prepare($sql);
                $types = str_repeat('s', count($this->values));
                $stmt->bind_param($types, ...$this->values);
                $stmt->execute();
                $id = $stmt->insert_id;
                $stmt->close();
                return $id;
            } catch (\Exception $e) {
                die("Error al insertar: " . $e->getMessage());
            }
        }

        //Actualiza registros 
        public function update($data) {
            try {
                if (empty($data) || empty($this->where)) {
                    throw new \Exception("Faltan datos para actualizar o condición where");
                }

                $sets = [];
                $values = [];
                $types = '';

                foreach ($data as $field => $value) {
                    $sets[] = "`$field` = ?";
                    $values[] = $value;
                    $types .= $this->getParamType($value);
                }

                // Agregamos los parámetros del where
                $values = array_merge($values, $this->params);
                $types .= $this->paramTypes;

                $sql = "UPDATE " . $this->tableName .
                       " SET " . implode(", ", $sets) .
                       " WHERE " . $this->where;

                $stmt = $this->table->prepare($sql);
                $stmt->bind_param($types, ...$values);
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();
                return $affected;
            } catch (\Exception $e) {
                die("Error al actualizar: " . $e->getMessage());
            }
        }

        //Elimina registros 
        public function delete() {
            try {
                if (empty($this->where)) {
                    throw new \Exception("Falta condición where para eliminar");
                }

                $sql = "DELETE FROM " . $this->tableName . " WHERE " . $this->where;
                
                $stmt = $this->table->prepare($sql);
                if ($this->params) {
                    $stmt->bind_param($this->paramTypes, ...$this->params);
                }
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();
                return $affected;
            } catch (\Exception $e) {
                die("Error al eliminar: " . $e->getMessage());
            }
        }

        //Determina el tipo de parámetro para bind_param 
        private function getParamType($value) {
            if (is_int($value)) return 'i';
            if (is_double($value)) return 'd';
            if (is_string($value)) return 's';
            return 'b';
        }

        //Ejecuta una consulta SQL directa
        public function query($sql) {
            try {
                $result = $this->table->query($sql);
                if (!$result) {
                    throw new \Exception("Error ejecutando la consulta: " . $this->table->error);
                }
                
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
                
                $result->free();
                return $data;
            } catch (\Exception $e) {
                error_log("Error en la consulta SQL: " . $e->getMessage() . "\nSQL: " . $sql);
                die("Error en la consulta: " . $e->getMessage());
            }
        }

        //Cierra la conexión 
        public function __destruct() {
            if ($this->table) {
                $this->table->close();
            }
        }
    }