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

class homeIndexController extends AstridController {

    public function __construct() {
        require_once(FERNICO_PATH . "/models/Bootstrapper.php");
        parent::__construct();
        $this->auth = new Authentication();
    }

    public function home() {

        global $fernico_db;

        $opt = array(
            'pageName' => App::loadSiteVar('website_homepage_title'),
            'claims_registered' => $fernico_db->query("SELECT id, user_name, amount_credited, time FROM claims_registered ORDER BY id DESC LIMIT 15")
        );

        $this->renderTemplate('Home.tpl', $opt);

    }

}
