<?php
    namespace app\models;

    class posts extends Model {

        protected $table;
        protected $fillable = [
            'userId',
            'title',
            'body',
            'active',
            'image'
        ];

        public function __construct(){
            parent::__construct();
            $this->table = $this->connect();
        }
        public $values = [];

        public function getAllPosts($limit = 10){
            $result = $this -> select( ['a.id','a.title','a.body','date_format(a.created_at,"%d/%m/%Y") as fecha','b.name']) 
                            -> join( "user b","a.userId=b.id")
                            -> where( [['a.active',1]] )
                            -> orderBy( [['a.created_at','desc']] )
                            -> limit( $limit )
                            -> get();
            return $result;

            /* return $this->all()->get(); */
        }

        public function getLastPost(){
            $result = $this -> select( ['a.id','a.title','a.body','a.image','date_format(a.created_at,"%d/%m/%Y") as fecha','b.name']) 
                            -> join( "user b","a.userId=b.id")
                            -> where( [['a.active',1]] )
                            -> orderBy( [['a.created_at','desc']] )
                            -> limit( 1 )
                            -> get();
            return $result;          

        }
        public function openPost( $pid ){
            $result = $this -> select( ['a.id','a.title','a.body','a.image','date_format(a.created_at,"%d/%m/%Y") as fecha','b.name']) 
                            -> join( "user b","a.userId=b.id")
                            -> where( [['a.active',1],['a.id',$pid]] )
                            -> get();
            return $result;          

        }
    }