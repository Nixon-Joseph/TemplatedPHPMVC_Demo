<?php

use devpirates\MVC\Base\Controller;
use devpirates\MVC\Base\ControllerResponse;
use devpirates\MVC\TemplateMVCApp;

class AccountController extends Controller {
    use AuthorizedController;

    public function __construct(TemplateMVCApp $app) {
        parent::__construct($app);
        $this->_init();
    }

    public function Index(): ControllerResponse {
        return $this->authorize(function() {
            // $this->scripts[] = "/public/scripts/account/index.js";
            // $user = $this->getCurrentUser();
            // $model = new AccountVM();
            // $model->Username = $user->Username;
            // $model->Enabled2FA = $user->Require2FA;
            // $this->view($model);
            return $this->view(null, "./public/scripts/angular/account/index.html");
        });
    }

    public function Login(): ControllerResponse {
        $this->scripts[] = "/public/scripts/account/login.js";
        return $this->view();
    }

    public function Logout(): ?ControllerResponse {
        $this->clearCurrentUser();
        header('location: /account/login');
        return null;
    }

    public function ResetPassword(): ControllerResponse {
        $this->scripts[] = "/public/scripts/account/resetpassword.js";
        return $this->view(['Code' => REQUEST_GET['code']]);
    }

    public function Create(): ControllerResponse {
        $this->scripts[] = "/public/scripts/account/create.js";
        return $this->view(['Code' => REQUEST_GET['code']]);
    }
}
?>