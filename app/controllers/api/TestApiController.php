<?php

use devpirates\MVC\TemplateMVCApp;

class TestApiController extends \devpirates\MVC\Base\ApiController {
    public function __construct(TemplateMVCApp $app) {
        parent::__construct($app);
    }

    public function Index() {
        return $this->ok(json_decode('{ "testProp1": "testVal1", "testProp2": "testVal2",  "testProp3": "testVal3" }'));
    }
}
?>