<?php
// classes/Customizer.php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class E360VO_Customizer
{

    public function __construct()
    {
        // Hook para registrar opciones en el personalizador
        add_action('customize_register', [$this, 'register_customizer_options']);

        // Hook para añadir clases al body
        add_filter('body_class', [$this, 'add_color_scheme_class']);

        // Encolar hojas de estilo (admin & login)
        // add_action('admin_enqueue_scripts', [$this, 'enqueue_color_scheme_styles']);
        // add_action('login_enqueue_scripts', [$this, 'enqueue_color_scheme_styles']);
    }

    /**
     * Registrar ajustes y controles en el Customizer
     */
    public function register_customizer_options($wp_customize)
    {
        // Ajuste para "forma del logo" (o proporción del logo)
        $wp_customize->add_setting('th360_logo_shape', [
            'default'   => 'square',
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control(
            new E360VO_ImageSelectControl(
                $wp_customize,
                'th360_logo_shape',
                [
                    'label'       => __('Proporción del Logo', '360vo-theme'),
                    'description' => __('Selecciona la forma aproximada del logo.', '360vo-theme'),
                    'section'     => 'title_tagline',
                    'choices'     => [
                        'square' => [
                            'label' => __('Square (1:1)', '360vo-theme'),
                            // ancho y alto iguales
                            'style' => 'width: 40px; height: 40px; border: 2px solid #333;',
                        ],
                        'wide2' => [
                            'label' => __('Wide (2:1)', '360vo-theme'),
                            // 2:1 => 60x30, por ejemplo
                            'style' => 'width: 60px; height: 30px; border: 2px solid #333;',
                        ],
                        'wide3' => [
                            'label' => __('Panoramic (3:1)', '360vo-theme'),
                            // 3:1 => 90x30
                            'style' => 'width: 90px; height: 30px; border: 2px solid #333;',
                        ],
                        'wide4' => [
                            'label' => __('Cinematic (4:1)', '360vo-theme'),
                            // 4:1 => 120x30
                            'style' => 'width: 120px; height: 30px; border: 2px solid #333;',
                        ],
                        'wide5' => [
                            'label' => __('Ultra Wide (5:1)', '360vo-theme'),
                            // 5:1 => 150x30
                            'style' => 'width: 150px; height: 30px; border: 2px solid #333;',
                        ],
                        'wide6' => [
                            'label' => __('Mega Wide (6:1)', '360vo-theme'),
                            // 6:1 => 180x30
                            'style' => 'width: 180px; height: 30px; border: 2px solid #333;',
                        ],
                        'wide9' => [
                            'label' => __('Extreme Wide (9:1)', '360vo-theme'),
                            // 9:1 => 270x30
                            'style' => 'width: 270px; height: 30px; border: 2px solid #333;',
                        ],
                    ],
                ]
            )
        );

        /**
         * Sección Esquema de Color
         */
        $wp_customize->add_section('th360_color_scheme_section', [
            'title'    => __('Esquema de color', '360vo-theme'),
            'priority' => 30,
        ]);

        // Ajuste esquema de color
        $wp_customize->add_setting('th360_color_scheme', [
            'default'   => 'azul',
            'transport' => 'refresh',
        ]);

        // Control esquema de color
        $wp_customize->add_control('th360_color_scheme', [
            'label'    => __('Esquema de color', '360vo-theme'),
            'section'  => 'th360_color_scheme_section',
            'type'     => 'radio',
            'choices'  => [
                'azul'          => __('Azul', '360vo-theme'),
                'rojo'          => __('Rojo', '360vo-theme'),
                'verde'         => __('Verde', '360vo-theme'),
                'morado'        => __('Morado', '360vo-theme'),
                'naranja'       => __('Naranja', '360vo-theme'),
                'cian'          => __('Cian', '360vo-theme'),
                'lima'          => __('Lima', '360vo-theme'),
                'personalizado' => __('Personalizar', '360vo-theme'),
            ],
        ]);

        // Ajuste color base para "Personalizar"
        $wp_customize->add_setting('th360_custom_color', [
            'default'           => '#2e61ff', // por si entra en modo personalizado sin escoger color
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);

        $wp_customize->add_control(new WP_Customize_Color_Control(
            $wp_customize,
            'th360_custom_color',
            [
                'label'       => __('Color base (Personalizar)', '360vo-theme'),
                'description' => __('Elige el color base para generar la paleta primaria.', '360vo-theme'),
                'section'     => 'th360_color_scheme_section',
                'settings'    => 'th360_custom_color',
            ]
        ));

        // Ajuste tema (claro/oscuro)
        $wp_customize->add_setting('th360_theme', [
            'default'   => 'light',
            'transport' => 'refresh',
        ]);

        // Control tema
        $wp_customize->add_control('th360_theme', [
            'label'    => __('Tema', '360vo-theme'),
            'section'  => 'th360_color_scheme_section',
            'type'     => 'radio',
            'choices'  => [
                'light' => __('Claro', '360vo-theme'),
                'dark'  => __('Oscuro', '360vo-theme'),
            ],
        ]);

        // Ajuste encabezado (claro / oscuro / color principal)
        $wp_customize->add_setting('th360_header', [
            'default'   => 'claro',
            'transport' => 'refresh',
        ]);

        // Control encabezado
        $wp_customize->add_control('th360_header', [
            'label'    => __('Encabezado', '360vo-theme'),
            'section'  => 'th360_color_scheme_section',
            'type'     => 'radio',
            'choices'  => [
                'claro'           => __('Claro', '360vo-theme'),
                'oscuro'          => __('Oscuro', '360vo-theme'),
                'color_principal' => __('Color principal', '360vo-theme'),
            ],
        ]);

        /**
         * Ajuste de tamaño de logo (opcional)
         * Supongamos que quieres un boolean para "modo automático" vs "manual".
         * Luego en tu header puedes hacer la lógica para asignar clases al <img>.
         */
        $wp_customize->add_setting('th360_logo_size_auto', [
            'default'   => true, // Por defecto, automático
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('th360_logo_size_auto', [
            'type'        => 'checkbox',
            'section'     => 'title_tagline', // o crea otra sección si prefieres
            'label'       => __('Tamaño de logo automático', '360vo-theme'),
            'description' => __('Si se marca, el tema asignará clases según la proporción del logo.', '360vo-theme'),
        ]);
    }

    /**
     * Añade clases al body, por ejemplo para el esquema de color
     */
    public function add_color_scheme_class($classes)
    {
        $color_scheme = get_theme_mod('th360_color_scheme', 'azul');
        $theme        = get_theme_mod('th360_theme', 'light');
        $header_class = get_theme_mod('th360_header', 'claro');

        // Siguiendo tu idea BEM: esquema-*, theme-*, header-*
        $classes[] = 'esquema-' . $color_scheme;
        $classes[] = 'theme-' . $theme;
        $classes[] = 'header-' . $header_class;

        return $classes;
    }

    /**
     * Encolar la hoja de estilo según tema y color (si las usas)
     * Nota: tu paleta se imprime inline desde ColorPalette, esto es opcional.
     */
    public function enqueue_color_scheme_styles()
    {
        $color_scheme = get_theme_mod('th360_color_scheme', 'azul');
        $theme        = get_theme_mod('th360_theme', 'light');

        // Ejemplo: paleta_light_azul.css, paleta_dark_azul.css, etc.
        $handle     = 'paleta_' . $theme . '_' . $color_scheme;
        $style_path = get_template_directory_uri() . '/includes/assets/css/' . $handle . '.css';

        wp_enqueue_style('paleta_colores', $style_path, [], null);
    }
}
