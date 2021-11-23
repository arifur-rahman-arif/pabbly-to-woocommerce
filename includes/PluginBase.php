<?php

namespace PTW\includes;
use PHPHtmlParser\Dom;

class PluginBase {

    /**
     * @var mixed
     */
    public $RestRoute = null;

    public function __construct() {
        $this->initializeClasses();
        $this->initializeHooks();
        $this->parseHTML();
    }

    public function initializeClasses() {
        $this->RestRoute = new \PTW\includes\classes\RestRoute();
    }

    public function initializeHooks() {
    }

    public function parseHTML() {

        $dom = new Dom;
        $dom->loadStr(file_get_contents(PTW_BASE_PATH . 'data.html'));
        $tr = $dom->find('div[size=Letter] tbody tr')[1];
        $shipTo = $tr->find('td')[1];
        $billingAddress = explode("<br />", $shipTo->innerHtml);
        // wp_console_log($billingAddress);
    }

}