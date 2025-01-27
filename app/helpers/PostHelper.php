<?php

use devpirates\MVC\Base\Helper;
use devpirates\MVC\TemplateMVCApp;

class PostHelper extends Helper {
    /**
     * @var PostRepo
     */
    private $repo;

    public function __construct(TemplateMVCApp $app) {
        parent::__construct($app);
        $this->repo = new PostRepo($app);
    }

    public function GetRecentPosts(): ?array {
        return $this->repo->GetRecentPosts();
    }

    public function GetPost($postId): ?Post {
        return $this->repo->GetPost($postId);
    }
}
?>