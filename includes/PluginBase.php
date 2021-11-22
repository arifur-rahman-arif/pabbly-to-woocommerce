<?php

namespace PTW\includes;

class PluginBase {

    /**
     * @var mixed
     */
    public $RestRoute = null;

    public function __construct() {
        $this->initializeClasses();
        $this->initializeHooks();
    }

    public function initializeClasses() {
        $this->RestRoute = new \PTW\includes\classes\RestRoute();
    }

    public function initializeHooks() {
    }

}