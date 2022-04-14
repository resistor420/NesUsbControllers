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

class fatalErrorController extends AstridController {
	
	public function __construct() {
        require_once(FERNICO_PATH . "/models/Bootstrapper.php");
        parent::__construct();
        $this->auth = new Authentication();
    }

    public function errorHandler($err_text, $error_msg) {
        fernico_loadComponent(Config::fetch('TEMPLATE_DIR'), 'Error500.tpl', array('message' => $err_text, 'error_message' => $error_msg, 'pageName' => 'Internal Error Occurred'));
    }

}
