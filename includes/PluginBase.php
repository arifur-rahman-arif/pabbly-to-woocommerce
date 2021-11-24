<?php

namespace PTW\includes;

class PluginBase {

    /**
     * @var mixed
     */
    public $RestRoute = null;
    /**
     * @var mixed
     */
    public $OptionSettings = null;

    public function __construct() {
        $this->initializeClasses();
        $this->loadFunctions();
    }

    public function initializeClasses() {
        $this->RestRoute = new \PTW\includes\classes\RestRoute();
        $this->OptionSettings = new \PTW\includes\classes\OptionSettings();
    }

    public function loadFunctions() {
        require_once PTW_BASE_PATH . 'includes/functions/template/admin/admin-functions.php';
    }

}