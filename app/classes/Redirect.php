<?php

    namespace app\classes;

    class Redirect {

        private $location;

        public static function to( $location ){
            $self = new self();
            $self->location = $location;

            //Si las cabeceras ya fueron envíadas
            if( headers_sent() ){
                echo '<script type="text/javascript">';
                echo 'window.location.href="' . URL . $self->location . '"';
                echo '</script>';
                echo '<noscript>';
                echo '<meta http-equiv="refresh" content="0;url=' .  URL . $self->location . '"';
                echo '</noscript>';
                die();
            }
            // Cuando pasamos una url externa a nuewstro sitio
            if( strpos( $self->location, 'http') !== false){
                header('Location: ' . $self->location);
                die();
            }
            header('Location: ' . $self->location);
            die();


        }

    }