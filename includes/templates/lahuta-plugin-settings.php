<div class="lahuta-plugin-settings container-fluid">
    <div class="row">
        <div class="col-12">
            <h4><?php esc_html_e( 'Plugin Settings', 'lahuta-plugin' ); ?></h4>
            <p><?php esc_html_e( 'Manage the settings for the Lahuta plugin.', 'lahuta-plugin' ); ?></p>
        </div>
    </div>

    <form id="lahuta-plugin-settings-form">
        <div class="row">
            <div class="col-12">
                <?php do_settings_sections( \LahutaRestAPI\Admin\LahutaPluginSettings::SETTINGS_PAGE ); ?>
            </div>
        </div>

        <div class="row align-items-center mt-3">
            <div class="col-auto">
                <button type="submit" class="btn btn-success" id="lahuta-save-plugin-settings">
                    <?php esc_html_e( 'Save Settings', 'lahuta-plugin' ); ?>
                </button>
            </div>
            <div class="col">
                <div id="lahuta-plugin-settings-message" class="alert d-none mb-0" role="status" aria-live="polite"></div>
            </div>
        </div>
    </form>
</div>