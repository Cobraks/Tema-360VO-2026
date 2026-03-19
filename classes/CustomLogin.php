<?php
// classes/class-e360vo-custom-login.php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class E360VO_CustomLogin
{
    public function __construct()
    {
        add_action('login_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('login_header', [$this, 'add_custom_logo']);
    }

    public function enqueue_styles()
    {
        wp_enqueue_style('custom-login', THEME_URI . '/public/assets/css/custom-login.css', [], THEME_VERSION);
    }

    public function add_custom_logo()
    {
        if (has_custom_logo()) {
            $custom_logo_id = get_theme_mod('custom_logo');
            $logo = wp_get_attachment_image_src($custom_logo_id, 'full');

?>
            <div class="custom-login-logo">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="<?php echo esc_url($logo[0]); ?>" alt="<?php bloginfo('name'); ?>">
                </a>
            </div>
        <?php
        } else {
        ?>
            <div class="custom-login-logo">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="<?php echo esc_url(THEME_URI . '/assets/images/default-logo.png'); ?>" alt="<?php bloginfo('name'); ?>">
                </a>
            </div>
<?php
        }
    }
}
