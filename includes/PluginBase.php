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
    /**
     * @var mixed
     */
    public $LoadAssets = null;

    /**
     * @var mixed
     */
    public $AjaxHooks = null;
    /**
     * @var mixed
     */
    public $Hooks = null;

    public function __construct() {
        $this->initializeClasses();
        $this->loadFunctions();
    }

    public function initializeClasses() {
        $this->RestRoute = new \PTW\includes\classes\RestRoute();
        $this->OptionSettings = new \PTW\includes\classes\OptionSettings();
        $this->LoadAssets = new \PTW\includes\classes\LoadAssets();
        $this->AjaxHooks = new \PTW\includes\classes\AjaxHooks();
        $this->Hooks = new \PTW\includes\classes\Hooks();
    }

    public function loadFunctions() {
        require_once PTW_BASE_PATH . 'includes/functions/template/admin/admin-functions.php';
    }

}