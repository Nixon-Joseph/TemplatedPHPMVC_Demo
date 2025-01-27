<?php

use devpirates\MVC\TemplateMVCApp;

class PostsController extends \devpirates\MVC\Base\Controller {
    public function __construct(TemplateMVCApp $app) {
        parent::__construct($app);
    }

    public function index() {
        header("location: /404/notfound/");
    }

    public function Post($postId, $postName) {
        $postHelper = new PostHelper($this->app);
        $post = $postHelper->GetPost($postId);
        return $this->view($post);
    }
}
?>