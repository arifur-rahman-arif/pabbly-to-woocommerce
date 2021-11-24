<div class="wrap">
    <form action="options.php" method="POST">
        <?php settings_fields('ptw_options')?>
        <?php do_settings_sections('email-to-order')?>
        <?php submit_button('Save Settings');?>
    </form>
</div>