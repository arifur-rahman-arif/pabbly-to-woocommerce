<?php

namespace PTW\includes\classes;

class AjaxHooks {

    /**
     * @var array
     */
    public $output = [];

    public function __construct() {
        add_action('wp_ajax_ptw_save_options', [$this, 'saveOptions']);

        add_action('wp_ajax_ptw_delete_option', [$this, 'deleteOptions']);
    }

    /**
     * Sanitize an array of data
     * @param  array   $NonSanitzedData
     * @return mixed
     */
    public function sanitizeData(array $NonSanitzedData) {
        $sanitizedData = null;

        $sanitizedData = array_map(function ($data) {
            if (gettype($data) == 'array') {
                return $this->sanitizeData($data);
            } else {
                return sanitize_text_field($data);
            }
        }, $NonSanitzedData);

        return $sanitizedData;
    }

    public function saveOptions() {
        if (sanitize_text_field($_POST['action']) != 'ptw_save_options') {
            $this->output['response_type'] = esc_html('invalid_action');
            $this->output['output'] = '<b>' . esc_html__('Action is invalid', 'ptw') . '</b>';
            echo json_encode($this->output);
            wp_die();
        }

        $this->saveOptionsToDb();

        echo json_encode($this->output);
        wp_die();
    }

    public function saveOptionsToDb() {
        $sanitizedData = $this->sanitizeData($_POST);

        update_option('ptwOptions', $sanitizedData['organizedData']);
        $this->output['response_type'] = esc_html('success');
        $this->output['output'] = '<b>' . esc_html__('Data saved successfully', 'ptw') . '</b>';
        echo json_encode($this->output);
        wp_die();
    }

    public function deleteOptions() {
        if (sanitize_text_field($_POST['action']) != 'ptw_delete_option') {
            $this->output['response_type'] = esc_html('invalid_action');
            $this->output['output'] = '<b>' . esc_html__('Action is invalid', 'ptw') . '</b>';
            echo json_encode($this->output);
            wp_die();
        }

        $this->deleteOptionsFromDb();

        echo json_encode($this->output);
        wp_die();
    }

    public function deleteOptionsFromDb() {
        $sanitizedData = $this->sanitizeData($_POST);

        $ptwOptions = get_option('ptwOptions');

        if ($ptwOptions) {
            $deleteAction = $sanitizedData['deleteAction'];

            if ($deleteAction == 'single_delete') {
                $optionID = intval($sanitizedData['id']) - 1;

                if (isset($ptwOptions[$optionID])) {
                    unset($ptwOptions[$optionID]);
                    update_option('ptwOptions', $ptwOptions);
                    $this->output['response_type'] = esc_html('success');
                    $this->output['output'] = esc_html__('Data deleted successfully', 'ptw');
                    echo json_encode($this->output);
                    wp_die();
                } else {
                    $this->output['response_type'] = esc_html('invalid_action');
                    $this->output['output'] = esc_html__('Data is not found', 'ptw');
                    echo json_encode($this->output);
                    wp_die();
                }
            }

            if ($deleteAction == 'delete_all') {
                if (delete_option('ptwOptions')) {
                    $this->output['response_type'] = esc_html('success');
                    $this->output['type'] = esc_html('delete_all');
                    $this->output['output'] = esc_html__('All data deleted successfully', 'ptw');
                    echo json_encode($this->output);
                    wp_die();
                }
            }
        }

    }
}