<?php
/**
 * @author      Wployalty (Alagesan)
 * @license     http://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://www.wployalty.net
 * */

defined('ABSPATH') or die;
$enable_conversion_in_page = isset($options['enable_conversion_in_page']) && $options['enable_conversion_in_page'] == 'yes';
?>
<div id="wlac-settings">
    <div class="wlac-setting-page-holder">
        <div class="wlac-spinner">
            <span class="spinner"></span>
        </div>
        <form id="wlac-settings_form" method="post">
            <div class="wlac-settings-header">
                <div class="wlac-setting-heading"><p><?php esc_html_e('SETTINGS', 'wp-loyalty-auto-currency') ?></p>
                </div>
                <div class="wlac-button-block">
                    <div class="wlac-back-to-apps">
                        <a class="button" target="_self"
                           href="<?php echo isset($app_url) ? esc_url($app_url) : '#'; ?>">
                            <?php esc_html_e('Back to WPLoyalty', 'wp-loyalty-auto-currency'); ?></a>
                    </div>
                    <div class="wlac-save-changes">
                        <button type="button" id="wlac-setting-submit-button" onclick="wlac.saveSettings();">
                            <span><?php esc_html_e('Save Changes', 'wp-loyalty-auto-currency') ?></span>
                        </button>
                    </div>
                    <span class='spinner'></span>
                </div>
            </div>
            <div class="wlac-setting-body">
                <div class="wlac-settings-body-content">
                    <div class="wlac-field-block">
                        <div>
                            <label
                                    class="wlac-settings-enable-conversion-label"><?php esc_html_e('Enable Currency conversion in WPLoyalty pages', 'wp-loyalty-auto-currency'); ?></label>
                        </div>
                        <div class="wlpe-input-field">
                            <select name="enable_conversion_in_page">
                                <option value="no" <?php echo !$enable_conversion_in_page ? 'selected="selected"' : ''; ?> ><?php esc_html_e('No', 'wp-loyalty-auto-currency'); ?></option>
                                <option value="yes" <?php echo $enable_conversion_in_page ? 'selected="selected"' : ''; ?>><?php esc_html_e('Yes', 'wp-loyalty-auto-currency'); ?></option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="action" value="wlac_save_settings">
                    <input type="hidden" name="wlac_nonce"
                           value="<?php echo isset($wlac_setting_nonce) && !empty($wlac_setting_nonce) ? esc_attr($wlac_setting_nonce) : ''; ?>">
                    <input type="hidden" name="option_key"
                           value="wlac_settings">
                </div>
            </div>
        </form>
    </div>
</div>
