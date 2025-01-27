<?php

use devpirates\MVC\Authentication;
use devpirates\MVC\Base\ControllerResponse;
use devpirates\MVC\GUIDHelper;
use devpirates\MVC\ResponseInfo;
use devpirates\MVC\TemplateMVCApp;

class AccountApiController extends \devpirates\MVC\Base\ApiController {
    use AuthorizedApiController;

    /**
     * @var UserHelper
     */
    private $helper;

    public function __construct(TemplateMVCApp $app) {
        parent::__construct($app);
        $this->_init();
        $this->helper = new UserHelper($app);
    }

    /**
     * Returns basic user information for
     *
     * @return void
     */
    public function Index(): ControllerResponse {
        if ($this->isLoggedIn() === true) {
            return $this->ok(array("IsLoggedIn" => true, "Username" => $this->getCurrentUsername()));
        } else {
            return $this->ok(array("IsLoggedIn" => false));
        }
    }

    /**
     * First step to 2 factor setup for an account
     * Returns data required to set up 2 factor via the authenticator app
     *
     * @return void
     */
    public function Info(): ControllerResponse {
        return $this->authorize(function() {
            $user = $this->getCurrentUser();
            return $this->ok(array(
                "Username" => $user->Username,
                "Email" => $user->Email,
                "Require2FA" => $user->Require2FA
            ));
        });
    }

    /**
     * Checks the username and password against what's in the db to decide if the user should be able to log in
     *
     * @return void
     */
    public function Login(): ControllerResponse {
        $errorMessage = "Username and password are required.";
        if (isset(REQUEST_POST['payload']) && strlen(REQUEST_POST['payload'])) {
            $errorMessage = "Username or password provided could not be validated.";
            $credSplit = explode("|::|", base64_decode(REQUEST_POST['payload']), 2);
            if ($this->helper->UserExists($credSplit[0]) === true) {
                $user = $this->helper->GetUserByUsername($credSplit[0]);
                if (isset($user)) {
                    if (Authentication::CheckPassword($credSplit[1], $user->PasswordSalt, $user->PasswordHash) === true) {
                        if ($user->Require2FA === true) {
                            $twoFactorRequest = GUIDHelper::GUIDv4();
                            $_SESSION[Constants::TWO_FACTOR_SESSION] = $twoFactorRequest;
                            $_SESSION[$twoFactorRequest] = base64_encode($user->Username);
                            return $this->ok(ResponseInfo::Success("2FA", $twoFactorRequest));
                        } else {
                            $this->setCurrentUser($user);
                            return $this->ok(ResponseInfo::Success());
                        }
                    }
                }
            }
        }
        return $this->ok(ResponseInfo::Error($errorMessage));
    }

    /**
     * Second step in login via 2FA, compares code provided with the secret stored on the user
     *
     * @return ControllerResponse
     */
    public function Login2FA(): ControllerResponse {
        return $this->throttle("2FA", 5, 1, function() {
            if (isset(REQUEST_POST['2fa']) && isset($_SESSION[Constants::TWO_FACTOR_SESSION]) && REQUEST_POST['2fa'] === $_SESSION[Constants::TWO_FACTOR_SESSION]) {
                if (isset($_SESSION[$_SESSION[Constants::TWO_FACTOR_SESSION]]) && strlen($_SESSION[$_SESSION[Constants::TWO_FACTOR_SESSION]])) {
                    $username = base64_decode($_SESSION[$_SESSION[Constants::TWO_FACTOR_SESSION]]);
                    unset($_SESSION[$_SESSION[Constants::TWO_FACTOR_SESSION]]);
                    unset($_SESSION[Constants::TWO_FACTOR_SESSION]);
                    if (isset(REQUEST_POST['code']) && strlen(REQUEST_POST['code'])) {
                        $user = $this->helper->GetUserByUsername($username);
                        if (isset($user) === true) {
                            $g = new Sonata\GoogleAuthenticator\GoogleAuthenticator();
                            if ($g->checkCode($user->Secret2FA, REQUEST_POST['code']) === true) {
                                $_SESSION['authenticated'] = base64_encode($user->Username . '||' . $user->Uid);
                                return $this->ok(ResponseInfo::Success());
                            } else {
                                $twoFactorRequest = GUIDHelper::GUIDv4();
                                $_SESSION[Constants::TWO_FACTOR_SESSION] = $twoFactorRequest;
                                $_SESSION[$twoFactorRequest] = base64_encode($user->Username);
                                $response = new ResponseInfo(false, $twoFactorRequest, "Code could not be verified.");
                                return $this->ok($response);
                            }
                        }
                    }
                }
            }
            return $this->ok(ResponseInfo::Error("Failed to authenticate provided code."));
        });
    }

    /**
     * First step to 2 factor setup for an account
     * Returns data required to set up 2 factor via the authenticator app
     *
     * @return ControllerResponse
     */
    public function Get2FactorSetup(): ControllerResponse {
        return $this->authorize(function() {
            $username = $this->getCurrentUsername();
            $g = new Sonata\GoogleAuthenticator\GoogleAuthenticator();
            $secret = $g->generateSecret();
            $_SESSION["2FASetupSecret"] = $secret;
            return $this->ok(ResponseInfo::Success(null, Sonata\GoogleAuthenticator\GoogleQrUrl::generate($username, $secret, CONSTANTS::SITE_NAME_CLEAN)));
        });
    }

    /**
     * Final step to setting up 2 factor for a user
     * Takes the $code provided from the authenticator app to authenticate the setup
     *
     * @param string $code
     * @return ControllerResponse
     */
    public function Enable2FactorSetup(string $code): ControllerResponse {
        return $this->authorize(function($c) {
            if (isset($_SESSION["2FASetupSecret"])) {
                $secret = $_SESSION["2FASetupSecret"];
                $g = new Sonata\GoogleAuthenticator\GoogleAuthenticator();
                if ($g->checkCode($secret, $c)) {
                    $user = $this->getCurrentUser();
                    $user->Secret2FA = $secret;
                    $user->Require2FA = true;
                    $response = $this->helper->UpsertUser($user);
                    if ($response->Success === true) {
                        unset($_SESSION["2FASetupSecret"]);
                        return $this->ok(ResponseInfo::Success());
                    } else {
                        return $this->ok(ResponseInfo::Error("Unable to update your user account. Please try again, or contact us for assistance."));
                    }
                } else {
                    return $this->ok(ResponseInfo::Error("Invalid code, please try again."));
                }
            } else {
                return $this->ok(ResponseInfo::Error("Unable to verify 2 Factor request. Please start over."));
            }
        }, $code);
    }

    /**
     * Disables 2 factor for the current account if the password provided matches
     *
     * @return ControllerResponse
     */
    public function Disable2Factor(): ControllerResponse {
        return $this->authorize(function() {
            if (isset(REQUEST_POST["password"])) {
                $password = REQUEST_POST["password"];
                $user = $this->getCurrentUser();
                if (Authentication::CheckPassword($password, $user->PasswordSalt, $user->PasswordHash)) {
                    $user->Secret2FA = null;
                    $user->Require2FA = false;
                    $response = $this->helper->UpsertUser($user);
                    if ($response->Success === true) {
                        return $this->ok(ResponseInfo::Success());
                    } else {
                        return $this->ok(ResponseInfo::Error("Unable to update your user account. Please try again, or contact us for assistance."));
                    }
                } else {
                    return $this->ok(ResponseInfo::Error("Invalid password, please try again."));
                }
            } else {
                return $this->ok(ResponseInfo::Error("Password is required."));
            }
        });
    }

    /**
     * Verifies if the provided password is correct for the currently logged in user.
     *
     * @return ControllerResponse
     */
    public function VerifyPassword(): ControllerResponse {
        return $this->authorize(function() {
            if (isset(REQUEST_POST["password"])) {
                $password = REQUEST_POST["password"];
                $user = $this->getCurrentUser();
                if (Authentication::CheckPassword($password, $user->PasswordSalt, $user->PasswordHash)) {
                    return $this->ok(ResponseInfo::Success());
                } else {
                    return $this->ok(ResponseInfo::Error("Invalid password, please try again."));
                }
            } else {
                return $this->ok(ResponseInfo::Error("Password is required."));
            }
        });
    }

    /**
     * Sends the forgot password email to the provided email address
     *
     * @return ControllerResponse
     */
    public function BeginResetPassword(): ControllerResponse {
        return $this->throttle("BeginResetPassword", 1, 1, function() {
            $this->helper->BeginResetPassword();
            return $this->ok(ResponseInfo::Success());
        });
    }

    /**
     * Finalizes the account password reset
     *
     * @return ControllerResponse
     */
    public function ResetPassword(): ControllerResponse {
        return $this->throttle("ResetPassword", 2, 5, function() {
            return $this->ok($this->helper->ResetPassword());
        });
    }

    /**
     * Instantiates intitial user account, and sends registration email to new user
     *
     * @return ControllerResponse
     */
    public function BeginRegister(): ControllerResponse {
        return $this->throttle("BeginRegister", 2, 1, function() {
            return $this->ok($this->helper->BeginRegister());
        });
    }

    /**
     * Final registration step
     *
     * @return ControllerResponse
     */
    public function FinishRegister(): ControllerResponse {
        return $this->throttle("FinishRegister", 5, 1, function() {
            return $this->ok($this->helper->FinishRegister());
        });
    }
}
?>