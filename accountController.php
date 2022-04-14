<?php

/**
 * Fernico - Ridiculously lite PHP framework
 *
 * @author Areeb Majeed, Volcrado Holdings
 * @package Fernico
 * @copyright 2017 - Volcrado Holdings Limited
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://volcrado.com/
 *
 */

if (!defined('FERNICO')) {
    fernico_destroy();
}

class accountController extends AstridController {

    public $auth;

    public function __construct() {
        require_once(FERNICO_PATH . "/models/Bootstrapper.php");
        parent::__construct();
        $this->auth = new Authentication();
    }

    public function login($message = "") {

        $opt = array(
            'userLoggedIn' => $this->auth->UserLoggedIn(),
            'captchaCode' => $this->auth->vomitRecaptcha(),
            'pageName' => "Login"
        );

        if ($message != "") {
            $opt['responseMessage'] = "You need to be logged in to view that page.";
        }

        if (Request::POST('login')) {

            $resp = Account::loginHandler($this->auth);

            if ($resp === true) {

                header("Location: " . fernico_getAbsURL() . "page/dashboard");
                fernico_destroy();

            } else {

                $opt['responseMessage'] = $resp;

            }

        }

        if ($opt['userLoggedIn'] == true) {
            header("Location: " . fernico_getAbsURL());
            fernico_destroy();
        } else {
            $this->renderTemplate('Login.tpl', $opt);
        }

    }

    public function register() {

        $opt = array(
            'userLoggedIn' => $this->auth->UserLoggedIn(),
            'captchaCode' => $this->auth->vomitRecaptcha(),
            'pageName' => 'Register'
        );

        if (Request::POST('register')) {

            $resp = Account::registerHandler($this->auth);
            $opt['responseMessage'] = $resp;

        }

        if ($opt['userLoggedIn'] == true) {
            header("Location: " . fernico_getAbsURL());
            fernico_destroy();
        } else {
            $this->renderTemplate('Register.tpl', $opt);
        }

    }

    public function reset__password() {

        $opt = array(
            'userLoggedIn' => $this->auth->UserLoggedIn(),
            'captchaCode' => $this->auth->vomitRecaptcha(),
            'pageName' => 'Reset Password'
        );

        if (Request::POST('reset_password')) {

            $resp = Account::resetPasswordHandler($this->auth);
            $opt['responseMessage'] = $resp;

        }

        $this->renderTemplate('Reset-Password.tpl', $opt);

    }

    public function resend__email() {

        $opt = array(
            'userLoggedIn' => $this->auth->UserLoggedIn(),
            'captchaCode' => $this->auth->vomitRecaptcha(),
            'pageName' => 'Resend Email'
        );

        if (Request::POST('resend_email')) {

            $resp = Account::resendEmailHandler($this->auth);
            $opt['responseMessage'] = $resp;

        }

        $this->renderTemplate('Resend-Email.tpl', $opt);

    }

    public function confirm_account($hash) {

        $opt = array(
            'userLoggedIn' => $this->auth->UserLoggedIn(),
            'captchaCode' => $this->auth->vomitRecaptcha(),
            'pageName' => 'Login'
        );

        $resp = Account::confirmEmailHandler($this->auth, $hash);
        $opt['responseMessage'] = $resp;

        header("Location: " . fernico_getAbsURL() . "account/login");
        fernico_desotry();

    }

    public function confirm_email_change($hash) {

        $resp = Account::confirmEmailChangeHandler($this->auth, $hash);

        $opt = array(
            'userLoggedIn' => $this->auth->UserLoggedIn(),
            'pageName' => 'Home',
            'globalMessage' => $resp
        );

        header("Location: " . fernico_getAbsURL() . "account/login");
        fernico_desotry();

    }

    public function confirm_password_change($hash) {

        $opt = array(
            'userLoggedIn' => $this->auth->UserLoggedIn(),
            'pageName' => 'Reset Password'
        );

        $resp = $this->auth->isValidResetLink($hash);

        if ($resp == 'IS_VALID_RESET_LINK') {

            if (Request::POST('password') != "" && Request::POST('password_repeat') != null) {

                $co_resp = Account::confirmPasswordChangeHandler($this->auth, $hash);

                if ($co_resp === true) {

                    header("Location: " . fernico_getAbsURL() . "account/login");
                    fernico_destroy();

                } else {

                    $opt['responseMessage'] = $co_resp;
                    $this->renderTemplate('Reset-Password-Change.tpl', $opt);

                }

            } else {

                $this->renderTemplate('Reset-Password-Change.tpl', $opt);

            }

        } else {

            $opt['responseMessage'] = ResponseTranslator::respCode($resp);
            $this->renderTemplate('Reset-Password.tpl', $opt);

        }

    }

    public function settings() {

        global $fernico_db;

        $opt = array(
            'userLoggedIn' => $this->auth->UserLoggedIn(),
            'pageName' => 'Account Settings',
        );

        if (Request::POST('change_address_details')) {

            $address = Request::POST('address');

            $stmt = $fernico_db->stmt_init();
            $stmt->prepare("SELECT COUNT(user_id) as count FROM users WHERE address = ?");
            $stmt->bind_param("s", $address);
            $stmt->execute();
            $data = $stmt->get_result();
            $stmt->close();
            $dataAssoc = $data->fetch_assoc();

            if ($dataAssoc['count'] < 0.99) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("UPDATE users SET address = ? WHERE user_id = ?");
                $stmt->bind_param("si", $address, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();

                $opt['changeAddressDetailsMessage'] = "Changed successfully.";

            } else {

                $opt['changeAddressDetailsMessage'] = "The address is already being used by someone else.";

            }

        }

        $opt['address'] = ($fernico_db->query("SELECT address FROM users WHERE user_id = {$_SESSION['user_id']}"))->fetch_assoc()['address'];

        if (Request::POST('change_email_details')) {

            $resp = Account::changeEmailHandler($this->auth);
            $opt['changeEmailMessage'] = $resp;

        }

        if (Request::POST('change_password_details')) {

            $resp = Account::changePasswordHandler($this->auth);
            $opt['changePasswordDetailsMessage'] = $resp;

        }

        $this->renderTemplate('Settings.tpl', $opt);

    }

    public function logout() {

        $this->auth->logout();

        header("Location: " . fernico_getAbsURL() . "account/login");
        fernico_desotry();

    }

}
