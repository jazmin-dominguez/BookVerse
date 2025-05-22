<?php

    namespace app\controllers;

    class ErrorController extends Controller {

        public function error404(){
            echo "Error 404, no encontrado";
            die;
        }
        public function errorMNF(){
            echo "El método no se encontró";
            die;
        }
    }