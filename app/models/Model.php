<?php

    namespace app\models;

    require_once __DIR__ . '/../classes/DB.php';
    use app\classes\DB as DB;

    class Model extends \app\classes\DB {
        public function __construct(){
            parent::__construct();
        }
    }