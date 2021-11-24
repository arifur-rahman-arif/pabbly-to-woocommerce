<?php

namespace PTW\includes\classes;

class OptionSettings {
    public function __construct() {
        // Add admin menu to control product fields
        add_action('admin_menu', [$this, 'adminMenus']);
        add_action('admin_init', [$this, 'addOptionSettings']);
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

    // Register settings for product options
    public function addOptionSettings() {

        $settinsKey = [
            'ptw_item_id',
            'ptw_wc_product_id'
        ];

        foreach ($settinsKey as $key => $settingKey) {
            register_setting(
                'ptw_options',
                $settingKey
            );
        }

        add_settings_section(
            'ptw_settings_section_id',
            'Product Options',
            null,
            'email-to-order'
        );
        add_settings_field(
            'ptw_settings_field_id',
            "",
            [$this, 'optionsFieldHTML'],
            'email-to-order',
            'ptw_settings_section_id'
        );
    }

    // Display the html of of product admin menu option page
    public function optionsFieldHTML() {
        load_template(PTW_BASE_PATH . 'includes/templates/admin/parts/options-settings.php');
    }
}