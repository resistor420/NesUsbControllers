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

class Error404Controller extends AstridController {

    public function __construct() {
        require_once(FERNICO_PATH . "/models/Bootstrapper.php");
        parent::__construct();
        $this->auth = new Authentication();
    }

    public function error404() {
        http_response_code(404);
        fernico_loadComponent(Config::fetch('TEMPLATE_DIR'), 'Error404.tpl', array('pageName' => 'Page Not Found'));
    }

}
