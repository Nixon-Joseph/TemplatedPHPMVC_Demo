<?php

use devpirates\MVC\TemplateMVCApp;

class PostRepo extends devpirates\MVC\Base\Repo {
    public function __construct(TemplateMVCApp $app) {
        parent::__construct($app, "Post");
    }

    public function GetRecentPosts(): ?array {
        return $this->_getAll(6, "Date", false);
    }

    public function GetPost($postId): ?Post {
        return $this->_getById($postId);
    }
}

class Post {
    public $Uid;
    public $Name;
    public $Title;
    public $Date;
    public $Author;
    public $Snippet;
    public $Contents;
    public $Category;
    public $Image;
    public $Featured;
}
?>