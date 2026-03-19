<?php
/**
 * Funciones para el backend
 *
 * @package 360vo-theme

 */

 if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}







// Permitir archivos SVG
function my_custom_mime_types( $mimes ) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'my_custom_mime_types' );

// Cambiar el logo en la página de inicio de sesión
function my_login_logo() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/site-login-logo.png);
            height:65px;
            width:320px;
            background-size: 320px 65px;
            background-repeat: no-repeat;
            padding-bottom: 30px;
        }
    </style>
<?php }
add_action( 'login_enqueue_scripts', 'my_login_logo' );

// Cambiar el enlace del logo en la página de inicio de sesión
function my_login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

//Cambiamos texto Gracias por usar WordPress
function my_admin_footer_text( $text ) {
    $text = sprintf( __( 'Desarrollado por <a href="%s">360vo</a>' ), 'https://360vo.es' );
    return $text;
}
add_filter( 'admin_footer_text', 'my_admin_footer_text' );

//Quitamos versión de WordPress
function my_update_footer( $content ) {
    return '';
}
add_filter( 'update_footer', 'my_update_footer', 9999 );

function my_remove_dashboard_widgets() {
    remove_action( 'welcome_panel', 'wp_welcome_panel' );
    remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
}
add_action( 'wp_dashboard_setup', 'my_remove_dashboard_widgets' );




//Widget datos de contacto en escritorio
// Función que muestra el contenido del widget


// Función que muestra el contenido del widget
// Función que muestra el contenido del widget

// Convertirlo en clases y ver si lo metemos en el plugin
function mi_widget_de_contacto()
{

    // Obtener la URL del logo del sitio
     $custom_logo_id = get_theme_mod('custom_logo');
      $logo = wp_get_attachment_image_src($custom_logo_id , 'full');

    // Obtener los datos de contacto desde ACF
    $telefono_principal = get_field('correo_y_telefono_telefono_principal', 'option');
    $correo_principal = get_field('correo_y_telefono_correo_principal', 'option');
    $whatsapp = get_field('whatsapp', 'option');

    // Obtener redes sociales desde ACF
    $facebook = get_field('social_face', 'option');
    $twitter = get_field('social_twitter', 'option');
    $instagram = get_field('social_insta', 'option');
    $youtube = get_field('social_youtube', 'option');
    $tiktok = get_field('social_tiktok', 'option');

    // Obtener el horario de ACF
    $horario = get_field('horario', 'option');
    $conf_horario = $horario['conf_horario'];
    $horario_alt = $conf_horario['horario_alt'];

    if (!$horario_alt) {
        // Horario de lunes a viernes
        $lunes_a_viernes = $horario['lunes_a_viernes'];
        $horario_sabado = $horario['horario_sabado'];
        $horario_domingo = $horario['horario_domingo'];

        // Lunes a viernes
        $entrada_lun_vier = $lunes_a_viernes['entrada_lun_vier'];
        $salida_lun_vier = $lunes_a_viernes['salida_lun_vier'];
        $horario_tarde = $lunes_a_viernes['horario_tarde'];
        $entrada_lun_vier_tarde = $lunes_a_viernes['entrada_lun_vier_tarde'];
        $salida_lun_vier_tarde = $lunes_a_viernes['salida_lun_vier_tarde'];

        // Sábado
        $abierto_sabados = $horario_sabado['abierto_sabados'];
        $entrada_sab = $horario_sabado['entrada_sab'];
        $salida_sab = $horario_sabado['salida_sab'];
        $horario_tarde_sab = $horario_sabado['horario_tarde_sab'];
        $entrada_sab_tarde = $horario_sabado['entrada_sab_tarde'];
        $salida_sab_tarde = $horario_sabado['salida_sab_tarde'];

        // Domingo
        $abierto_domingos = $horario_domingo['abierto_domingos'];
        $entrada_dom = $horario_domingo['entrada_dom'];
        $salida_dom = $horario_domingo['salida_dom'];
        $horario_tarde_dom = $horario_domingo['horario_tarde_dom'];
        $entrada_dom_tarde = $horario_domingo['entrada_dom_tarde'];
        $salida_dom_tarde = $horario_domingo['salida_dom_tarde'];

        // Generar horarios
        $lunes_viernes_horarios = "Lunes a Viernes: {$entrada_lun_vier} - {$salida_lun_vier}";
        if ($horario_tarde) {
            $lunes_viernes_horarios .= ", {$entrada_lun_vier_tarde} - {$salida_lun_vier_tarde}";
        }

        $sabado_horarios = "";
        if ($abierto_sabados) {
            $sabado_horarios .= "Sábado: {$entrada_sab} - {$salida_sab}";
            if ($horario_tarde_sab) {
                $sabado_horarios .= ", {$entrada_sab_tarde} - {$salida_sab_tarde}";
            }
        }

        $domingo_horarios = "";
        if ($abierto_domingos) {
            $domingo_horarios .= "Domingo: {$entrada_dom} - {$salida_dom}";
            if ($horario_tarde_dom) {
                $domingo_horarios .= ", {$entrada_dom_tarde} - {$salida_dom_tarde}";
            }
        }

        // Unir horarios
        $horarios_array = array($lunes_viernes_horarios);
        if (!empty($sabado_horarios)) {
            array_push($horarios_array, $sabado_horarios);
        }
        if (!empty($domingo_horarios)) {
            array_push($horarios_array, $domingo_horarios);
        }
        $horario_comercio = '';
        foreach ($horarios_array as $horario) {
            $horario_comercio .= '<p class="horario__dias">' . $horario . '</p>';
        }
    } else {
        //Horario alternativo (días y horas por separado)
        $horario_comercio = '<p class="horario__dias">Horario alternativo disponible</p>';
    }
    if (has_custom_logo()) {
        echo '<div class="site-logo">';
        echo '<img src="' . esc_url($logo[0]) . '" alt="' . get_bloginfo('name') . '">';
        echo '</div>';
    }
    echo '<div class="postbox">';
  
    echo '<div class="inside">';
    if (!empty($telefono_principal)) {
        echo '<p><strong>Teléfono principal:</strong> ' . esc_html($telefono_principal) . '</p>';
    }
    if (!empty($correo_principal)) {
        echo '<p><strong>Correo electrónico:</strong> ' . esc_html($correo_principal) . '</p>';
    }
    if (!empty($whatsapp)) {
        echo '<p><strong>WhatsApp:</strong> ' . esc_html($whatsapp) . '</p>';
    }
    echo $horario_comercio;
    echo '<ul class="postbox_contacto__ul">';
    if (!empty($facebook)) {
        echo '<li><a href="https://www.facebook.com/' . esc_html(strtolower($facebook)) . '" target="_blank"><i class="icon-facebook"></i> ' . esc_html($facebook) . '</a></li>';
    }
    if (!empty($twitter)) {
        echo '<li><a href="https://www.twitter.com/' . esc_html(strtolower($twitter)) . '" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" height="16" width="16" viewBox="0 0 512 512"><path opacity="1" fill="#1E3050" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"></path></svg> ' . esc_html($twitter) . '</a></li>';
    }
    if (!empty($instagram)) {
        echo '<li><a href="https://www.instagram.com/' . esc_html(strtolower($instagram)) . '" target="_blank"><i class="icon-instagram"></i> ' . esc_html($instagram) . '</a></li>';
    }
    if (!empty($youtube)) {
        echo '<li><a href="https://www.youtube.com/' . esc_html(strtolower($youtube)) . '" target="_blank"><svg aria-hidden="true" height="16" width="16" viewBox="0 0 18 18"><path d="M7.2,11.6V6.4L12,9.1L7.2,11.6z M17.8,5.3c0,0-0.2-1.2-0.7-1.8c-0.7-0.7-1.4-0.7-1.8-0.8C12.8,2.6,9,2.6,9,2.6 s-3.8,0-6.3,0.2c-0.3,0-1.1,0-1.8,0.8C0.4,4.1,0.2,5.3,0.2,5.3S0,6.8,0,8.2v1.5c0,1.5,0.2,2.9,0.2,2.9s0.2,1.2,0.7,1.8 c0.7,0.7,1.6,0.7,2,0.8c1.4,0.1,5.9,0.2,6.1,0.2c0,0,3.8,0,6.3-0.2c0.3,0,1.1,0,1.8-0.8c0.5-0.5,0.7-1.8,0.7-1.8S18,11.2,18,9.8V8.2 C18,6.8,17.8,5.3,17.8,5.3z"></path></svg> ' . esc_html($youtube) . '</a></li>';
    }
    if (!empty($tiktok)) {
        echo '<li><a href="https://www.tiktok.com/' . esc_html(strtolower($tiktok)) . '" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" width="20px" height="20px"><path d="M41,4H9C6.243,4,4,6.243,4,9v32c0,2.757,2.243,5,5,5h32c2.757,0,5-2.243,5-5V9C46,6.243,43.757,4,41,4z M37.006,22.323 c-0.227,0.021-0.457,0.035-0.69,0.035c-2.623,0-4.928-1.349-6.269-3.388c0,5.349,0,11.435,0,11.537c0,4.709-3.818,8.527-8.527,8.527 s-8.527-3.818-8.527-8.527s3.818-8.527,8.527-8.527c0.178,0,0.352,0.016,0.527,0.027v4.202c-0.175-0.021-0.347-0.053-0.527-0.053 c-2.404,0-4.352,1.948-4.352,4.352s1.948,4.352,4.352,4.352s4.527-1.894,4.527-4.298c0-0.095,0.042-19.594,0.042-19.594h4.016 c0.378,3.591,3.277,6.425,6.901,6.685V22.323z"></path></svg> ' . esc_html($tiktok) . '</a></li>';
    }
    // Repetidor de ACF para redes sociales adicionales
    if (have_rows('add_social', 'option')) {
        while (have_rows('add_social', 'option')) {
            the_row();
            $social_network_name = get_sub_field('nombre_rs');
            $social_network_link = get_sub_field('link_red_social');
            $social_network_icon = get_sub_field('icono_red_social');
            if (!empty($social_network_icon)) {
                echo '<li><a href="' . esc_url($social_network_link) . '" target="_blank"><img src="' . esc_url($social_network_icon) . '" alt="' . esc_attr($social_network_name) . '" width="18" height="18"> ' . esc_html($social_network_name) . '</a></li>';
            } else {
                echo '<li><a href="' . esc_url($social_network_link) . '" target="_blank">' . esc_html($social_network_name) . '</a></li>';
            }
        }
    }
    echo '</ul>';
    echo '<p><a href="' . admin_url('admin.php?page=datos_de_contacto') . '" class="button button-primary">Editar datos de contacto</a></p>';
    echo '</div>'; // Cierra div.inside
    echo '</div>'; // Cierra div.postbox
}


// Función que registra el widget
function registrar_mi_widget_de_contacto()
{
    wp_add_dashboard_widget(
        'mi_widget_de_contacto', // ID del widget
        'Información de contacto', // Título del widget
        'mi_widget_de_contacto' // Función que muestra el contenido del widget
    );
}
add_action('wp_dashboard_setup', 'registrar_mi_widget_de_contacto');

