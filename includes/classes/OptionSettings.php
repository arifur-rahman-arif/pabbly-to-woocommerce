<?php

namespace PTW\includes\classes;

class OptionSettings {
    public function __construct() {
        // Add admin menu to control product fields
        add_action('admin_menu', [$this, 'adminMenus']);
    }

    // Registering admin menus to control product options
    public function adminMenus() {
        add_menu_page(
            __('Email To Order', 'ptw'),
            __('Email To Order', 'ptw'),
            'manage_options',
            'email-to-order',
            [$this, 'adminPage'],
            'dashicons-tickets',
            5
        );
    }

    public function adminPage() {
        load_template(PTW_BASE_PATH . 'includes/templates/admin/dashboard.php', true);
    }
}