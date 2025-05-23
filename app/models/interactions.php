<?php

    namespace app\models;

    class interactions extends Model {

        protected $table;
        protected $fillable = [
            'postId',
            'userId',
            'tipo',
        ];

        public function __construct(){
            parent::__construct();
            $this->table = $this->connect();
        }
        public $values = [];

        public function toggleLike($pid,$uid){
            $result = $this -> count()
                            -> where([['userId',$uid],['postId',$pid]])
                            ->get();
            if (json_decode( $result )[0]->tt == 0){
                $this -> values = [$pid,$uid,1];
                $this -> create();
            }else{
                $this -> where([['userId',$uid],['postId',$pid]])
                      -> delete();
            }
            return;
        }

    }