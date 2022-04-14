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

class pageController extends AstridController {

    public function __construct() {
        require_once(FERNICO_PATH . "/models/Bootstrapper.php");
        parent::__construct();
        $this->auth = new Authentication();
    }

    public function dashboard($hashWhichWillBeClaimed = "") {

        App::vomitLoginPageByRedirection($this->auth);

        global $fernico_db;

        $opt = array(
            'pageName' => 'Faucet',
            'captchaCode' => App::getCaptcha(),
            'claims_registered' => $fernico_db->query("SELECT id, amount_credited, time FROM claims_registered WHERE user_id = {$_SESSION['user_id']} ORDER BY id DESC LIMIT 100")
        );

        $reward = App::loadSiteVar('faucet_reward');
        $winAmt = $opt['winAmt'] = $reward;

        if ($hashWhichWillBeClaimed != "") {

            if (strlen($hashWhichWillBeClaimed) == 64) {

                $stmt = $fernico_db->stmt_init();
                $stmt->prepare("SELECT id, win_amount FROM claims_hashes WHERE user_id = ? AND hash = ?");
                $stmt->bind_param("is", $_SESSION['user_id'], $hashWhichWillBeClaimed);
                $stmt->execute();
                $data = $stmt->get_result();
                $stmt->close();
                $dataAssoc = $data->fetch_assoc();

                if ($data->num_rows > 0.99) {

                    $paymentApiResponse = App::sendFaucetPay($_SESSION['user_id'], $dataAssoc['win_amount']);

                    $time = time();

                    if (is_array($paymentApiResponse) && $paymentApiResponse['status'] == 200) {

                        $opt['responseMessage'] = "Your faucet claim of " . $dataAssoc['win_amount'] . " " . App::loadSiteVar('coin_abbreviation') . " has been sent to your FaucetPay.io account.";

                        $stmt = $fernico_db->stmt_init();
                        $stmt->prepare("INSERT INTO claims_registered (user_id, user_name, time, amount_credited) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("isid", $_SESSION['user_id'], $_SESSION['user_name'], $time, $winAmt);
                        $stmt->execute();
                        $stmt->close();

                        $fernico_db->query("DELETE FROM claims_hashes WHERE id = {$dataAssoc['id']}");

                        $referralPercentage = App::loadSiteVar('referral_percentage');
                        $referralReward = bcmul(($referralPercentage / 100), $dataAssoc['win_amount'], 8);
                        $referral = $fernico_db->query("SELECT referral FROM users WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['referral'];

                        if ($referral != 0) {

                            if ((App::sendFaucetPay($referral, $referralReward, true))['status'] == 200) {

                                $referralName = $fernico_db->query("SELECT user_name FROM users WHERE user_id = {$referral}")->fetch_assoc()['user_name'];

                                $stmt = $fernico_db->stmt_init();
                                $stmt->prepare("INSERT INTO referral_returns (user_name, referred_by, amount, time) VALUES (?, ?, ?, ?)");
                                $stmt->bind_param("sidi", $referralName, $_SESSION['user_id'], $referralReward, $time);
                                $stmt->execute();
                                $stmt->close();

                                $fernico_db->query("UPDATE users SET referral_income = referral_income + {$referralReward} WHERE user_id = {$referral}");

                            }

                            $fernico_db->query("UPDATE users SET last_claimed = {$time}, claims_made = claims_made + 1, referred_income = referred_income + {$referralReward} WHERE user_id = {$_SESSION['user_id']}");

                        } else {

                            $referralReward = 0;
                            $fernico_db->query("UPDATE users SET last_claimed = {$time}, claims_made = claims_made + 1 WHERE user_id = {$_SESSION['user_id']}");

                        }

                        $ttotal = $referralReward + $dataAssoc['win_amount'];

                        $opt['claims_registered'] = $fernico_db->query("SELECT id, amount_credited, time FROM claims_registered WHERE user_id = {$_SESSION['user_id']} ORDER BY id DESC LIMIT 100");

                        $fernico_db->query("UPDATE config SET value = value + 1 WHERE parameter = 'stats_Claims_Made'");
                        $fernico_db->query("UPDATE config SET value = value + {$ttotal} WHERE parameter = 'stats_Amount_Claimed'");

                    } else {

                        $opt['responseMessage'] = "We've failed to process your claim. FaucetPay.io responded with an error: " . $paymentApiResponse['message'];

                    }

                }

            }

        }

        if (Request::POST('claim')) {

            if (App::verifyCaptcha() == true) {

                $lastClaimed = $fernico_db->query("SELECT last_claimed FROM users WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['last_claimed'];
                $diff = time() - $lastClaimed;
                $diffShouldBe = round(App::loadSiteVar('faucet_time_limit') * 60);

                if ($diff >= $diffShouldBe) {

                    $claimHash = hash('sha256', time() . mt_rand() . time() . mt_rand() . time() . uniqid() . microtime() . md5(time() . mt_rand()));
                    $linkClaim = fernico_getAbsURL() . "page/dashboard/" . $claimHash;

                    if (App::loadSiteVar('shortlink_preference') == '1') {

                        $getShortLink = fernico_get("https://ouo.io/api/" . App::loadSiteVar('ouo_api_key') . "?s=" . $linkClaim);

                    } elseif (App::loadSiteVar('shortlink_preference') == '2') {

                        $getShortLink = App::shortest($linkClaim);

                    }

                    if (App::loadSiteVar('shortlink_preference') != 0 && ($getShortLink == "" OR !filter_var($getShortLink, FILTER_VALIDATE_URL))) {

                        $opt['responseMessage'] = "It seems that there is an issue with our system right now, please try again later.";

                    } else {

                        $time = time();

                        $stmt = $fernico_db->stmt_init();
                        $stmt->prepare("INSERT INTO claims_hashes (user_id, hash, win_amount, time) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("isdi", $_SESSION['user_id'], $claimHash, $winAmt, $time);
                        $stmt->execute();
                        $stmt->close();

                        if (in_array(App::loadSiteVar('shortlink_preference'), [1, 2])) {

                            header("Location: " . $getShortLink);
                            fernico_destroy();

                        } else {

                            header("Location: " . $linkClaim);
                            fernico_destroy();

                        }

                    }

                } else {

                    $timeWait = round(($diffShouldBe - $diff) / 60);

                    $opt['responseMessage'] = "You have to wait for " . $timeWait . " minutes before you will be able to claim again.";

                }

            } else {

                $opt['responseMessage'] = "It seems that you haven't passed the captcha test successfully.";

            }

        }

        $this->renderTemplate('Dashboard.tpl', $opt);

    }

    public function affiliate__programme() {

        global $fernico_db;

        App::vomitLoginPageByRedirection($this->auth);

        $opt = array('pageName' => 'Affiliate Programme');

        $opt['u'] = $fernico_db->query("SELECT referral_income FROM users WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc();

        $this->renderTemplate('Affiliate-Programme.tpl', $opt);

    }

    public function referred__users() {

        global $fernico_db;

        App::vomitLoginPageByRedirection($this->auth);

        $opt = array('pageName' => 'Referred Users');

        $rc = $fernico_db->query("SELECT COUNT(user_id) AS id FROM users WHERE referral = {$_SESSION['user_id']} ORDER BY user_id DESC");
        $numrows = $rc->fetch_assoc();

        $records = 50;
        $total_pages = ceil($numrows['id'] / $records);

        if (isset($_GET['offset']) && is_numeric($_GET['offset'])) {
            $req_page = (int)Request::GET('offset');
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

        $opt['items'] = $fernico_db->query("SELECT user_name, registration_datetime, referred_income FROM users WHERE referral = {$_SESSION['user_id']} ORDER BY user_id DESC LIMIT $offset, $records");
        $opt['req_page'] = $req_page;
        $opt['total_pages'] = $total_pages;

        $this->renderTemplate('Referred-Users.tpl', $opt);

    }

    public function referral__claims() {

        global $fernico_db;

        App::vomitLoginPageByRedirection($this->auth);

        $opt = array('pageName' => 'Referral Claims');

        $rc = $fernico_db->query("SELECT COUNT(id) AS id FROM referral_returns WHERE referred_by = {$_SESSION['user_id']} ORDER BY id DESC");
        $numrows = $rc->fetch_assoc();

        $records = 100;
        $total_pages = ceil($numrows['id'] / $records);

        if (isset($_GET['offset']) && is_numeric($_GET['offset'])) {
            $req_page = (int)Request::GET('offset');
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

        $opt['items'] = $fernico_db->query("SELECT user_name, amount, time FROM referral_returns WHERE referred_by = {$_SESSION['user_id']} ORDER BY id DESC LIMIT $offset, $records");
        $opt['req_page'] = $req_page;
        $opt['total_pages'] = $total_pages;

        $this->renderTemplate('Referral-Claims.tpl', $opt);

    }

    public function contact() {

        $opt = array('pageName' => 'Contact');

        if (Request::POST('contactForm') != null) {
            $opt['responseMessage'] = App::contactFormSubmit();
        }

        $this->renderTemplate('Contact.tpl', $opt);

    }

}
