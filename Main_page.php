<?php

use Model\Boosterpack_model;
use Model\Post_model;
use Model\User_model;
use Model\Comment_model;

/**
 * Created by PhpStorm.
 * User: mr.incognito
 * Date: 10.11.2018
 * Time: 21:36
 */
class Main_page extends MY_Controller
{

    public function __construct()
    {

        parent::__construct();

        if (is_prod())
        {
            die('In production it will be hard to debug! Run as development environment!');
        }
    }

    public function index()
    {
        $user = User_model::get_user();

        App::get_ci()->load->view('main_page', ['user' => User_model::preparation($user, 'default')]);
    }

    public function get_all_posts()
    {
        $posts =  Post_model::preparation_many(Post_model::get_all(), 'default');
        return $this->response_success(['posts' => $posts]);
    }

    public function get_boosterpacks()
    {
        $posts =  Boosterpack_model::preparation_many(Boosterpack_model::get_all(), 'default');
        return $this->response_success(['boosterpacks' => $posts]);
    }

    public function login()
    {
        // TODO: task 1, аутентификация
        
        $us_email = $_POST['login'];
        $us_passw = $_POST['password'];

        $us_email = 'admin@admin.pl';
        $us_passw = '12345';

        $res = 0;
        $user = User_model::find_user_by_email($us_email);
        if ($user->get_password() == $us_passw)
          {
            $res = 1; 
            App::get_ci()->session->set_userdata('id', $user->get_id());
//            var_dump(App::get_ci()->session);
          }

        return $this->response_success(['user' => $user]);
    }

    public function logout()
    {
       // TODO: task 1, аутентификация
       App::get_ci()->session->unset_userdata('id');
    }

    public function comment()
    {
        // TODO: task 2, комментирование
        $post_id = $_POST["postId"];
        $text    = $_POST["commentText"];
        
        $data = ['user_id' => 1, 'assign_id' => $post_id, 'text' => $text, 'likes' => 0];
        $coment = Comment_model::create($data);
        
    }

    public function like_comment(int $comment_id)
    {
        // TODO: task 3, лайк комментария
        $comment = new Comment_model;
        $comment->set_id($comment_id);
        
        $user = new User_model;
        $user = $comment->get_user();
        
        $like_balance = $user->get_likes_balance();
        if ($like_balance > 0)
          {
            $user->set_likes_balance(--$like_balance);
            $user->remove_money(1);
        
            $likes = $comment->get_likes();
            $comment->set_likes(++$likes);
          }
    }

    public function like_post(int $post_id)
    {
        // TODO: task 3, лайк поста
        $post = new Post_model;
        $post->set_id($post_id);

        $user = new User_model;
        $user = $post->get_user();

        $like_balance = $user->get_likes_balance();
        if ($like_balance > 0)
          {
            $user->set_likes_balance(--$like_balance);

            $likes = $post->get_likes();
            $post->set_likes(++$likes);
          }  
    }

    public function add_money()
    {
        // TODO: task 4, пополнение баланса
        $sum = (float)App::get_ci()->input->post('sum');
        $user_id = 1; //так и не взлетела работа с сессиями
        
        $user = new User_model;
        $user->set_id($user_id);
        $user->get_user();
        $user->add_money($sum);
    }

    public function get_post(int $post_id) {
        // TODO получения поста по id
        
/*
        $post = new Post_model;
        $post->set_id($post_id);
        

        
        var_dump($post);
        return $this->response_success(['post' => (array) $post]);

        
  */      

        $post    = App::get_s()->from('post')->where(['id' => $post_id])->one();
        $user_id = (int) (array) $post['user_id'];
        $post['user'] = App::get_s()->from('user')->where(['id' => $user_id])->one();
        
        
        $coments = App::get_s()->from('comment')->where(['assign_id' => $post_id])->orderBy('time_created', 'ASC')->many();
        
        $index = 0;
        foreach ($coments as $key => $value):
          foreach($value as $k => $v):
            if ($k == 'user_id')
              {$user = App::get_s()->from('user')->where(['id' => $v])->one();}
          endforeach;
          $coments[$index++]["user"] = $user;
        endforeach;
        
        $post['coments'] = $coments;
        return $this->response_success(['post' => $post]);
    }

    public function buy_boosterpack()
    {
        // Check user is authorize
        App::get_ci()->session->set_userdata('id', 1); //так и не взлетела работа с сессиями
        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }

        // TODO: task 5, покупка и открытие бустерпака
        
        $pack = $_POST["id"];
        
        
        if ($pack == 1) {$likes = 5;}
        if ($pack == 2) {$likes = 20;}
        if ($pack == 3) {$likes = 50;}
        
        
        $user = User_model::get_user();
        if ($user->remove_money($likes))
         { $like_balance = $user->get_likes_balance();
           $user->set_likes_balance($like_balance+$likes);
         }
        
        return $this->response_success(['amount' => $likes]);
        
    }





    /**
     * @return object|string|void
     */
    public function get_boosterpack_info(int $bootserpack_info)
    {
        // Check user is authorize

        if ( ! User_model::is_logged())
        {
            return $this->response_error(System\Libraries\Core::RESPONSE_GENERIC_NEED_AUTH);
        }


        //TODO получить содержимое бустерпака
    }
}
