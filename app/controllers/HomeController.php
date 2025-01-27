<?php

use devpirates\MVC\TemplateMVCApp;

class HomeController extends \devpirates\MVC\Base\Controller {
    public function __construct(TemplateMVCApp $app) {
        parent::__construct($app);
    }

    function Index () {
        return $this->outputCache("home_index", function () {
            $postHelper = new PostHelper($this->app);
            $posts = $postHelper->GetRecentPosts();
            $model = null;
            if (isset($posts) && count($posts) > 0) {
                $model = new HomeVM($posts);
            }
            return $this->view($model, ACTION_NAME, "_layoutHome");
        }, 3600);
    }
}
?>