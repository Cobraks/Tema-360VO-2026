<?php
// classes/ColorPalette.php
if (!defined('ABSPATH')) {
    exit;
}

class E360VO_ColorPalette
{
    protected static $bases = [
        'azul' => [
            '--primary5'   => 'hsla(225, 100%, 8%, 1)',
            '--primary10'  => 'hsla(225, 100%, 12%, 1)',
            '--primary15'  => 'hsla(225, 100%, 16%, 1)',
            '--primary20'  => 'hsla(225, 100%, 20%, 1)',
            '--primary25'  => 'hsla(225, 95%, 25%, 1)',
            '--primary30'  => 'hsla(225, 90%, 30%, 1)',
            '--primary35'  => 'hsla(225, 85%, 35%, 1)',
            '--primary40'  => 'hsla(225, 80%, 40%, 1)',
            '--primary45'  => 'hsla(225, 75%, 45%, 1)',
            '--primary50'  => 'hsla(225, 70%, 50%, 1)',
            '--primary60'  => 'hsla(225, 65%, 60%, 1)',
            '--primary70'  => 'hsla(225, 60%, 70%, 1)',
            '--primary80'  => 'hsla(225, 50%, 80%, 1)',
            '--primary90'  => 'hsla(225, 30%, 90%, 1)',
            '--primary95'  => 'hsla(225, 15%, 95%, 1)',
            '--primary96'  => 'hsla(225, 10%, 96%, 1)',
            '--primary97'  => 'hsla(225, 8%, 97%, 1)',
            '--primary98'  => 'hsla(225, 5%, 98%, 1)',
            '--primary99'  => 'hsla(225, 2%, 99%, 1)',
            '--neutral5'   => 'hsla(45, 8%, 7%, 1)',
            '--neutral10'  => 'hsla(45, 6%, 12%, 1)',
            '--neutral15'  => 'hsla(45, 5%, 16%, 1)',
            '--neutral20'  => 'hsla(45, 4%, 20%, 1)',
            '--neutral25'  => 'hsla(45, 3.5%, 25%, 1)',
            '--neutral30'  => 'hsla(45, 3%, 30%, 1)',
            '--neutral35'  => 'hsla(45, 2.5%, 35%, 1)',
            '--neutral40'  => 'hsla(45, 2%, 40%, 1)',
            '--neutral45'  => 'hsla(45, 1.8%, 45%, 1)',
            '--neutral50'  => 'hsla(45, 1.6%, 50%, 1)',
            '--neutral60'  => 'hsla(45, 1.4%, 60%, 1)',
            '--neutral70'  => 'hsla(45, 1.2%, 70%, 1)',
            '--neutral80'  => 'hsla(45, 1%, 80%, 1)',
            '--neutral90'  => 'hsla(45, 12%, 90%, 1)',
            '--neutral95'  => 'hsla(45, 6%, 95%, 1)',
            '--neutral96'  => 'hsla(45, 4%, 96%, 1)',
            '--neutral97'  => 'hsla(45, 3%, 97%, 1)',
            '--neutral98'  => 'hsla(45, 2%, 98%, 1)',
            '--neutral99'  => 'hsla(45, 1%, 99%, 1)',
        ],
    ];

    protected static $colorConfigs = [
        'azul' => [
            'primary_hue'        => 225,
            'neutral_hue'        => 45,
            'accent_hue'         => 20,
            'accent_saturation'  => 85,
            'accent_lightness'   => 50,
        ],
        'amarillo' => [
            'primary_hue'        => 48,
            'neutral_hue'        => 38,
            'accent_hue'         => 164,
            'accent_saturation'  => 35,
            'accent_lightness'   => 47,
        ],
        'cian' => [
            'primary_hue'        => 190,
            'neutral_hue'        => 45,
            'accent_hue'         => 15,
            'accent_saturation'  => 80,
            'accent_lightness'   => 48,
        ],
        'rojo' => [
            'primary_hue'        => 0,
            'neutral_hue'        => 210,
            'accent_hue'         => 180,
            'accent_saturation'  => 75,
            'accent_lightness'   => 52,
        ],
        'verde' => [
            'primary_hue'        => 140,
            'neutral_hue'        => 330,
            'accent_hue'         => 320,
            'accent_saturation'  => 80,
            'accent_lightness'   => 50,
        ],
        'morado' => [
            'primary_hue'        => 285,
            'neutral_hue'        => 75,
            'accent_hue'         => 105,
            'accent_saturation'  => 75,
            'accent_lightness'   => 50,
        ],
        'naranja' => [
            'primary_hue'        => 30,
            'neutral_hue'        => 210,
            'accent_hue'         => 210,
            'accent_saturation'  => 80,
            'accent_lightness'   => 50,
        ],
        'lima' => [
            'primary_hue'        => 85,
            'neutral_hue'        => 285,
            'accent_hue'         => 275,
            'accent_saturation'  => 75,
            'accent_lightness'   => 50,
        ],
    ];

    protected static $palettes = [];
    protected static $cache = [];

    public function __construct()
    {
        add_action('init', [__CLASS__, 'build_predefined_palettes'], 1);
        add_action('wp_head', [$this, 'print_inline_palette'], 5);
    }

    public static function build_predefined_palettes()
    {
        $base = self::$bases['azul'];
        $base_primary = self::pick_vars($base, '--primary');
        $base_neutral = self::pick_vars($base, '--neutral');

        foreach (self::$colorConfigs as $slug => $config) {
            if ($slug === 'amarillo') {
                self::$palettes[$slug] = self::build_yellow_palette();
                continue;
            }

            $primary = self::generate_primary_palette($base_primary, $config['primary_hue']);
            $neutral = self::generate_neutral_palette($base_neutral, $config);
            $accent  = self::generate_manual_accent_palette($config);
            $aliases = self::build_compatibility_aliases($primary, $neutral, $accent);
            $tokens  = self::build_smart_semantic_tokens();

            self::$palettes[$slug] = array_merge($primary, $neutral, $accent, $aliases, $tokens);
        }
    }

    protected static function build_yellow_palette(): array
    {
        $palette = [
            '--primary0' => '#000000',
            '--primary4' => '#130e00',
            '--primary5' => '#161000',
            '--primary6' => '#191300',
            '--primary10' => '#221b00',
            '--primary12' => '#271f00',
            '--primary15' => '#2d2400',
            '--primary17' => '#332900',
            '--primary20' => '#3a3000',
            '--primary22' => '#3f3400',
            '--primary24' => '#443800',
            '--primary25' => '#473a00',
            '--primary30' => '#544600',
            '--primary35' => '#625100',
            '--primary40' => '#705d00',
            '--primary45' => '#7d6900',
            '--primary50' => '#8c7500',
            '--primary60' => '#aa8f00',
            '--primary70' => '#c9a900',
            '--primary80' => '#e9c400',
            '--primary90' => '#ffe16d',
            '--primary95' => '#fff0c2',
            '--primary96' => '#fff3d1',
            '--primary97' => '#fff6df',
            '--primary98' => '#fff9ef',
            '--primary99' => '#fffbff',
            '--secondary0' => '#000000',
            '--secondary4' => '#130e00',
            '--secondary5' => '#161000',
            '--secondary6' => '#181301',
            '--secondary10' => '#211b04',
            '--secondary12' => '#251f07',
            '--secondary17' => '#302a10',
            '--secondary20' => '#373016',
            '--secondary22' => '#3c341a',
            '--secondary24' => '#40391e',
            '--secondary25' => '#423b20',
            '--secondary30' => '#4e462a',
            '--secondary35' => '#5a5235',
            '--secondary40' => '#675e40',
            '--secondary50' => '#807757',
            '--secondary60' => '#9b906f',
            '--secondary70' => '#b6ab87',
            '--secondary80' => '#d2c6a1',
            '--secondary90' => '#efe2bc',
            '--secondary95' => '#fdf0c9',
            '--secondary96' => '#fff3d1',
            '--secondary97' => '#fff6df',
            '--secondary98' => '#fff9ef',
            '--secondary99' => '#fffbff',
            '--tertiary0' => '#000000',
            '--tertiary4' => '#001206',
            '--tertiary5' => '#001507',
            '--tertiary6' => '#001809',
            '--tertiary10' => '#00210e',
            '--tertiary12' => '#032512',
            '--tertiary17' => '#0e301c',
            '--tertiary20' => '#153722',
            '--tertiary22' => '#1a3b26',
            '--tertiary24' => '#1f402a',
            '--tertiary25' => '#21422c',
            '--tertiary30' => '#2d4e37',
            '--tertiary35' => '#385a42',
            '--tertiary40' => '#44664e',
            '--tertiary50' => '#5c7f65',
            '--tertiary60' => '#75997e',
            '--tertiary70' => '#8fb497',
            '--tertiary80' => '#aad0b2',
            '--tertiary90' => '#c6eccd',
            '--tertiary95' => '#d4fadb',
            '--tertiary96' => '#d6fddd',
            '--tertiary97' => '#dfffdf',
            '--tertiary98' => '#e9ffeb',
            '--tertiary99' => '#f5fff3',
            '--neutral0' => '#000000',
            '--neutral4' => '#100e09',
            '--neutral5' => '#12110c',
            '--neutral6' => '#15130e',
            '--neutral10' => '#1d1b16',
            '--neutral12' => '#211f1a',
            '--neutral17' => '#2c2a24',
            '--neutral20' => '#33302a',
            '--neutral22' => '#37352e',
            '--neutral24' => '#3b3933',
            '--neutral25' => '#3e3b35',
            '--neutral30' => '#494640',
            '--neutral35' => '#55524b',
            '--neutral40' => '#615e57',
            '--neutral45' => '#6d695f',
            '--neutral50' => '#7a776f',
            '--neutral60' => '#949088',
            '--neutral70' => '#afaba2',
            '--neutral80' => '#cbc6bd',
            '--neutral90' => '#e8e2d9',
            '--neutral95' => '#f6f0e7',
            '--neutral96' => '#f9f3ea',
            '--neutral97' => '#fcf6ed',
            '--neutral98' => '#fff9ef',
            '--neutral99' => '#fffbff',
            '--neutral-variant0' => '#000000',
            '--neutral-variant4' => '#110e05',
            '--neutral-variant5' => '#141107',
            '--neutral-variant6' => '#161309',
            '--neutral-variant10' => '#1f1b10',
            '--neutral-variant12' => '#231f14',
            '--neutral-variant17' => '#2d2a1e',
            '--neutral-variant20' => '#343024',
            '--neutral-variant22' => '#393528',
            '--neutral-variant24' => '#3d392c',
            '--neutral-variant25' => '#3f3b2e',
            '--neutral-variant30' => '#4b4739',
            '--neutral-variant35' => '#575244',
            '--neutral-variant40' => '#635e50',
            '--neutral-variant45' => '#6f6a5b',
            '--neutral-variant50' => '#7c7767',
            '--neutral-variant60' => '#979080',
            '--neutral-variant70' => '#b2ab9a',
            '--neutral-variant80' => '#cdc6b4',
            '--neutral-variant90' => '#eae2cf',
            '--neutral-variant95' => '#f8f0dd',
            '--neutral-variant96' => '#fbf3e0',
            '--neutral-variant97' => '#fdf6e6',
            '--neutral-variant98' => '#fff9ef',
            '--neutral-variant99' => '#fffbff',
            '--action30' => 'var(--tertiary30)',
            '--action40' => 'var(--tertiary40)',
            '--action50' => 'var(--tertiary50)',
            '--action60' => 'var(--tertiary60)',
            '--action90' => 'var(--tertiary90)',
            '--on-action' => '#ffffff',
            '--accent-text' => 'var(--tertiary40)',
        ];

        return array_merge($palette, self::build_smart_semantic_tokens());
    }

    protected static function generate_manual_accent_palette(array $config): array
    {
        $hue = $config['accent_hue'];
        $base_sat = $config['accent_saturation'];
        $base_light = $config['accent_lightness'];

        $lightness_map = [
            30 => $base_light - 20,
            40 => $base_light - 10,
            50 => $base_light,
            60 => $base_light + 10,
            90 => $base_light + 40,
        ];

        $saturation_map = [
            30 => $base_sat,
            40 => $base_sat + 5,
            50 => $base_sat + 10,
            60 => $base_sat + 5,
            90 => max(5, $base_sat - 40),
        ];

        if ((int) $config['primary_hue'] === 190) {
            $hue = 15;
            $lightness_map[30] = 35;
            $lightness_map[40] = 42;
            $lightness_map[50] = 48;
            $saturation_map[50] = 80;
        }

        if ((int) $config['primary_hue'] === 225) {
            $hue = 20;
            $lightness_map[40] = 42;
            $saturation_map[50] = 85;
        }

        return [
            '--action30'    => self::build_hsla($hue, $saturation_map[30], $lightness_map[30], 1),
            '--action40'    => self::build_hsla($hue, $saturation_map[40], $lightness_map[40], 1),
            '--action50'    => self::build_hsla($hue, $saturation_map[50], $lightness_map[50], 1),
            '--action60'    => self::build_hsla($hue, $saturation_map[60], $lightness_map[60], 1),
            '--action90'    => self::build_hsla($hue, $saturation_map[90], $lightness_map[90], 1),
            '--on-action'   => '#ffffff',
            '--accent-text' => self::build_hsla($hue, $saturation_map[40], $lightness_map[40], 1),
        ];
    }

    protected static function build_smart_semantic_tokens(): array
    {
        return [
            '--color-bg'           => 'var(--neutral99)',
            '--color-surface'      => 'var(--neutral97)',
            '--color-surface-2'    => 'var(--neutral95)',
            '--color-surface-3'    => 'var(--neutral90)',
            '--color-surface-4'    => 'var(--neutral80)',
            '--color-text'         => 'var(--neutral15)',
            '--color-text-2'       => 'var(--neutral30)',
            '--color-muted'        => 'var(--neutral50)',
            '--color-on-light'     => 'var(--neutral10)',
            '--color-on-dark'      => 'var(--neutral95)',
            '--color-border'       => 'var(--neutral90)',
            '--color-border-2'     => 'var(--neutral80)',
            '--color-divider'      => 'var(--neutral80)',
            '--color-primary'      => 'var(--primary40)',
            '--color-primary-hover'=> 'var(--primary30)',
            '--color-primary-light'=> 'var(--primary90)',
            '--color-on-primary'   => '#ffffff',
            '--color-primary-text' => 'var(--primary40)',
            '--color-accent'       => 'var(--action50)',
            '--color-accent-hover' => 'var(--action40)',
            '--color-accent-light' => 'var(--action90)',
            '--color-on-accent'    => '#ffffff',
            '--color-accent-text'  => 'var(--accent-text)',
            '--color-on-accent-icon' => '#ffffff',
            '--color-success'      => 'hsla(140, 65%, 45%, 1)',
            '--color-on-success'   => '#ffffff',
            '--color-success-text' => 'hsla(140, 65%, 35%, 1)',
            '--color-warning'      => 'hsla(25, 85%, 50%, 1)',
            '--color-on-warning'   => '#ffffff',
            '--color-warning-text' => 'hsla(25, 85%, 40%, 1)',
            '--color-error'        => 'hsla(0, 75%, 55%, 1)',
            '--color-on-error'     => '#ffffff',
            '--color-error-text'   => 'hsla(0, 75%, 45%, 1)',
            '--color-info'         => 'hsla(210, 75%, 55%, 1)',
            '--color-on-info'      => '#ffffff',
            '--color-info-text'    => 'hsla(210, 75%, 45%, 1)',
            '--cta-background'     => 'var(--action50)',
            '--cta-text'           => '#ffffff',
            '--cta-medium'         => 'var(--action40)',
            '--cta-icon'           => '#ffffff',
            '--color-accent-label' => 'var(--accent-text)',
        ];
    }

    protected static function build_compatibility_aliases(array $primary, array $neutral, array $accent): array
    {
        $aliases = [];
        $levels = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 95, 96, 97, 98, 99];

        foreach ($levels as $level) {
            if (isset($neutral["--neutral{$level}"])) {
                $aliases["--secondary{$level}"] = $neutral["--neutral{$level}"];
                $aliases["--neutral-variant{$level}"] = $neutral["--neutral{$level}"];
            }
        }

        $tertiary_map = [
            30 => $accent['--action30'] ?? ($primary['--primary30'] ?? 'var(--primary30)'),
            35 => $accent['--action30'] ?? ($primary['--primary35'] ?? 'var(--primary35)'),
            40 => $accent['--action40'] ?? ($primary['--primary40'] ?? 'var(--primary40)'),
            45 => $accent['--action40'] ?? ($primary['--primary45'] ?? 'var(--primary45)'),
            50 => $accent['--action50'] ?? ($primary['--primary50'] ?? 'var(--primary50)'),
            60 => $accent['--action60'] ?? ($primary['--primary60'] ?? 'var(--primary60)'),
            70 => $primary['--primary70'] ?? 'var(--primary70)',
            80 => $primary['--primary80'] ?? 'var(--primary80)',
            90 => $accent['--action90'] ?? ($primary['--primary90'] ?? 'var(--primary90)'),
        ];

        foreach ($levels as $level) {
            $aliases["--tertiary{$level}"] = $tertiary_map[$level] ?? ($neutral["--neutral{$level}"] ?? 'var(--neutral95)');
        }

        return $aliases;
    }

    public function print_inline_palette()
    {
        $scheme = get_theme_mod('th360_color_scheme', 'azul');

        if ($scheme === 'personalizado') {
            $hex = get_theme_mod('th360_custom_color', '#2e61ff');
            $palette = self::build_custom_palette_with_good_accent($hex);
        } else {
            $palette = self::$palettes[$scheme] ?? self::$palettes['azul'] ?? [];
        }

        $palette = self::apply_runtime_overrides($palette, $scheme);
        $this->print_palette_css($palette);
    }

    protected static function apply_runtime_overrides(array $palette, string $scheme): array
    {
        if ((bool) get_theme_mod('th360_use_advanced_palette', false) && in_array($scheme, ['amarillo', 'personalizado'], true)) {
            $neutral_hex = (string) get_theme_mod('th360_custom_neutral_color', '');
            if (self::is_hex_color($neutral_hex)) {
                $neutral = self::generate_neutral_palette_from_hex($neutral_hex);
                $palette = array_merge($palette, $neutral, self::build_neutral_aliases($neutral));
            }

            if ((bool) get_theme_mod('th360_use_custom_action_color', false)) {
                $action_hex = (string) get_theme_mod('th360_custom_action_color', '#44664e');
                if (self::is_hex_color($action_hex)) {
                    $palette = array_merge($palette, self::build_action_palette_from_hex($action_hex));
                }
            }
        }

        return array_merge($palette, self::build_runtime_header_tokens($scheme));
    }

    protected static function build_runtime_header_tokens(string $scheme): array
    {
        $defaults = self::get_dark_header_defaults($scheme);
        $use_custom = (bool) get_theme_mod('th360_use_advanced_palette', false)
            && (bool) get_theme_mod('th360_use_custom_dark_header', false);

        $bg = $use_custom && self::is_hex_color((string) get_theme_mod('th360_header_dark_bg', ''))
            ? (string) get_theme_mod('th360_header_dark_bg')
            : $defaults['bg'];
        $text = $use_custom && self::is_hex_color((string) get_theme_mod('th360_header_dark_text', ''))
            ? (string) get_theme_mod('th360_header_dark_text')
            : $defaults['text'];
        $accent = $use_custom && self::is_hex_color((string) get_theme_mod('th360_header_dark_accent', ''))
            ? (string) get_theme_mod('th360_header_dark_accent')
            : $defaults['accent'];
        $button_bg = $use_custom && self::is_hex_color((string) get_theme_mod('th360_header_dark_button_bg', ''))
            ? (string) get_theme_mod('th360_header_dark_button_bg')
            : $defaults['button_bg'];
        $breadcrumb_bg = $use_custom && self::is_hex_color((string) get_theme_mod('th360_header_dark_breadcrumb_bg', ''))
            ? (string) get_theme_mod('th360_header_dark_breadcrumb_bg')
            : $defaults['breadcrumb_bg'];

        return [
            '--header-dark-bg'               => $bg,
            '--header-dark-border'           => $defaults['border'],
            '--header-dark-link'             => $text,
            '--header-dark-link-hover'       => $accent,
            '--header-dark-link-active'      => $accent,
            '--header-dark-logo'             => $defaults['logo'],
            '--header-dark-button-bg'        => $button_bg,
            '--header-dark-button-text'      => $defaults['button_text'],
            '--header-dark-button-border'    => $defaults['button_border'],
            '--header-dark-button-hover-bg'  => $defaults['button_hover_bg'],
            '--header-dark-button-hover-text'=> $defaults['button_hover_text'],
            '--header-dark-breadcrumbs-bg'   => $breadcrumb_bg,
            '--header-dark-breadcrumbs-border' => $defaults['breadcrumb_border'],
            '--header-dark-breadcrumbs-text' => $text,
            '--header-dark-breadcrumbs-link' => $text,
            '--header-dark-breadcrumbs-hover'=> $accent,
            '--header-dark-breadcrumbs-separator' => $defaults['breadcrumb_separator'],
        ];
    }

    protected static function get_dark_header_defaults(string $scheme): array
    {
        $defaults = [
            'bg'                  => 'var(--neutral5)',
            'border'              => 'transparent',
            'text'                => 'var(--primary95)',
            'accent'              => 'var(--primary90)',
            'logo'                => 'var(--primary90)',
            'button_bg'           => 'var(--primary96)',
            'button_text'         => 'var(--primary30)',
            'button_border'       => 'transparent',
            'button_hover_bg'     => 'var(--primary95)',
            'button_hover_text'   => 'var(--primary30)',
            'breadcrumb_bg'       => 'var(--neutral20)',
            'breadcrumb_border'   => 'transparent',
            'breadcrumb_separator'=> 'var(--primary90)',
        ];

        return $defaults;
    }

    protected static function build_custom_palette_with_good_accent(string $hex): array
    {
        [$primary_hue] = self::hex_to_hsl($hex);
        $base_config = self::get_closest_config($primary_hue);
        $config = array_merge($base_config, ['primary_hue' => $primary_hue]);

        $accent_hue = self::wrap_hue($primary_hue + 150);
        if ($primary_hue >= 160 && $primary_hue <= 260) {
            $accent_hue = self::wrap_hue($primary_hue + 140);
        }

        $config['accent_hue'] = $accent_hue;
        $config['accent_saturation'] = 80;
        $config['accent_lightness'] = 48;

        $base = self::$bases['azul'];
        $base_primary = self::pick_vars($base, '--primary');
        $base_neutral = self::pick_vars($base, '--neutral');

        $primary = self::generate_primary_palette($base_primary, $primary_hue);
        $neutral = self::generate_neutral_palette($base_neutral, $config);
        $accent  = self::generate_manual_accent_palette($config);
        $aliases = self::build_compatibility_aliases($primary, $neutral, $accent);

        return array_merge($primary, $neutral, $accent, $aliases, self::build_smart_semantic_tokens());
    }

    protected static function generate_primary_palette(array $base_primary, float $hue): array
    {
        $levels = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 95, 96, 97, 98, 99];
        $primary = [];

        foreach ($levels as $level) {
            $val = $base_primary["--primary{$level}"] ?? 'hsla(0,0%,50%,1)';
            [$unused, $sat, $light, $alpha] = self::parse_hsla($val);
            $primary["--primary{$level}"] = self::build_hsla($hue, $sat, $light, $alpha);
        }

        return $primary;
    }

    protected static function generate_neutral_palette(array $base_neutral, array $config): array
    {
        $levels = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 95, 96, 97, 98, 99];
        $neutral = [];

        foreach ($levels as $level) {
            $val = $base_neutral["--neutral{$level}"] ?? 'hsla(0,0%,50%,1)';
            [$unused, $sat, $light, $alpha] = self::parse_hsla($val);
            $neutral_hue = $config['neutral_hue'];

            if ($level === 90) {
                $sat = max($sat, 12);
            }

            $neutral["--neutral{$level}"] = self::build_hsla($neutral_hue, $sat, $light, $alpha);
        }

        return $neutral;
    }

    protected static function generate_neutral_palette_from_hex(string $hex): array
    {
        [$hue, $sat] = self::hex_to_hsl($hex);
        $warm_reference = self::pick_vars(self::build_yellow_palette(), '--neutral');
        $levels = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 95, 96, 97, 98, 99];
        $neutral = [];
        $sat_influence = min(max($sat * 0.22, 0.8), 4.2);

        foreach ($levels as $level) {
            $reference = $warm_reference["--neutral{$level}"] ?? '#f6f0e7';
            [$unused_hue, $reference_sat, $reference_light] = self::hex_to_hsl($reference);
            $new_sat = min(16, max(1.8, ($reference_sat * 0.82) + $sat_influence));
            if ($level >= 90) {
                $new_sat = min(16, max($new_sat, 5.2));
            }
            $neutral["--neutral{$level}"] = self::build_hsla($hue, $new_sat, $reference_light, 1);
        }

        return $neutral;
    }

    protected static function build_neutral_aliases(array $neutral): array
    {
        $aliases = [];

        foreach ($neutral as $name => $value) {
            $suffix = str_replace('--neutral', '', $name);
            $aliases["--secondary{$suffix}"] = $value;
            $aliases["--neutral-variant{$suffix}"] = $value;
        }

        return $aliases;
    }

    protected static function build_action_palette_from_hex(string $hex): array
    {
        [$hue, $sat, $light] = self::hex_to_hsl($hex);
        $sat = max(55, min(90, $sat));
        $light = min(max($light, 38), 58);

        return [
            '--action30'    => self::build_hsla($hue, min(95, $sat + 6), max(18, $light - 18), 1),
            '--action40'    => self::build_hsla($hue, min(95, $sat + 4), max(24, $light - 8), 1),
            '--action50'    => self::build_hsla($hue, $sat, $light, 1),
            '--action60'    => self::build_hsla($hue, max(30, $sat - 8), min(72, $light + 10), 1),
            '--action90'    => self::build_hsla($hue, max(18, $sat - 35), 92, 1),
            '--on-action'   => '#ffffff',
            '--accent-text' => self::build_hsla($hue, min(95, $sat + 4), max(24, $light - 8), 1),
        ];
    }

    protected function print_palette_css(array $palette)
    {
        if (empty($palette)) {
            return;
        }

        $key = md5(wp_json_encode($palette));
        if (isset(self::$cache[$key])) {
            echo self::$cache[$key];
            return;
        }

        $css = ':root{';
        foreach ($palette as $var => $value) {
            $css .= $var . ':' . $value . ';';
        }
        $css .= '}';

        $output = '<style id="e360vo-color-palette">' . $css . '</style>';
        self::$cache[$key] = $output;
        echo $output;
    }

    protected static function get_closest_config(float $hue): array
    {
        $ranges = [
            [0, 30, 'rojo'],
            [30, 45, 'naranja'],
            [45, 70, 'amarillo'],
            [70, 100, 'lima'],
            [100, 160, 'verde'],
            [160, 200, 'cian'],
            [200, 260, 'azul'],
            [260, 330, 'morado'],
            [330, 360, 'rojo'],
        ];

        foreach ($ranges as $range) {
            [$min, $max, $slug] = $range;
            if ($hue >= $min && $hue < $max) {
                return self::$colorConfigs[$slug] ?? self::$colorConfigs['azul'];
            }
        }

        return self::$colorConfigs['azul'];
    }

    protected static function pick_vars(array $all, string $prefix): array
    {
        $picked = [];

        foreach ($all as $name => $value) {
            if (strpos($name, $prefix) === 0) {
                $picked[$name] = $value;
            }
        }

        return $picked;
    }

    protected static function parse_hsla(string $value): array
    {
        if (preg_match('/hsla\(\s*([\d.]+)\s*,\s*([\d.]+)%\s*,\s*([\d.]+)%\s*,\s*([\d.]+)\s*\)/i', $value, $matches)) {
            return [floatval($matches[1]), floatval($matches[2]), floatval($matches[3]), floatval($matches[4])];
        }

        return [0.0, 0.0, 0.0, 1.0];
    }

    protected static function build_hsla(float $hue, float $sat, float $light, float $alpha): string
    {
        $hue = self::wrap_hue($hue);
        $sat = max(0.0, min(100.0, $sat));
        $light = max(0.0, min(100.0, $light));
        $alpha = max(0.0, min(1.0, $alpha));

        return sprintf(
            'hsla(%s,%s%%,%s%%,%s)',
            rtrim(rtrim(number_format($hue, 1, '.', ''), '0'), '.'),
            rtrim(rtrim(number_format($sat, 1, '.', ''), '0'), '.'),
            rtrim(rtrim(number_format($light, 1, '.', ''), '0'), '.'),
            rtrim(rtrim(number_format($alpha, 3, '.', ''), '0'), '.')
        );
    }

    protected static function wrap_hue(float $hue): float
    {
        $hue = fmod($hue, 360.0);
        return $hue < 0 ? $hue + 360.0 : $hue;
    }

    protected static function hex_to_hsl(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $red = hexdec(substr($hex, 0, 2)) / 255;
        $green = hexdec(substr($hex, 2, 2)) / 255;
        $blue = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($red, $green, $blue);
        $min = min($red, $green, $blue);
        $light = ($max + $min) / 2;

        if ($max === $min) {
            $hue = 0;
            $sat = 0;
        } else {
            $delta = $max - $min;
            $sat = $light > 0.5 ? $delta / (2 - $max - $min) : $delta / ($max + $min);

            if ($max === $red) {
                $hue = ($green - $blue) / $delta + ($green < $blue ? 6 : 0);
            } elseif ($max === $green) {
                $hue = ($blue - $red) / $delta + 2;
            } else {
                $hue = ($red - $green) / $delta + 4;
            }

            $hue *= 60;
        }

        return [self::wrap_hue((float) $hue), (float) ($sat * 100), (float) ($light * 100)];
    }

    protected static function is_hex_color(string $value): bool
    {
        return (bool) preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value);
    }
}
