<?php

    namespace app\controllers;

    use app\models\posts as posts;
    use app\models\comments as comments;
    use app\models\interactions as inter;

    class PostsController extends Controller {
        public function __construct(){
            parent::__construct();
        }

        public function index(){}

        public function getPosts(){
            $posts = new posts();
            echo $posts->getAllPosts(5);
        }

        public function getLastPost( $params = null){
            $post = new posts();
            $rp = json_decode( $post -> getLastPost() );
            if( count( $rp ) > 0){
                $comments = new comments();
                $rc = $comments -> count('postId')
                                -> where([['postId',$rp[0]->id]])
                                -> get();
                $inter = new inter();
                $ri = $inter -> count('postId')
                             -> where([['postId',$rp[0]->id]])
                             -> get();
                $ri = self::youLiked( $ri );
                echo json_encode( array_merge( $rp, json_decode( $ri ), json_decode( $rc )));
            }

        }
        public function openPost( $params = null){
            $post = new posts();
            $pid = $params[2];
            $rp = json_decode( $post -> openPost( $pid ) );
            if( count( $rp ) > 0){
                $comments = new comments();
                $rc = $comments -> count('postId')
                                -> where([['postId',$pid]])
                                -> get();
                $inter = new inter();
                $ri = $inter -> count('postId')
                            -> where([['postId',$pid]])
                            -> get();
                $ri = self::youLiked( $ri );
                echo json_encode( array_merge( $rp, json_decode( $ri ), json_decode( $rc )));
            }

        }

        /* Interacciones */
        private static function youLiked( $ri ){
            session_start();
            if ( isset($_SESSION['sv']) && $_SESSION['sv'] == true ){
                $ri = json_decode( $ri );
                $inter = new inter();
                $ri[0]->liked = json_decode($inter->count()
                                        ->where([['postId',$ri[0]->id],['userId',$_SESSION['id']]])
                                        ->get())[0]->tt > 0 ? true : false;
                $ri = json_encode( $ri );
            }
            session_write_close();
            return $ri;
        }

        public function toggleLike($params){
            $inter =  new inter();
            list(,,$pid,$uid) = $params;
            $inter->toggleLike($pid,$uid);
            $ri = $inter -> count('postId')-> where([['postId',$pid]])-> get();
            $ri = self::youLiked( $ri );
            echo $ri;
        }


        /* Comentarios */
        public function getComments( $params = null){
            $comments = new comments();
            $pid = $params[2];
            echo $comments -> getComments( $pid );
        }

        public function saveComment( $params = null ){
            $comments = new comments();
            list(,,$pid,$uid) = $params;
            $comment = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
            $comments -> values = [ $pid, $uid, $comment['comment'], 1];
            $comments -> create();
            $rc = $comments  -> count('postId') -> where( [['postId',$pid]]) -> get();
            echo $rc;

        }


    }