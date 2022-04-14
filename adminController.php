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

class adminController extends AstridController {

    public function __construct() {
        require_once(FERNICO_PATH . "/models/Bootstrapper.php");
        parent::__construct();
        $this->auth = new Authentication();
    }

    public function home() {

        global $fernico_db;

        App::setAdminRedirections();

        $opt = array(
            'pageName' => 'Admin Dashboard'
        );


        if (Request::POST('update_user')) {

            $user_id = Request::POST('user_id', true);
            $user_name = Request::POST('user_name', true);
            $user_email = Request::POST('user_email', true);
            $user_verified = Request::POST('user_verified', true);
            $claims_made = Request::POST('claims_made', true);
            $referred_income = Request::POST('referred_income', true);
            $referral_income = Request::POST('referral_income', true);
            $referral = Request::POST('referral', true);
            $address = Request::POST('address', true);

            $stmt = $fernico_db->stmt_init();
            $stmt->prepare("UPDATE users SET user_name = ?, user_email = ?, user_verified = ?, claims_made = ?, referred_income = ?, referral_income = ?, referral = ?, address = ? WHERE user_id = ?");
            $stmt->bind_param("ssiiddisi", $user_name, $user_email, $user_verified, $claims_made, $referred_income, $referral_income, $referral, $address, $user_id);
            $stmt->execute();
            $stmt->close();

            $opt['responseMessage'] = "The changes have been applied to the user account.";

        }

        if (Request::POST('edit_user')) {

            $user = Request::POST('user', true);

            if (is_numeric($user)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_id, user_name, user_email, user_verified, claims_made, referred_income, referral_income, referral, address FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } elseif (filter_var($user, FILTER_VALIDATE_EMAIL)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_id, user_name, user_email, user_verified, claims_made, referred_income, referral_income, referral, address FROM users WHERE user_email = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } else {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_id, user_name, user_email, user_verified, claims_made, referred_income, referral_income, referral, address FROM users WHERE user_name = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            }

            if ($user['count'] > 0.99) {

                $opt['showEditSection'] = true;
                $opt['editData'] = $user;

            } else {

                $opt['responseMessage'] = "The user does not exist.";

            }

        }

        if (Request::GET('edit_user')) {

            $user = Request::GET('edit_user', true);

            if (is_numeric($user)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_id, user_name, user_email, user_verified, claims_made, referred_income, referral_income, referral, address FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } elseif (filter_var($user, FILTER_VALIDATE_EMAIL)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_id, user_name, user_email, user_verified, claims_made, referred_income, referral_income, referral, address FROM users WHERE user_email = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } else {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_id, user_name, user_email, user_verified, claims_made, referred_income, referral_income, referral, address FROM users WHERE user_name = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            }

            if ($user['count'] > 0.99) {

                $opt['showEditSection'] = true;
                $opt['editData'] = $user;

            } else {

                $opt['responseMessage'] = "The user does not exist.";

            }

        }


        if (Request::GET('delete_user')) {

            $user = Request::GET('delete_user', true);

            if (is_numeric($user)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } elseif (filter_var($user, FILTER_VALIDATE_EMAIL)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name FROM users WHERE user_email = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } else {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name FROM users WHERE user_name = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            }

            if ($user['count'] > 0.99) {

                $fernico_db->query("DELETE FROM users WHERE user_name = '{$user['user_name']}'");

                $opt['responseMessage'] = "The user with username <b>" . $user['user_name'] . "</b> has been successfully deleted.";

            } else {

                $opt['responseMessage'] = "The user does not exist.";

            }

        }

        if (Request::POST('delete_user')) {

            $user = Request::POST('user', true);

            if (is_numeric($user)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } elseif (filter_var($user, FILTER_VALIDATE_EMAIL)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name FROM users WHERE user_email = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } else {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name FROM users WHERE user_name = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            }

            if ($user['count'] > 0.99) {

                $fernico_db->query("DELETE FROM users WHERE user_name = '{$user['user_name']}'");

                $opt['responseMessage'] = "The user with username <b>" . $user['user_name'] . "</b> has been successfully deleted.";

            } else {

                $opt['responseMessage'] = "The user does not exist.";

            }

        }

        if (Request::GET('ban_unban_user')) {

            $user = Request::GET('ban_unban_user', true);

            if (is_numeric($user)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name, account_status FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } elseif (filter_var($user, FILTER_VALIDATE_EMAIL)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name, account_status FROM users WHERE user_email = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } else {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name, account_status FROM users WHERE user_name = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            }

            if ($user['count'] > 0.99) {

                if ($user['account_status'] == 1) {

                    $fernico_db->query("UPDATE users SET account_status = 0 WHERE user_name = '{$user['user_name']}'");
                    $opt['responseMessage'] = "The user with username <b>" . $user['user_name'] . "</b> has been successfully banned.";

                } else {

                    $fernico_db->query("UPDATE users SET account_status = 1 WHERE user_name = '{$user['user_name']}'");
                    $opt['responseMessage'] = "The user with username <b>" . $user['user_name'] . "</b> has been successfully unbanned.";

                }

            } else {

                $opt['responseMessage'] = "The user does not exist.";

            }

        }

        if (Request::POST('ban_unban_user')) {

            $user = Request::POST('user', true);

            if (is_numeric($user)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name, account_status FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } elseif (filter_var($user, FILTER_VALIDATE_EMAIL)) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name, account_status FROM users WHERE user_email = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            } else {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT COUNT(user_id) as count, user_name, account_status FROM users WHERE user_name = ?");
                $stmt->bind_param("s", $user);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $user = $data->fetch_assoc();

            }

            if ($user['count'] > 0.99) {

                if ($user['account_status'] == 1) {

                    $fernico_db->query("UPDATE users SET account_status = 0 WHERE user_name = '{$user['user_name']}'");
                    $opt['responseMessage'] = "The user with username <b>" . $user['user_name'] . "</b> has been successfully banned.";

                } else {

                    $fernico_db->query("UPDATE users SET account_status = 1 WHERE user_name = '{$user['user_name']}'");
                    $opt['responseMessage'] = "The user with username <b>" . $user['user_name'] . "</b> has been successfully unbanned.";

                }

            } else {

                $opt['responseMessage'] = "The user does not exist.";

            }

        }

        $this->renderTemplate('Admin/Home.tpl', $opt);

    }

    public function banned__users() {

        global $fernico_db;

        App::setAdminRedirections();

        $opt = array(
            'pageName' => 'Banned Users'
        );

        $data = array();

        $numrows = ($fernico_db->query("SELECT COUNT(user_id) as id FROM users WHERE account_status = 0 ORDER BY user_id DESC"))->fetch_assoc();

        $records = 200;
        $total_pages = ceil($numrows['id'] / $records);

        if (isset($_GET['offset']) && is_numeric(Request::GET('offset', true))) {
            $req_page = (int)Request::GET('offset', true);
        } else {
            $req_page = 1;
        }

        if ($req_page > $total_pages) {
            $req_page = $total_pages;
        }

        if ($req_page < 1) {
            $req_page = 1;
        }

        $offset = ($req_page - 1) * $records;

        $qry = $fernico_db->query("SELECT user_id,user_name,user_email,registration_datetime,registration_ip FROM users WHERE account_status = 0 ORDER BY user_id DESC LIMIT $offset, $records");
        $key = 0;

        while ($r = $qry->fetch_assoc()) {

            $data[$key]['user_id'] = $r['user_id'];
            $data[$key]['user_name'] = $r['user_name'];
            $data[$key]['user_email'] = $r['user_email'];
            $data[$key]['registration_datetime'] = $r['registration_datetime'];
            $data[$key]['registration_ip'] = $r['registration_ip'];
            $key++;

        }

        $opt['items'] = $data;
        $opt['req_page'] = $req_page;
        $opt['total_pages'] = $total_pages;

        $this->renderTemplate('Admin/Banned-Users.tpl', $opt);

    }


    public function users() {

        global $fernico_db;

        App::setAdminRedirections();

        $opt = array(
            'pageName' => 'Users'
        );

        $data = array();

        $numrows = ($fernico_db->query("SELECT COUNT(user_id) as id FROM users ORDER BY user_id DESC"))->fetch_assoc();

        $records = 200;
        $total_pages = ceil($numrows['id'] / $records);

        if (isset($_GET['offset']) && is_numeric(Request::GET('offset', true))) {
            $req_page = (int)Request::GET('offset', true);
        } else {
            $req_page = 1;
        }

        if ($req_page > $total_pages) {
            $req_page = $total_pages;
        }

        if ($req_page < 1) {
            $req_page = 1;
        }

        $offset = ($req_page - 1) * $records;

        $qry = $fernico_db->query("SELECT user_id,user_name,user_email,registration_datetime,registration_ip,account_status FROM users ORDER BY user_id DESC LIMIT $offset, $records");
        $key = 0;

        while ($r = $qry->fetch_assoc()) {

            $data[$key]['user_id'] = $r['user_id'];
            $data[$key]['user_name'] = $r['user_name'];
            $data[$key]['user_email'] = $r['user_email'];
            $data[$key]['registration_datetime'] = $r['registration_datetime'];
            $data[$key]['registration_ip'] = $r['registration_ip'];
            $data[$key]['account_status'] = $r['account_status'];
            $key++;

        }

        $opt['items'] = $data;
        $opt['req_page'] = $req_page;
        $opt['total_pages'] = $total_pages;

        $this->renderTemplate('Admin/Users.tpl', $opt);

    }

    public function ads() {

        header('X-XSS-Protection:0');

        global $fernico_db;

        App::setAdminRedirections();

        $opt = array(
            'pageName' => 'Ads'
        );

        if (Request::POST('submit')) {

            $size = (int)$fernico_db->real_escape_string(Request::POST('size'));
            $code = $fernico_db->real_escape_string(Request::POST('code'));
            $fernico_db->query("INSERT INTO ads (type,code) VALUES ({$size}, '{$code}')");

            $opt['responseMessage'] = "Added to database";

        }

        if (Request::GET('d')) {

            $id = (int)$fernico_db->real_escape_string(Request::GET('d'));
            $fernico_db->query("DELETE FROM ads WHERE id = {$id}");
            $opt['responseMessage'] = "Deleted from database";

        }

        $opt['items'] = $fernico_db->query("SELECT * FROM ads");

        $this->renderTemplate('Admin/Ads.tpl', $opt);

    }

    public function profile() {

        global $fernico_db;

        App::setAdminRedirections();

        $opt = array();
        $opt['pageName'] = "Admin Profile";

        if (isset($_POST['update_user_name']) && App::isAdmin() == true) {

            $user_name = Request::POST('new_user_name', true);

            if (strlen($user_name) < 3) {

                $opt['responseMessage'] = "The Username needs to be 3 characters at minimum.";

            } elseif (strlen($user_name) > 16) {

                $opt['responseMessage'] = "The Username needs to be 16 characters at maximum.";

            } elseif (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $user_name)) {

                $opt['responseMessage'] = "The Username may not contain any special character.";

            } else {

                $fernico_db->query("UPDATE admin_details SET user_name = '{$user_name}' WHERE user_name = '{$_SESSION['admin_user_name']}'");
                App::destroyAdminSession();
                $opt['responseMessage'] = "Your username has been changed. Please login with your new username now.";

            }

        }

        if (isset($_POST['update_password']) && App::isAdmin() == true) {

            $current_password = Request::POST('current_password', true);
            $new_password = Request::POST('new_password', true);

            $s = $fernico_db->query("SELECT password FROM admin_details WHERE user_name = '{$_SESSION['admin_user_name']}'");
            $s = $s->fetch_assoc();

            $hash = $s['password'];
            $hash_generated = App::generatePasswordHash($current_password);

            if (strlen($new_password) < 6) {

                $opt['responseMessage'] = "The Password you entered is too short, it needs to be 6 characters at minimum.";

            } elseif (strlen($new_password) > 64) {

                $opt['responseMessage'] = "The Password you entered is too long, it needs to be 64 characters at maximum.";

            } elseif ($hash != $hash_generated) {

                $opt['responseMessage'] = "The Current Password you entered does not match the one on your profile.";

            } else {

                $new_hash = App::generatePasswordHash($new_password);

                $fernico_db->query("UPDATE admin_details SET password = '{$new_hash}' WHERE user_name = '{$_SESSION['admin_user_name']}'");
                App::destroyAdminSession();
                $opt['responseMessage'] = "Your password has been changed. Please login with your new password now.";

            }

        }

        $this->renderTemplate('Admin/Profile.tpl', $opt);

    }

    public function login() {

        global $fernico_db;

        $opt = array(
            'pageName' => "Administrator Login"
        );

        if (isset($_POST['login']) && App::isAdmin() == false) {

            $user_name = Request::POST('user_name');
            $password = Request::POST('password');
            $password_hash = App::generatePasswordHash($password);

            $stmt = $fernico_db->stmt_init();
            $stmt->prepare("SELECT COUNT(id) as count, id FROM admin_details WHERE user_name = ? AND password = ?");
            $stmt->bind_param("ss", $user_name, $password_hash);
            $stmt->execute();
            $data = $stmt->get_result();
            $stmt->close();
            $user = $data->fetch_assoc();

            if ($user['count'] > 0.99) {

                $token = bin2hex(openssl_random_pseudo_bytes(64));
                $sessionTime = 60 * 60 * 24 * Config::fetch('SESSION_DAYS');

                $fernico_db->query("UPDATE admin_details SET token = '{$token}' WHERE id = {$user['id']}");
                $_SESSION['admin_user_name'] = $user_name;

                setcookie('admin_token', $token, time() + $sessionTime, "/", Config::fetch('COOKIE_DOMAIN'));
                header("Location: " . fernico_getAbsURL() . "admin/home");
                fernico_destroy();

            } else {

                $opt['responseMessage'] = "The login details you've used are not valid.";

            }

        }

        if (App::isAdmin() == true) {
            header("Location: " . fernico_getAbsURL() . "admin/home");
            fernico_destroy();
        }

        $this->renderTemplate('Admin/Login.tpl', $opt);

    }

    public function settings() {

        global $fernico_db;

        App::setAdminRedirections();

        $opt = array(
            'pageName' => 'Settings'
        );

        if (Request::POST('update')) {

            foreach ($_POST as $key => $value) {

                if ($key == 'coin_information') {

                    $units = explode("-", $value);
                    $coin_abbrev = $units[0];
                    $coin_name = $units[1];

                    $fernico_db->query("UPDATE config SET value = '{$coin_abbrev}' WHERE parameter = 'coin_abbreviation'");
                    $fernico_db->query("UPDATE config SET value = '{$coin_name}' WHERE parameter = 'coin_name'");

                } else {

                    $stmt = $fernico_db->stmt_init();
                    $stmt->prepare("UPDATE config SET value = ? WHERE parameter = ?");
                    $stmt->bind_param("ss", $value, $key);
                    $stmt->execute();
                    $stmt->close();

                }

            }

            $opt['responseMessage'] = "The settings have been updated successfully.";

        }

        $contents = json_decode(fernico_post('https://faucetpay.io/page/currs'), true);
        $opt['coins'] = $contents['currencies_names'];

        $this->renderTemplate('Admin/Settings.tpl', $opt);

    }

    public function logout() {

        App::setAdminRedirections();
        App::destroyAdminSession();
        header("Location: " . fernico_getAbsURL() . 'admin/login');

    }

}