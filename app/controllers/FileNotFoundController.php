<?php

class FileNotFoundController extends \devpirates\MVC\Base\Controller {
    public function __construct(\devpirates\MVC\TemplateMVCApp $app) {
        parent::__construct($app);
    }

    function Index() {
        return $this->view();
    }
}

?>