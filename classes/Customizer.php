<?php
// classes/Customizer.php
if (!defined('ABSPATH')) {
    exit;
}

class E360VO_Customizer
{
    public function __construct()
    {
        add_action('customize_register', [$this, 'register_customizer_options']);
        add_filter('body_class', [$this, 'add_color_scheme_class']);
    }

    public function register_customizer_options($wp_customize)
    {
        $wp_customize->add_setting('th360_logo_shape', [
            'default'   => 'square',
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control(
            new E360VO_ImageSelectControl(
                $wp_customize,
                'th360_logo_shape',
                [
                    'label'       => __('Proporcion del Logo', '360vo-theme'),
                    'description' => __('Selecciona la forma aproximada del logo.', '360vo-theme'),
                    'section'     => 'title_tagline',
                    'choices'     => [
                        'square' => [
                            'label' => __('Square (1:1)', '360vo-theme'),
                            'style' => 'width: 40px; height: 40px; border: 2px solid #333;',
                        ],
                        'wide2' => [
                            'label' => __('Wide (2:1)', '360vo-theme'),
                            'style' => 'width: 60px; height: 30px; border: 2px solid #333;',
                        ],
                        'wide3' => [
                            'label' => __('Panoramic (3:1)', '360vo-theme'),
                            'style' => 'width: 90px; height: 30px; border: 2px solid #333;',
                        ],
                        'wide4' => [
                            'label' => __('Cinematic (4:1)', '360vo-theme'),
                            'style' => 'width: 120px; height: 30px; border: 2px solid #333;',
                        ],
                        'wide5' => [
                            'label' => __('Ultra Wide (5:1)', '360vo-theme'),
                            'style' => 'width: 150px; height: 30px; border: 2px solid #333;',
                        ],
                        'wide6' => [
                            'label' => __('Mega Wide (6:1)', '360vo-theme'),
                            'style' => 'width: 180px; height: 30px; border: 2px solid #333;',
                        ],
                        'wide9' => [
                            'label' => __('Extreme Wide (9:1)', '360vo-theme'),
                            'style' => 'width: 270px; height: 30px; border: 2px solid #333;',
                        ],
                    ],
                ]
            )
        );

        $wp_customize->add_section('th360_color_scheme_section', [
            'title'    => __('Esquema de color', '360vo-theme'),
            'priority' => 30,
        ]);

        $wp_customize->add_setting('th360_color_scheme', [
            'default'           => 'azul',
            'transport'         => 'refresh',
            'sanitize_callback' => [$this, 'sanitize_color_scheme'],
        ]);

        $wp_customize->add_control('th360_color_scheme', [
            'label'   => __('Esquema de color', '360vo-theme'),
            'section' => 'th360_color_scheme_section',
            'type'    => 'radio',
            'choices' => [
                'azul'          => __('Azul', '360vo-theme'),
                'amarillo'      => __('Amarillo', '360vo-theme'),
                'rojo'          => __('Rojo', '360vo-theme'),
                'verde'         => __('Verde', '360vo-theme'),
                'morado'        => __('Morado', '360vo-theme'),
                'naranja'       => __('Naranja', '360vo-theme'),
                'cian'          => __('Cian', '360vo-theme'),
                'lima'          => __('Lima', '360vo-theme'),
                'personalizado' => __('Personalizar', '360vo-theme'),
            ],
        ]);

        $wp_customize->add_setting('th360_custom_color', [
            'default'           => '#2e61ff',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);

        $wp_customize->add_control(new WP_Customize_Color_Control(
            $wp_customize,
            'th360_custom_color',
            [
                'label'           => __('Color base (Personalizar)', '360vo-theme'),
                'description'     => __('Elige el color base para generar la paleta primaria.', '360vo-theme'),
                'section'         => 'th360_color_scheme_section',
                'settings'        => 'th360_custom_color',
                'active_callback' => [$this, 'is_custom_scheme_selected'],
            ]
        ));

        $wp_customize->add_setting('th360_use_advanced_palette', [
            'default'           => false,
            'transport'         => 'refresh',
            'sanitize_callback' => [$this, 'sanitize_checkbox'],
        ]);

        $wp_customize->add_control('th360_use_advanced_palette', [
            'type'        => 'checkbox',
            'section'     => 'th360_color_scheme_section',
            'label'       => __('Activar ajustes avanzados de color', '360vo-theme'),
            'description' => __('Permite ajustar fondos/neutrales, color de accion y el encabezado oscuro sin romper el sistema actual.', '360vo-theme'),
        ]);

        $wp_customize->add_setting('th360_custom_neutral_color', [
            'default'           => '',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);

        $wp_customize->add_control(new WP_Customize_Color_Control(
            $wp_customize,
            'th360_custom_neutral_color',
            [
                'label'           => __('Color fondos / neutrales', '360vo-theme'),
                'description'     => __('Usalo para sesgar los neutros hacia asfalto, tierra, marron, gris calido, etc. El sistema lo suaviza para que no domine.', '360vo-theme'),
                'section'         => 'th360_color_scheme_section',
                'settings'        => 'th360_custom_neutral_color',
                'active_callback' => [$this, 'is_advanced_palette_enabled'],
            ]
        ));

        $wp_customize->add_setting('th360_use_custom_action_color', [
            'default'           => false,
            'transport'         => 'refresh',
            'sanitize_callback' => [$this, 'sanitize_checkbox'],
        ]);

        $wp_customize->add_control('th360_use_custom_action_color', [
            'type'            => 'checkbox',
            'section'         => 'th360_color_scheme_section',
            'label'           => __('Usar color de accion personalizado', '360vo-theme'),
            'description'     => __('Si no lo activas, el sistema usa una armonia automatica y realista segun el esquema elegido.', '360vo-theme'),
            'active_callback' => [$this, 'is_advanced_palette_enabled'],
        ]);

        $wp_customize->add_setting('th360_custom_action_color', [
            'default'           => '#44664e',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);

        $wp_customize->add_control(new WP_Customize_Color_Control(
            $wp_customize,
            'th360_custom_action_color',
            [
                'label'           => __('Color de accion', '360vo-theme'),
                'description'     => __('Color principal para botones destacados, CTAs, hover fuertes y estados activos.', '360vo-theme'),
                'section'         => 'th360_color_scheme_section',
                'settings'        => 'th360_custom_action_color',
                'active_callback' => [$this, 'is_custom_action_color_enabled'],
            ]
        ));

        $wp_customize->add_setting('th360_use_custom_dark_header', [
            'default'           => false,
            'transport'         => 'refresh',
            'sanitize_callback' => [$this, 'sanitize_checkbox'],
        ]);

        $wp_customize->add_control('th360_use_custom_dark_header', [
            'type'            => 'checkbox',
            'section'         => 'th360_color_scheme_section',
            'label'           => __('Personalizar encabezado oscuro', '360vo-theme'),
            'description'     => __('Activa colores especificos para header y migas cuando el encabezado esta en modo oscuro.', '360vo-theme'),
            'active_callback' => [$this, 'is_advanced_palette_enabled'],
        ]);

        $wp_customize->add_setting('th360_header_dark_bg', [
            'default'           => '#100e09',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);

        $wp_customize->add_control(new WP_Customize_Color_Control(
            $wp_customize,
            'th360_header_dark_bg',
            [
                'label'           => __('Fondo encabezado oscuro', '360vo-theme'),
                'section'         => 'th360_color_scheme_section',
                'settings'        => 'th360_header_dark_bg',
                'active_callback' => [$this, 'is_custom_dark_header_enabled'],
            ]
        ));

        $wp_customize->add_setting('th360_header_dark_text', [
            'default'           => '#c9a900',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);

        $wp_customize->add_control(new WP_Customize_Color_Control(
            $wp_customize,
            'th360_header_dark_text',
            [
                'label'           => __('Texto encabezado oscuro', '360vo-theme'),
                'section'         => 'th360_color_scheme_section',
                'settings'        => 'th360_header_dark_text',
                'active_callback' => [$this, 'is_custom_dark_header_enabled'],
            ]
        ));

        $wp_customize->add_setting('th360_header_dark_accent', [
            'default'           => '#e9c400',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);

        $wp_customize->add_control(new WP_Customize_Color_Control(
            $wp_customize,
            'th360_header_dark_accent',
            [
                'label'           => __('Acento encabezado oscuro', '360vo-theme'),
                'description'     => __('Hover, estados activos y elementos destacados del header oscuro.', '360vo-theme'),
                'section'         => 'th360_color_scheme_section',
                'settings'        => 'th360_header_dark_accent',
                'active_callback' => [$this, 'is_custom_dark_header_enabled'],
            ]
        ));

        $wp_customize->add_setting('th360_header_dark_button_bg', [
            'default'           => '#221b00',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);

        $wp_customize->add_control(new WP_Customize_Color_Control(
            $wp_customize,
            'th360_header_dark_button_bg',
            [
                'label'           => __('Fondo botones encabezado oscuro', '360vo-theme'),
                'section'         => 'th360_color_scheme_section',
                'settings'        => 'th360_header_dark_button_bg',
                'active_callback' => [$this, 'is_custom_dark_header_enabled'],
            ]
        ));

        $wp_customize->add_setting('th360_header_dark_breadcrumb_bg', [
            'default'           => '#1d1b16',
            'transport'         => 'refresh',
            'sanitize_callback' => 'sanitize_hex_color',
        ]);

        $wp_customize->add_control(new WP_Customize_Color_Control(
            $wp_customize,
            'th360_header_dark_breadcrumb_bg',
            [
                'label'           => __('Fondo migas en header oscuro', '360vo-theme'),
                'section'         => 'th360_color_scheme_section',
                'settings'        => 'th360_header_dark_breadcrumb_bg',
                'active_callback' => [$this, 'is_custom_dark_header_enabled'],
            ]
        ));

        $wp_customize->add_setting('th360_theme', [
            'default'           => 'light',
            'transport'         => 'refresh',
            'sanitize_callback' => [$this, 'sanitize_theme'],
        ]);

        $wp_customize->add_control('th360_theme', [
            'label'   => __('Tema', '360vo-theme'),
            'section' => 'th360_color_scheme_section',
            'type'    => 'radio',
            'choices' => [
                'light' => __('Claro', '360vo-theme'),
                'dark'  => __('Oscuro', '360vo-theme'),
            ],
        ]);

        $wp_customize->add_setting('th360_header', [
            'default'           => 'claro',
            'transport'         => 'refresh',
            'sanitize_callback' => [$this, 'sanitize_header'],
        ]);

        $wp_customize->add_control('th360_header', [
            'label'   => __('Encabezado', '360vo-theme'),
            'section' => 'th360_color_scheme_section',
            'type'    => 'radio',
            'choices' => [
                'claro'           => __('Claro', '360vo-theme'),
                'oscuro'          => __('Oscuro', '360vo-theme'),
                'color_principal' => __('Color principal', '360vo-theme'),
            ],
        ]);

        $wp_customize->add_setting('th360_logo_size_auto', [
            'default'   => true,
            'transport' => 'refresh',
        ]);

        $wp_customize->add_control('th360_logo_size_auto', [
            'type'        => 'checkbox',
            'section'     => 'title_tagline',
            'label'       => __('Tamano de logo automatico', '360vo-theme'),
            'description' => __('Si se marca, el tema asignara clases segun la proporcion del logo.', '360vo-theme'),
        ]);
    }

    public function add_color_scheme_class($classes)
    {
        $color_scheme = get_theme_mod('th360_color_scheme', 'azul');
        $theme        = get_theme_mod('th360_theme', 'light');
        $header_class = get_theme_mod('th360_header', 'claro');

        $classes[] = 'esquema-' . $color_scheme;
        $classes[] = 'theme-' . $theme;
        $classes[] = 'header-' . $header_class;

        return $classes;
    }

    public function enqueue_color_scheme_styles()
    {
        $color_scheme = get_theme_mod('th360_color_scheme', 'azul');
        $theme        = get_theme_mod('th360_theme', 'light');

        $handle     = 'paleta_' . $theme . '_' . $color_scheme;
        $style_path = get_template_directory_uri() . '/includes/assets/css/' . $handle . '.css';

        wp_enqueue_style('paleta_colores', $style_path, [], null);
    }

    public function sanitize_checkbox($checked)
    {
        return (bool) $checked;
    }

    public function sanitize_color_scheme($value)
    {
        return $this->sanitize_choice($value, [
            'azul',
            'amarillo',
            'rojo',
            'verde',
            'morado',
            'naranja',
            'cian',
            'lima',
            'personalizado',
        ], 'azul');
    }

    public function sanitize_theme($value)
    {
        return $this->sanitize_choice($value, ['light', 'dark'], 'light');
    }

    public function sanitize_header($value)
    {
        return $this->sanitize_choice($value, ['claro', 'oscuro', 'color_principal'], 'claro');
    }

    protected function sanitize_choice($value, array $choices, $default)
    {
        $value = is_string($value) ? sanitize_key($value) : $default;
        return in_array($value, $choices, true) ? $value : $default;
    }

    public function is_custom_scheme_selected($control)
    {
        return 'personalizado' === $control->manager->get_setting('th360_color_scheme')->value();
    }

    public function is_advanced_palette_enabled($control)
    {
        return (bool) $control->manager->get_setting('th360_use_advanced_palette')->value();
    }

    public function is_custom_action_color_enabled($control)
    {
        return $this->is_advanced_palette_enabled($control)
            && (bool) $control->manager->get_setting('th360_use_custom_action_color')->value();
    }

    public function is_custom_dark_header_enabled($control)
    {
        return $this->is_advanced_palette_enabled($control)
            && (bool) $control->manager->get_setting('th360_use_custom_dark_header')->value();
    }
}
