<?php
// classes/UserAvatar.php
defined('ABSPATH') || exit;

/**
 * E360VO_UserAvatar
 *
 * Gestiona avatars de usuario sin Gravatar, con soporte retina
 * y corrige el enctype del form de perfil.
 */
class E360VO_UserAvatar
{
    public function __construct()
    {
        // Añade el campo de subida / eliminación en perfil
        add_action('show_user_profile',    [$this, 'render_avatar_field']);
        add_action('edit_user_profile',    [$this, 'render_avatar_field']);
        add_action('show_user_profile',    [$this, 'render_delete_checkbox']);
        add_action('edit_user_profile',    [$this, 'render_delete_checkbox']);

        // Guarda la subida o la eliminación
        add_action('personal_options_update',   [$this, 'save_avatar']);
        add_action('edit_user_profile_update',  [$this, 'save_avatar']);

        // Intercepta get_avatar()
        add_filter('get_avatar', [$this, 'override_avatar'], 10, 6);

        // Forzar enctype en el form
        add_action('admin_head-profile.php', [$this, 'fix_profile_form_enctype']);
        add_action('admin_head-user-edit.php', [$this, 'fix_profile_form_enctype']);
    }

    /**
     * Inyecta un pequeño script para poner enctype multipart/form-data
     */
    public function fix_profile_form_enctype()
    {
?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var form = document.getElementById('your-profile');
                if (form) {
                    form.setAttribute('enctype', 'multipart/form-data');
                }
            });
        </script>
    <?php
    }

    public function render_avatar_field($user)
    {
        $attach_id = get_user_meta($user->ID, 'custom_avatar_id', true);
    ?>
        <h2>Avatar personalizado</h2>
        <table class="form-table">
            <tr>
                <th><label for="custom_avatar">Subir avatar</label></th>
                <td>
                    <?php if ($attach_id) {
                        echo wp_get_attachment_image($attach_id, [96, 96], false, ['style' => 'border-radius:50%;']);
                        echo '<br>';
                    } ?>
                    <input type="file" name="custom_avatar" id="custom_avatar" /><br>
                    <span class="description">JPG/PNG, máximo 200×200px</span>
                </td>
            </tr>
        </table>
        <?php
    }

    public function render_delete_checkbox($user)
    {
        $attach_id = get_user_meta($user->ID, 'custom_avatar_id', true);
        if ($attach_id) {
        ?>
            <h3>Eliminar avatar</h3>
            <table class="form-table">
                <tr>
                    <th></th>
                    <td>
                        <label>
                            <input type="checkbox" name="custom_avatar_delete" value="1" />
                            Eliminar mi avatar
                        </label>
                    </td>
                </tr>
            </table>
<?php
        }
    }

    public function save_avatar($user_id)
    {
        if (! current_user_can('edit_user', $user_id)) {
            return;
        }

        // Subida nueva
        if (! empty($_FILES['custom_avatar']['name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attach_id = media_handle_upload('custom_avatar', 0);
            if (! is_wp_error($attach_id)) {
                // Borra el anterior
                $old = get_user_meta($user_id, 'custom_avatar_id', true);
                if ($old) {
                    wp_delete_attachment($old, true);
                }
                update_user_meta($user_id, 'custom_avatar_id', $attach_id);
            }
        }

        // Eliminación
        if (! empty($_POST['custom_avatar_delete'])) {
            $old = get_user_meta($user_id, 'custom_avatar_id', true);
            if ($old) {
                wp_delete_attachment($old, true);
                delete_user_meta($user_id, 'custom_avatar_id');
            }
        }
    }

    /**
     * Sustituye el avatar por el personalizado (si existe),
     * generando srcset/sizes para retina.
     */
    public function override_avatar($avatar, $id_or_email, $size, $default, $alt, $args)
    {
        $user = false;
        if (is_numeric($id_or_email)) {
            $user = get_user_by('id', absint($id_or_email));
        } elseif (is_object($id_or_email) && ! empty($id_or_email->user_id)) {
            $user = get_user_by('id', absint($id_or_email->user_id));
        } elseif (is_email($id_or_email)) {
            $user = get_user_by('email', $id_or_email);
        }

        if ($user) {
            $attach_id = get_user_meta($user->ID, 'custom_avatar_id', true);
            if ($attach_id) {
                $src    = wp_get_attachment_image_url($attach_id, [$size, $size]);
                $srcset = wp_get_attachment_image_srcset($attach_id, [$size, $size]);
                $sizes  = esc_attr("{$size}px");
                if ($src) {
                    return sprintf(
                        "<img alt='%s' src='%s' srcset='%s' sizes='%s' class='avatar avatar-%d photo' height='%d' width='%d' />",
                        esc_attr($alt),
                        esc_url($src),
                        esc_attr($srcset),
                        $sizes,
                        (int) $size,
                        (int) $size,
                        (int) $size
                    );
                }
            }
        }

        return $avatar;
    }
}
