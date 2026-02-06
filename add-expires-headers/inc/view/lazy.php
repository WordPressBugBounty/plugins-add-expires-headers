<?php
if (!defined('ABSPATH')) die;
function save_lazy_loading_settings($aeh_lazy_loading_settings)
{
    $errors = '';
    if (isset($_POST['aeh_lazy_loading_settings']['submit_form']) && wp_verify_nonce($_POST['aeh_nonce_header'], 'aeh_lazy_loading_submit')) {
        $aeh_lazy_loading_settings = esc_sql($_POST['aeh_lazy_loading_settings']);
        $aeh_lazy_loading_save_settings = AEH_Settings::parse_lazy_loading_settings($aeh_lazy_loading_settings);
        if ($aeh_lazy_loading_save_settings) {
            if ($aeh_lazy_loading_save_settings['shortcode_tags']) {
                foreach ($aeh_lazy_loading_save_settings['shortcode_tags'] as $key => $shortcode_tags) {
                    if (empty($shortcode_tags['shortcodeTag'])) {
                        unset($aeh_lazy_loading_save_settings['shortcode_tags'][$key]);
                    }
                }
            }
            if (strlen($errors) > 0) {
                echo "<script>
    jQuery(document).ready(function() {
        M.toast({
            html: 'Please correct following Errors:',
            classes: 'rounded red',
            displayLength: 6000
        });
        M.toast({
            html: '" . $errors . "',
            classes: 'rounded red',
            displayLength: 8000
        });
    });
</script>";
            } else {
                $aeh_lazy_loading_settings = $aeh_lazy_loading_save_settings;
                update_option('aeh_lazy_loading_settings', $aeh_lazy_loading_save_settings);
                echo "<script>
    jQuery(document).ready(function() {
        M.toast({
            html: 'Setting Saved!',
            classes: 'rounded teal',
            displayLength: 4000
        });
    });
</script>";
                $aeh_lazy_loading_settings = $aeh_lazy_loading_save_settings;
            }
        } else {
            echo "<script>
    jQuery(document).ready(function() {
        M.toast({
            html: 'Unable to update changes. Please try again!',
            classes: 'rounded teal',
            displayLength: 4000
        });
    });
</script>";
        }
    }
    return isset($aeh_lazy_loading_settings) ? $aeh_lazy_loading_settings : false;
}

$aeh_settings = AEH_Settings::get_instance();
$defaults = $aeh_settings->lazy_loading_settings;
$aeh_lazy_loading_settings = get_option('aeh_lazy_loading_settings', $defaults);
$aeh_lazy_loading_settings = save_lazy_loading_settings($aeh_lazy_loading_settings);

function cc_get($key, $radio = '')
{
    $settings = get_option('aeh_lazy_loading_settings');
    return $radio
        ? (isset($settings[$key]) && $settings[$key] == $radio ? $settings[$key] : '')
        : ($settings[$key] ?? '');
}
?>

<div class="col s12 aeh-options">
    <div class="col s12" style="margin-top:15px">
        <h5 class="left margin-zero">Lazy Loading Settings</h5>
        <a href="https://addexpiresheaders.com/contact-us" target="_blank" class="waves-effect waves-light btn-small right">
            <i class="material-icons left">message</i>Support
        </a>
    </div>

    <div class="clearfix"></div>
    <div class="divider" style="margin-top:15px"></div>

    <form method="post" action="" style="margin-top:20px">
        <div class="switch" style="margin-top:15px">
            <label>
                <input type="checkbox" name="aeh_lazy_loading_settings[enable]" <?= cc_get('enable') ? 'checked' : '' ?>>
                <span class="lever"></span>
                <span>Enable lazy loading features to improve page speed and reduce unnecessary resource loading.</span>
            </label>
        </div>

        <div class="section">
            <label><strong>Image & Media Lazy Loading</strong></label>
            <p><label><input type="checkbox" name="aeh_lazy_loading_settings[lazyload_images]" <?= cc_get('lazyload_images') ? 'checked' : '' ?> /><span>Lazy Load Images – Only load images when they enter the viewport.</span></label></p>
            <p><label><input type="checkbox" name="aeh_lazy_loading_settings[lazyload_iframes]" <?= cc_get('lazyload_iframes') ? 'checked' : '' ?> /><span>Lazy Load Iframes – Delay loading of embedded content like YouTube.</span></label></p>
            <p><label><input type="checkbox" name="aeh_lazy_loading_settings[lazyload_videos]" <?= cc_get('lazyload_videos') ? 'checked' : '' ?> /><span>Lazy Load Videos – Load HTML5 videos only when visible.</span></label></p>
            <p><label><input type="checkbox" name="aeh_lazy_loading_settings[lazyload_backgrounds]" <?= cc_get('lazyload_backgrounds') ? 'checked' : '' ?> /><span>Lazy Load Background Images – Useful for sections with heavy backgrounds.</span></label></p>
        </div>

        <div class="section">
            <label><strong>Advanced Widget & Shortcode Optimization</strong></label>
            <p><label><input type="checkbox" name="aeh_lazy_loading_settings[lazyload_shortcodes]" <?= cc_get('lazyload_shortcodes') ? 'checked' : '' ?> /><span>Lazy Load Shortcodes – Delay shortcode execution until visible.</span></label></p>
            <p><label><input type="checkbox" name="aeh_lazy_loading_settings[lazyload_widgets]" <?= cc_get('lazyload_widgets') ? 'checked' : '' ?> /><span>Lazy Load Widgets – Load widgets only when needed.</span></label></p>
        </div>

        <div class="section">
            <label><strong>Custom Shortcodes to Lazy Load</strong></label>
            <div id="repeater">
                <!-- Repeater Heading -->
                <div class="col s12" style="margin-top:15px">
                    <h6 class="left">Add Shortcodes Here:</h6>
                    <a class="waves-effect waves-light btn right repeater-add-btn"><i class="material-icons left">add</i>Add</a>
                </div>
                <div class="clearfix" style="clear:both"></div>
                <!-- Repeater Items -->
                <div class="items" data-group="aeh_lazy_loading_settings[shortcode_tags]">
                    <!-- Repeater Content -->
                    <div class="item-content">
                        <div class="col s12">
                            <div class="col s4 form-group">
                                <div class="input-field">
                                    <input type="text" class="form-control" id="shortcodeTag" placeholder="[shortcode]" data-name="shortcodeTag">
                                    <label for="shortcodeTag" class="control-label">Shortcode</label>
                                </div>
                            </div>
                            <div class="col s4 form-group">
                                <div class="pull-right repeater-remove-btn">
                                    <button class="btn btn-danger remove-btn">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($aeh_lazy_loading_settings['shortcode_tags']) && is_array($aeh_lazy_loading_settings['shortcode_tags'])) {
                        foreach ($aeh_lazy_loading_settings['shortcode_tags'] as $shortcode_tags) {
                    ?>
                            <div class="items" data-group="aeh_lazy_loading_settings[shortcode_tags]">
                                <!-- Repeater Content -->
                                <div class="item-content">
                                    <div class="col s12">
                                        <div class="col s4 form-group">
                                            <div class="input-field">
                                                <input type="text" class="form-control" id="shortcodeTag" value="<?php echo $shortcode_tags['shortcodeTag']; ?>" placeholder="File Extension" data-name="shortcodeTag">
                                                <label for="shortcodeTag" class="control-label">File Extension</label>
                                            </div>
                                        </div>
                                        <div class="col s4 form-group">
                                            <div class="pull-right repeater-remove-btn">
                                                <button class="btn btn-danger remove-btn">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php }
                    } ?>
                    <div class="clearfix" style="clear:both"></div>
                </div>
            </div>
        </div>

        <div class="section">
            <label><strong>Exclude Critical Elements</strong></label>
            <div class="input-field">
                <input type="text" name="aeh_lazy_loading_settings[exclude_selectors]" placeholder=".logo, #hero-banner, .important-class, 123" value="<?= cc_get('exclude_selectors') ?? '' ?>" />
                <label>Exclude Selectors – Comma-separated list of CSS selectors or post IDs to exclude from lazy loading.</label>
            </div>
        </div>

        <div class="section">
            <label><strong>Additional Settings</strong></label>
            <p><label><input type="checkbox" name="aeh_lazy_loading_settings[support_srcset]" <?= cc_get('support_srcset') ? 'checked' : '' ?> /><span>Enable Responsive Image Support (srcset/picture)</span></label></p>
            <label>Animation on Load</label>
            <p class="radio-group">
                <?php
                $animations = ['none' => 'None', 'fade-in' => 'Fade In', 'slide-bottom' => 'Slide from Bottom', 'slide-right' => 'Slide from Right', 'slide-left' => 'Slide from Left'];
                foreach ($animations as $value => $label) { ?>
                    <label>
                        <input name="aeh_lazy_loading_settings[animation_on_load]" type="radio" value="<?= $value ?>" <?= cc_get('animation_on_load', $value) ? 'checked' : '' ?> />
                        <span><?= $label ?></span>
                    </label>
                <?php } ?>
            </p>
        </div>

        <div class="section">
            <label><strong>Custom Placeholder Image</strong></label>
            <div class="input-field">
                <input type="text" id="media_url" name="aeh_lazy_loading_settings[placeholder_url]" class="validate" placeholder="https://yourdomain.com/path/to/image.jpg" value="<?= esc_url(cc_get('placeholder_url')) ?>" />
                <label for="media_url">Placeholder Media URL</label>
                <a href="#" id="upload_media_button" class="btn waves-effect waves-light"><i class="material-icons left">cloud_upload</i> Upload</a>
            </div>
            <div id="media_preview" style="margin-top:20px;">
                <img id="preview_image" src="<?= esc_url(cc_get('placeholder_url')) ?>" alt="Preview" style="max-width:75px; height:75px; <?= empty(cc_get('placeholder_url')) ? 'display:none;' : '' ?>" class="z-depth-2 responsive-img">
            </div>
        </div>

        <div class="section" style="margin-top:30px">
            <button type="submit" name="aeh_lazy_loading_settings[submit_form]" class="btn waves-effect waves-light">
                Save Settings
                <i class="material-icons right">save</i>
            </button>
            <?php wp_nonce_field('aeh_lazy_loading_submit', 'aeh_nonce_header'); ?>
        </div>
    </form>
</div>