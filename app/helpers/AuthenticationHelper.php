<?php

use devpirates\MVC\Base\Helper;
use devpirates\MVC\TemplateMVCApp;

class AuthenticationHelper extends Helper {
    public function __construct(TemplateMVCApp $app) {
        parent::__construct($app);
    }

    public static function IsLoggedIn(): bool {
        return isset($_SESSION['authenticated']) && strlen($_SESSION['authenticated']) > 0;
    }

    public static function GetAuthData(): array {
        if (AuthenticationHelper::IsLoggedIn()) {
            $parts = explode("||", base64_decode($_SESSION['authenticated']));
            return array('id' => intval($parts[1]), 'username' => $parts[0]);
        } else {
            return array();
        }
    }

    public static function SetCurrentUser(?User $user): void {
        if (isset($user) && $user !== null) {
            $_SESSION['authenticated'] = base64_encode($user->Username . '||' . $user->Uid);
        } else {
            unset($_SESSION['authenticated']);
        }
    }
}
?>