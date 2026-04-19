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
        'azul' => ['primary_hue' => 225, 'neutral_hue' => 45, 'accent_hue' => 20, 'accent_saturation' => 85, 'accent_lightness' => 50],
        'amarillo' => ['primary_hue' => 48, 'neutral_hue' => 38, 'accent_hue' => 164, 'accent_saturation' => 35, 'accent_lightness' => 47],
        'cian' => ['primary_hue' => 190, 'neutral_hue' => 45, 'accent_hue' => 15, 'accent_saturation' => 80, 'accent_lightness' => 48],
        'rojo' => ['primary_hue' => 0, 'neutral_hue' => 210, 'accent_hue' => 180, 'accent_saturation' => 75, 'accent_lightness' => 52],
        'verde' => ['primary_hue' => 140, 'neutral_hue' => 330, 'accent_hue' => 320, 'accent_saturation' => 80, 'accent_lightness' => 50],
        'morado' => ['primary_hue' => 285, 'neutral_hue' => 75, 'accent_hue' => 105, 'accent_saturation' => 75, 'accent_lightness' => 50],
        'naranja' => ['primary_hue' => 30, 'neutral_hue' => 210, 'accent_hue' => 210, 'accent_saturation' => 80, 'accent_lightness' => 50],
        'lima' => ['primary_hue' => 85, 'neutral_hue' => 285, 'accent_hue' => 275, 'accent_saturation' => 75, 'accent_lightness' => 50],
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
        $basePrimary = self::pick_vars($base, '--primary');
        $baseNeutral = self::pick_vars($base, '--neutral');

        foreach (self::$colorConfigs as $slug => $config) {
            if ($slug === 'amarillo') {
                self::$palettes[$slug] = self::build_yellow_palette();
                continue;
            }

            $primary = self::generate_primary_palette($basePrimary, $config['primary_hue']);
            $neutral = self::generate_neutral_palette($baseNeutral, $config);
            $accent = self::generate_manual_accent_palette($config);
            $aliases = self::build_compatibility_aliases($primary, $neutral, $accent);
            $tokens = self::build_smart_semantic_tokens();

            self::$palettes[$slug] = array_merge($primary, $neutral, $accent, $aliases, $tokens);
        }
    }

    protected static function build_yellow_palette(): array
    {
        $palette = [
            '--primary0' => '#000000', '--primary4' => '#130e00', '--primary5' => '#161000', '--primary6' => '#191300',
            '--primary10' => '#221b00', '--primary12' => '#271f00', '--primary17' => '#332900', '--primary20' => '#3a3000',
            '--primary22' => '#3f3400', '--primary24' => '#443800', '--primary25' => '#473a00', '--primary30' => '#544600',
            '--primary35' => '#625100', '--primary40' => '#705d00', '--primary50' => '#8c7500', '--primary60' => '#aa8f00',
            '--primary70' => '#c9a900', '--primary80' => '#e9c400', '--primary87' => '#ffd709', '--primary90' => '#ffe16d',
            '--primary92' => '#ffe792', '--primary94' => '#ffedb3', '--primary95' => '#fff0c2', '--primary96' => '#fff3d1',
            '--primary98' => '#fff9ef', '--primary99' => '#fffbff', '--primary100' => '#ffffff',
            '--secondary0' => '#000000', '--secondary4' => '#130e00', '--secondary5' => '#161000', '--secondary6' => '#181301',
            '--secondary10' => '#211b04', '--secondary12' => '#251f07', '--secondary17' => '#302a10', '--secondary20' => '#373016',
            '--secondary22' => '#3c341a', '--secondary24' => '#40391e', '--secondary25' => '#423b20', '--secondary30' => '#4e462a',
            '--secondary35' => '#5a5235', '--secondary40' => '#675e40', '--secondary50' => '#807757', '--secondary60' => '#9b906f',
            '--secondary70' => '#b6ab87', '--secondary80' => '#d2c6a1', '--secondary87' => '#e6dab4', '--secondary90' => '#efe2bc',
            '--secondary92' => '#f5e8c1', '--secondary94' => '#faedc6', '--secondary95' => '#fdf0c9', '--secondary96' => '#fff3d1',
            '--secondary98' => '#fff9ef', '--secondary99' => '#fffbff', '--secondary100' => '#ffffff',
            '--tertiary0' => '#000000', '--tertiary4' => '#001206', '--tertiary5' => '#001507', '--tertiary6' => '#001809',
            '--tertiary10' => '#00210e', '--tertiary12' => '#032512', '--tertiary17' => '#0e301c', '--tertiary20' => '#153722',
            '--tertiary22' => '#1a3b26', '--tertiary24' => '#1f402a', '--tertiary25' => '#21422c', '--tertiary30' => '#2d4e37',
            '--tertiary35' => '#385a42', '--tertiary40' => '#44664e', '--tertiary50' => '#5c7f65', '--tertiary60' => '#75997e',
            '--tertiary70' => '#8fb497', '--tertiary80' => '#aad0b2', '--tertiary87' => '#bde3c5', '--tertiary90' => '#c6eccd',
            '--tertiary92' => '#cbf2d2', '--tertiary94' => '#d1f7d8', '--tertiary95' => '#d4fadb', '--tertiary96' => '#d6fddd',
            '--tertiary98' => '#e9ffeb', '--tertiary99' => '#f5fff3', '--tertiary100' => '#ffffff',
            '--neutral0' => '#000000', '--neutral4' => '#100e09', '--neutral5' => '#12110c', '--neutral6' => '#15130e',
            '--neutral10' => '#1d1b16', '--neutral12' => '#211f1a', '--neutral17' => '#2c2a24', '--neutral20' => '#33302a',
            '--neutral22' => '#37352e', '--neutral24' => '#3b3933', '--neutral25' => '#3e3b35', '--neutral30' => '#494640',
            '--neutral35' => '#55524b', '--neutral40' => '#615e57', '--neutral50' => '#7a776f', '--neutral60' => '#949088',
            '--neutral70' => '#afaba2', '--neutral80' => '#cbc6bd', '--neutral87' => '#dfd9d0', '--neutral90' => '#e8e2d9',
            '--neutral92' => '#ede7de', '--neutral94' => '#f3ede4', '--neutral95' => '#f6f0e7', '--neutral96' => '#f9f3ea',
            '--neutral98' => '#fff9ef', '--neutral99' => '#fffbff', '--neutral100' => '#ffffff',
            '--neutral-variant0' => '#000000', '--neutral-variant4' => '#110e05', '--neutral-variant5' => '#141107', '--neutral-variant6' => '#161309',
            '--neutral-variant10' => '#1f1b10', '--neutral-variant12' => '#231f14', '--neutral-variant17' => '#2d2a1e', '--neutral-variant20' => '#343024',
            '--neutral-variant22' => '#393528', '--neutral-variant24' => '#3d392c', '--neutral-variant25' => '#3f3b2e', '--neutral-variant30' => '#4b4739',
            '--neutral-variant35' => '#575244', '--neutral-variant40' => '#635e50', '--neutral-variant50' => '#7c7767', '--neutral-variant60' => '#979080',
            '--neutral-variant70' => '#b2ab9a', '--neutral-variant80' => '#cdc6b4', '--neutral-variant87' => '#e1d9c7', '--neutral-variant90' => '#eae2cf',
            '--neutral-variant92' => '#f0e7d5', '--neutral-variant94' => '#f6edda', '--neutral-variant95' => '#f8f0dd', '--neutral-variant96' => '#fbf3e0',
            '--neutral-variant98' => '#fff9ef', '--neutral-variant99' => '#fffbff', '--neutral-variant100' => '#ffffff',
            '--action30' => 'var(--tertiary30)', '--action40' => 'var(--tertiary40)', '--action50' => 'var(--tertiary50)',
            '--action60' => 'var(--tertiary60)', '--action90' => 'var(--tertiary90)', '--on-action' => '#ffffff', '--accent-text' => 'var(--tertiary40)',
        ];

        return array_merge($palette, self::build_smart_semantic_tokens());
    }

    protected static function generate_manual_accent_palette(array $config): array
    {
        $hue = $config['accent_hue'];
        $base_sat = $config['accent_saturation'];
        $base_light = $config['accent_lightness'];

        $lightness_map = [30 => $base_light - 20, 40 => $base_light - 10, 50 => $base_light, 60 => $base_light + 10, 90 => $base_light + 40];
        $saturation_map = [30 => $base_sat, 40 => $base_sat + 5, 50 => $base_sat + 10, 60 => $base_sat + 5, 90 => $base_sat - 40];

        if ($config['primary_hue'] == 190) {
            $hue = 15;
            $lightness_map[30] = 35;
            $lightness_map[40] = 42;
            $lightness_map[50] = 48;
            $saturation_map[50] = 80;
        }

        if ($config['primary_hue'] == 225) {
            $hue = 20;
            $lightness_map[40] = 42;
            $saturation_map[50] = 85;
        }

        return [
            '--action30' => self::build_hsla($hue, $saturation_map[30], $lightness_map[30], 1),
            '--action40' => self::build_hsla($hue, $saturation_map[40], $lightness_map[40], 1),
            '--action50' => self::build_hsla($hue, $saturation_map[50], $lightness_map[50], 1),
            '--action60' => self::build_hsla($hue, $saturation_map[60], $lightness_map[60], 1),
            '--action90' => self::build_hsla($hue, $saturation_map[90], $lightness_map[90], 1),
            '--on-action' => '#ffffff',
            '--accent-text' => self::build_hsla($hue, $saturation_map[40], $lightness_map[40], 1),
        ];
    }

    protected static function build_smart_semantic_tokens(): array
    {
        return [
            '--color-bg' => 'var(--neutral99)', '--color-surface' => 'var(--neutral97)', '--color-surface-2' => 'var(--neutral95)',
            '--color-surface-3' => 'var(--neutral90)', '--color-surface-4' => 'var(--neutral80)', '--color-text' => 'var(--neutral15)',
            '--color-text-2' => 'var(--neutral30)', '--color-muted' => 'var(--neutral50)', '--color-on-light' => 'var(--neutral10)',
            '--color-on-dark' => 'var(--neutral95)', '--color-border' => 'var(--neutral90)', '--color-border-2' => 'var(--neutral80)',
            '--color-divider' => 'var(--neutral80)', '--color-primary' => 'var(--primary40)', '--color-primary-hover' => 'var(--primary30)',
            '--color-primary-light' => 'var(--primary90)', '--color-on-primary' => '#ffffff', '--color-primary-text' => 'var(--primary40)',
            '--color-accent' => 'var(--action50)', '--color-accent-hover' => 'var(--action40)', '--color-accent-light' => 'var(--action90)',
            '--color-on-accent' => '#ffffff', '--color-accent-text' => 'var(--accent-text)', '--color-on-accent-icon' => '#ffffff',
            '--color-success' => 'hsla(140, 65%, 45%, 1)', '--color-on-success' => '#ffffff', '--color-success-text' => 'hsla(140, 65%, 35%, 1)',
            '--color-warning' => 'hsla(25, 85%, 50%, 1)', '--color-on-warning' => '#ffffff', '--color-warning-text' => 'hsla(25, 85%, 40%, 1)',
            '--color-error' => 'hsla(0, 75%, 55%, 1)', '--color-on-error' => '#ffffff', '--color-error-text' => 'hsla(0, 75%, 45%, 1)',
            '--color-info' => 'hsla(210, 75%, 55%, 1)', '--color-on-info' => '#ffffff', '--color-info-text' => 'hsla(210, 75%, 45%, 1)',
            '--cta-background' => 'var(--action50)', '--cta-text' => '#ffffff', '--cta-medium' => 'var(--action40)', '--cta-icon' => '#ffffff',
            '--color-accent-label' => 'var(--accent-text)',
            '--header-bg' => 'var(--neutral98)', '--header-border' => 'var(--color-border)', '--header-link' => 'var(--color-text)',
            '--header-link-hover' => 'var(--primary40)', '--header-link-active' => 'var(--primary40)', '--header-logo' => 'var(--primary20)',
            '--header-button-bg' => 'var(--neutral99)', '--header-button-text' => 'var(--color-text)', '--header-button-border' => 'var(--color-border)',
            '--header-button-hover-bg' => 'var(--neutral90)', '--header-button-hover-text' => 'var(--neutral10)',
            '--breadcrumbs-bg' => 'var(--neutral97)', '--breadcrumbs-border' => 'var(--color-border)', '--breadcrumbs-text' => 'var(--neutral10)',
            '--breadcrumbs-link' => 'var(--primary30)', '--breadcrumbs-link-hover' => 'var(--primary40)', '--breadcrumbs-gradient-bg' => 'var(--neutral95)',
            '--breadcrumbs-separator' => 'var(--color-muted)',
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

        $tertiaryMap = [
            30 => $accent['--action30'] ?? ($primary['--primary30'] ?? 'var(--primary30)'),
            35 => $accent['--action30'] ?? ($primary['--primary35'] ?? 'var(--primary35)'),
            40 => $accent['--action40'] ?? ($primary['--primary40'] ?? 'var(--primary40)'),
            50 => $accent['--action50'] ?? ($primary['--primary50'] ?? 'var(--primary50)'),
            60 => $accent['--action60'] ?? ($primary['--primary60'] ?? 'var(--primary60)'),
            70 => $primary['--primary70'] ?? 'var(--primary70)', 80 => $primary['--primary80'] ?? 'var(--primary80)',
            90 => $accent['--action90'] ?? ($primary['--primary90'] ?? 'var(--primary90)'),
        ];

        foreach ($levels as $level) {
            $aliases["--tertiary{$level}"] = $tertiaryMap[$level] ?? ($neutral["--neutral{$level}"] ?? 'var(--neutral95)');
        }

        return $aliases;
    }

    public function print_inline_palette()
    {
        $scheme = get_theme_mod('th360_color_scheme', 'amarillo');

        if ($scheme === 'personalizado') {
            $hex = get_theme_mod('th360_custom_color', '#2e61ff');
            $palette = self::build_custom_palette_with_good_accent($hex);
        } else {
            $palette = self::$palettes[$scheme] ?? self::$palettes['amarillo'] ?? [];
        }

        $palette = self::apply_runtime_overrides($palette, $scheme);
        $this->print_palette_css($palette);
    }

    protected static function apply_runtime_overrides(array $palette, string $scheme): array
    {
        $advanced = (bool) get_theme_mod('th360_use_advanced_palette', false);

        if ($advanced) {
            $neutral_bias = get_theme_mod('th360_neutral_bias', 'automatico');
            if ($neutral_bias !== 'automatico') {
                $neutral = self::generate_biased_neutral_palette($neutral_bias);
                $palette = array_merge($palette, $neutral, self::build_neutral_aliases($neutral));
            }

            if ((bool) get_theme_mod('th360_use_custom_action_color', false)) {
                $action_hex = get_theme_mod('th360_custom_action_color', '#44664e');
                $palette = array_merge($palette, self::build_action_palette_from_hex($action_hex));
            }
        }

        return array_merge($palette, self::build_runtime_header_tokens($scheme));
    }

    protected static function build_runtime_header_tokens(string $scheme): array
    {
        $use_custom = (bool) get_theme_mod('th360_use_custom_dark_header', false);
        $defaults = self::get_dark_header_defaults($scheme);

        $bg = $use_custom ? (get_theme_mod('th360_header_dark_bg', '') ?: $defaults['bg']) : $defaults['bg'];
        $text = $use_custom ? (get_theme_mod('th360_header_dark_text', '') ?: $defaults['text']) : $defaults['text'];
        $accent = $use_custom ? (get_theme_mod('th360_header_dark_accent', '') ?: $defaults['accent']) : $defaults['accent'];
        $button_bg = $use_custom ? (get_theme_mod('th360_header_dark_button_bg', '') ?: $defaults['button_bg']) : $defaults['button_bg'];
        $breadcrumb_bg = $use_custom ? (get_theme_mod('th360_header_dark_breadcrumb_bg', '') ?: $defaults['breadcrumbs_bg']) : $defaults['breadcrumbs_bg'];

        $button_hover = self::is_hex_color($button_bg) ? self::adjust_hex_lightness($button_bg, -8) : $defaults['button_hover_bg'];
        $breadcrumb_separator = self::is_hex_color($text) ? self::adjust_hex_lightness($text, -24) : $defaults['separator'];

        return [
            '--header-dark-bg' => $bg,
            '--header-dark-border' => $defaults['border'],
            '--header-dark-link' => $text,
            '--header-dark-link-hover' => $accent,
            '--header-dark-link-active' => $accent,
            '--header-dark-logo' => $accent,
            '--header-dark-button-bg' => $button_bg,
            '--header-dark-button-text' => $text,
            '--header-dark-button-border' => $defaults['button_border'],
            '--header-dark-button-hover-bg' => $button_hover,
            '--header-dark-button-hover-text' => $text,
            '--header-dark-breadcrumbs-bg' => $breadcrumb_bg,
            '--header-dark-breadcrumbs-border' => $defaults['breadcrumbs_border'],
            '--header-dark-breadcrumbs-text' => $text,
            '--header-dark-breadcrumbs-link' => $text,
            '--header-dark-breadcrumbs-hover' => $accent,
            '--header-dark-breadcrumbs-separator' => $breadcrumb_separator,
        ];
    }

    protected static function get_dark_header_defaults(string $scheme): array
    {
        $defaults = [
            'bg' => '#111111',
            'border' => 'transparent',
            'text' => 'var(--primary70)',
            'accent' => 'var(--primary80)',
            'button_bg' => 'var(--primary10)',
            'button_hover_bg' => 'var(--primary20)',
            'button_border' => 'rgba(255,255,255,.08)',
            'breadcrumbs_bg' => 'var(--neutral10)',
            'breadcrumbs_border' => 'rgba(255,255,255,.06)',
            'separator' => 'rgba(255,255,255,.38)',
        ];

        if ($scheme === 'amarillo') {
            $defaults['bg'] = '#100e09';
            $defaults['text'] = 'var(--primary70)';
            $defaults['accent'] = 'var(--primary80)';
            $defaults['button_bg'] = 'var(--primary10)';
            $defaults['button_hover_bg'] = 'var(--primary20)';
            $defaults['breadcrumbs_bg'] = 'var(--neutral10)';
        }

        return $defaults;
    }

    protected static function build_custom_palette_with_good_accent(string $hex): array
    {
        [$hPrimary] = self::hex_to_hsl($hex);
        $baseConfig = self::get_closest_config($hPrimary);
        $config = array_merge($baseConfig, ['primary_hue' => $hPrimary]);

        $accent_hue = self::wrap_hue($hPrimary + 150);
        if ($hPrimary >= 160 && $hPrimary <= 260) {
            $accent_hue = self::wrap_hue($hPrimary + 140);
        }

        $config['accent_hue'] = $accent_hue;
        $config['accent_saturation'] = 80;
        $config['accent_lightness'] = 48;

        $base = self::$bases['azul'];
        $basePrimary = self::pick_vars($base, '--primary');
        $baseNeutral = self::pick_vars($base, '--neutral');

        $primary = self::generate_primary_palette($basePrimary, $hPrimary);
        $neutral = self::generate_neutral_palette($baseNeutral, $config);
        $accent = self::generate_manual_accent_palette($config);
        $aliases = self::build_compatibility_aliases($primary, $neutral, $accent);

        return array_merge($primary, $neutral, $accent, $aliases, self::build_smart_semantic_tokens());
    }

    protected static function get_closest_config(float $hue): array
    {
        $hueRanges = [
            [0, 30, 'rojo'], [30, 45, 'naranja'], [45, 70, 'amarillo'], [70, 100, 'lima'],
            [100, 160, 'verde'], [160, 200, 'cian'], [200, 260, 'azul'], [260, 330, 'morado'], [330, 360, 'rojo'],
        ];

        foreach ($hueRanges as [$min, $max, $slug]) {
            if ($hue >= $min && $hue < $max) {
                return self::$colorConfigs[$slug] ?? self::$colorConfigs['azul'];
            }
        }

        return self::$colorConfigs['azul'];
    }

    protected static function generate_primary_palette(array $basePrimary, float $hue): array
    {
        $levels = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 95, 96, 97, 98, 99];
        $primary = [];

        foreach ($levels as $level) {
            $val = $basePrimary["--primary{$level}"] ?? 'hsla(0,0%,50%,1)';
            [$h, $s, $l, $a] = self::parse_hsla($val);
            $primary["--primary{$level}"] = self::build_hsla($hue, $s, $l, $a);
        }

        return $primary;
    }

    protected static function generate_neutral_palette(array $baseNeutral, array $config): array
    {
        $levels = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 95, 96, 97, 98, 99];
        $neutral = [];

        foreach ($levels as $level) {
            $val = $baseNeutral["--neutral{$level}"] ?? 'hsla(0,0%,50%,1)';
            [$h, $s, $l, $a] = self::parse_hsla($val);
            $neutral_hue = $config['neutral_hue'];

            if ($level === 90) {
                $neutral_hue = 45;
                $s = 12;
                $l = 90;
            }

            $neutral["--neutral{$level}"] = self::build_hsla($neutral_hue, $s, $l, $a);
        }

        return $neutral;
    }

    protected static function generate_biased_neutral_palette(string $bias): array
    {
        $biasHueMap = ['calido' => 32, 'neutro' => 45, 'frio' => 210];
        $hue = $biasHueMap[$bias] ?? 45;
        $config = ['neutral_hue' => $hue];
        $baseNeutral = self::pick_vars(self::$bases['azul'], '--neutral');

        return self::generate_neutral_palette($baseNeutral, $config);
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
        if (!self::is_hex_color($hex)) {
            return [];
        }

        [$h, $s, $l] = self::hex_to_hsl($hex);

        return [
            '--action30' => self::build_hsla($h, max($s, 35), max($l - 18, 18), 1),
            '--action40' => self::build_hsla($h, max($s, 35), max($l - 10, 24), 1),
            '--action50' => self::build_hsla($h, max($s, 35), max($l, 32), 1),
            '--action60' => self::build_hsla($h, max($s - 8, 22), min($l + 10, 72), 1),
            '--action90' => self::build_hsla($h, max($s - 38, 12), min($l + 36, 92), 1),
            '--on-action' => $l >= 62 ? '#111111' : '#ffffff',
            '--accent-text' => self::build_hsla($h, max($s, 35), max($l - 14, 20), 1),
            '--tertiary30' => self::build_hsla($h, max($s - 12, 24), max($l - 18, 18), 1),
            '--tertiary40' => self::build_hsla($h, max($s - 10, 24), max($l - 10, 24), 1),
            '--tertiary50' => self::build_hsla($h, max($s - 8, 22), max($l, 32), 1),
            '--tertiary60' => self::build_hsla($h, max($s - 18, 18), min($l + 10, 72), 1),
            '--tertiary90' => self::build_hsla($h, max($s - 45, 10), min($l + 36, 92), 1),
        ];
    }

    protected function print_palette_css(array $palette)
    {
        if (empty($palette)) {
            return;
        }

        $key = md5(json_encode($palette));
        if (isset(self::$cache[$key])) {
            echo self::$cache[$key];
            return;
        }

        $css = ':root{';
        foreach ($palette as $var => $val) {
            $css .= "{$var}:{$val};";
        }
        $css .= '}';

        $out = "<style id=\"e360vo-color-palette\">{$css}</style>";
        self::$cache[$key] = $out;
        echo $out;
    }

    protected static function pick_vars(array $all, string $prefix): array
    {
        $out = [];
        foreach ($all as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $out[$key] = $value;
            }
        }
        return $out;
    }

    protected static function parse_hsla(string $hsla): array
    {
        if (preg_match('/hsla\(\s*([\d.]+)\s*,\s*([\d.]+)%\s*,\s*([\d.]+)%\s*,\s*([\d.]+)\s*\)/i', $hsla, $match)) {
            return [floatval($match[1]), floatval($match[2]), floatval($match[3]), floatval($match[4])];
        }
        return [0.0, 0.0, 0.0, 1.0];
    }

    protected static function build_hsla(float $h, float $s, float $l, float $a): string
    {
        $h = self::wrap_hue($h);
        $s = self::clamp_percent($s);
        $l = self::clamp_percent($l);
        $a = max(0.0, min(1.0, $a));

        $hStr = rtrim(rtrim(number_format($h, 1, '.', ''), '0'), '.');
        $sStr = rtrim(rtrim(number_format($s, 1, '.', ''), '0'), '.');
        $lStr = rtrim(rtrim(number_format($l, 1, '.', ''), '0'), '.');
        $aStr = rtrim(rtrim(number_format($a, 3, '.', ''), '0'), '.');

        return "hsla({$hStr},{$sStr}%,{$lStr}%,{$aStr})";
    }

    protected static function clamp_percent(float $value): float
    {
        return max(0.0, min(100.0, $value));
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

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $l = ($max + $min) / 2;

        if ($max === $min) {
            $h = 0;
            $s = 0;
        } else {
            $d = $max - $min;
            $s = $l > 0.5 ? $d / (2 - $max - $min) : $d / ($max + $min);

            if ($max === $r) {
                $h = ($g - $b) / $d + ($g < $b ? 6 : 0);
            } elseif ($max === $g) {
                $h = ($b - $r) / $d + 2;
            } else {
                $h = ($r - $g) / $d + 4;
            }

            $h *= 60;
        }

        return [self::wrap_hue((float) $h), (float) ($s * 100), (float) ($l * 100)];
    }

    protected static function is_hex_color(string $value): bool
    {
        return (bool) preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', trim($value));
    }

    protected static function adjust_hex_lightness(string $hex, float $delta): string
    {
        [$h, $s, $l] = self::hex_to_hsl($hex);
        return self::hsl_to_hex($h, $s, self::clamp_percent($l + $delta));
    }

    protected static function hsl_to_hex(float $h, float $s, float $l): string
    {
        $h = self::wrap_hue($h) / 360;
        $s = self::clamp_percent($s) / 100;
        $l = self::clamp_percent($l) / 100;

        if ($s == 0) {
            $r = $g = $b = $l;
        } else {
            $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
            $p = 2 * $l - $q;
            $r = self::hue_to_rgb($p, $q, $h + 1 / 3);
            $g = self::hue_to_rgb($p, $q, $h);
            $b = self::hue_to_rgb($p, $q, $h - 1 / 3);
        }

        return sprintf('#%02x%02x%02x', round($r * 255), round($g * 255), round($b * 255));
    }

    protected static function hue_to_rgb(float $p, float $q, float $t): float
    {
        if ($t < 0) {
            $t += 1;
        }
        if ($t > 1) {
            $t -= 1;
        }
        if ($t < 1 / 6) {
            return $p + ($q - $p) * 6 * $t;
        }
        if ($t < 1 / 2) {
            return $q;
        }
        if ($t < 2 / 3) {
            return $p + ($q - $p) * (2 / 3 - $t) * 6;
        }
        return $p;
    }
}
