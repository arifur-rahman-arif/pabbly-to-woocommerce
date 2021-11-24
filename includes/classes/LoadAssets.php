<?php

namespace PTW\includes\classes;

class LoadAssets {
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'loadAssets']);
    }

    public function loadAssets() {
        if (isset($_GET['page']) && $_GET['page'] === 'email-to-order') {

            // wp_enqueue_style('ptw_admin', PTW_BASE_URL . 'assets/DataTables/datatables.min.css', [], PTW_VERSION, 'all');

            wp_enqueue_script('jquery');
            // wp_enqueue_script('ptw_datatable', PTW_BASE_URL . 'assets/DataTables/datatables.min.js', ['jquery'], PTW_VERSION, true);
            wp_enqueue_script('ptw_admin', PTW_BASE_URL . 'assets/admin/scripts/admin.js', ['jquery'], PTW_VERSION, true);
            $this->localizeScripts();
        }

    }

    public function localizeScripts() {
        wp_localize_script('ptw_admin', 'localizeData', [
            'ajaxURL' => esc_url(admin_url('admin-ajax.php'))
        ]);
    }
}