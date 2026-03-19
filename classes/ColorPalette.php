<?php
// classes/ColorPalette.php
if (!defined('ABSPATH')) {
    exit;
}

class E360VO_ColorPalette
{
    protected static $bases = [
        'azul' => [
            // PRIMARY
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

            // NEUTRAL
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

    /**
     * CONFIGURACIONES CON ACCENTOS MANUALES OPTIMIZADOS
     * Basados en lo que ya funcionaba bien
     */
    protected static $colorConfigs = [
        'azul' => [
            'primary_hue' => 225,
            'neutral_hue' => 45,
            'accent_hue' => 20,      // Naranja coral oscuro (no amarillo)
            'accent_saturation' => 85, // Alta saturación para CTA
            'accent_lightness' => 50,  // Punto medio
        ],
        'cian' => [
            'primary_hue' => 190,
            'neutral_hue' => 45,
            'accent_hue' => 15,      // Coral anaranjado intenso (no amarillo)
            'accent_saturation' => 80,
            'accent_lightness' => 48,  // Un poco más oscuro para buen contraste
        ],
        'rojo' => [
            'primary_hue' => 0,
            'neutral_hue' => 210,
            'accent_hue' => 180,     // Cian para contraste
            'accent_saturation' => 75,
            'accent_lightness' => 52,
        ],
        'verde' => [
            'primary_hue' => 140,
            'neutral_hue' => 330,
            'accent_hue' => 320,     // Magenta para contraste
            'accent_saturation' => 80,
            'accent_lightness' => 50,
        ],
        'morado' => [
            'primary_hue' => 285,
            'neutral_hue' => 75,
            'accent_hue' => 105,     // Verde para contraste
            'accent_saturation' => 75,
            'accent_lightness' => 50,
        ],
        'naranja' => [
            'primary_hue' => 30,
            'neutral_hue' => 210,
            'accent_hue' => 210,     // Azul para contraste
            'accent_saturation' => 80,
            'accent_lightness' => 50,
        ],
        'lima' => [
            'primary_hue' => 85,
            'neutral_hue' => 285,
            'accent_hue' => 275,     // Púrpura para contraste
            'accent_saturation' => 75,
            'accent_lightness' => 50,
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
        $basePrimary = self::pick_vars($base, '--primary');
        $baseNeutral = self::pick_vars($base, '--neutral');

        foreach (self::$colorConfigs as $slug => $config) {
            $primary = self::generate_primary_palette($basePrimary, $config['primary_hue']);
            $neutral = self::generate_neutral_palette($baseNeutral, $config);
            $accent = self::generate_manual_accent_palette($config);
            $tokens = self::build_smart_semantic_tokens();

            self::$palettes[$slug] = array_merge($primary, $neutral, $accent, $tokens);
        }
    }

    /**
     * Genera paleta ACCENT con valores manuales optimizados
     * No usamos complementario automático, sino colores que sabemos funcionan
     */
    protected static function generate_manual_accent_palette(array $config): array
    {
        $hue = $config['accent_hue'];
        $base_sat = $config['accent_saturation'];
        $base_light = $config['accent_lightness'];

        // Curva de luminosidad para acento (similar a primary pero más contrastada)
        $lightness_map = [
            30 => $base_light - 20, // Más oscuro
            40 => $base_light - 10, // Oscuro
            50 => $base_light,      // Principal
            60 => $base_light + 10, // Claro
            90 => $base_light + 40, // Muy claro para fondos
        ];

        // Curva de saturación (más saturado en tonos medios)
        $saturation_map = [
            30 => $base_sat,
            40 => $base_sat + 5,
            50 => $base_sat + 10,
            60 => $base_sat + 5,
            90 => $base_sat - 40, // Mucho menos saturado en claros
        ];

        // Para cian específicamente, ajustar aún más para evitar amarillo
        if ($config['primary_hue'] == 190) { // Cian
            $hue = 15; // Coral anaranjado (no amarillo)
            // Asegurar tonos oscuros para buen contraste con texto blanco
            $lightness_map[30] = 35;
            $lightness_map[40] = 42;
            $lightness_map[50] = 48;
            $saturation_map[50] = 80; // Alta saturación
        }

        // Para azul también ajustar
        if ($config['primary_hue'] == 225) { // Azul
            $hue = 20; // Naranja coral
            $lightness_map[40] = 42; // Un poco más oscuro
            $saturation_map[50] = 85; // Muy saturado
        }

        $action30 = self::build_hsla($hue, $saturation_map[30], $lightness_map[30], 1);
        $action40 = self::build_hsla($hue, $saturation_map[40], $lightness_map[40], 1);
        $action50 = self::build_hsla($hue, $saturation_map[50], $lightness_map[50], 1);
        $action60 = self::build_hsla($hue, $saturation_map[60], $lightness_map[60], 1);
        $action90 = self::build_hsla($hue, $saturation_map[90], $lightness_map[90], 1);

        // Determinar si el texto sobre acento debe ser blanco o negro
        // Para acentos coral/anaranjados oscuros, blanco funciona bien
        $on_action = '#ffffff';
        $on_action_icon = '#ffffff';

        return [
            '--action30'    => $action30,
            '--action40'    => $action40,
            '--action50'    => $action50,
            '--action60'    => $action60,
            '--action90'    => $action90,
            '--on-action'   => $on_action,
            '--accent-text' => $action40, // Para texto sobre fondo blanco
        ];
    }

    /**
     * Tokens semánticos optimizados
     */
    protected static function build_smart_semantic_tokens(): array
    {
        return [
            // Superficies
            '--color-bg'        => 'var(--neutral99)',
            '--color-surface'   => 'var(--neutral97)',
            '--color-surface-2' => 'var(--neutral95)',
            '--color-surface-3' => 'var(--neutral90)',
            '--color-surface-4' => 'var(--neutral85)',

            // Texto
            '--color-text'      => 'var(--neutral15)',
            '--color-text-2'    => 'var(--neutral30)',
            '--color-muted'     => 'var(--neutral50)',
            '--color-on-light'  => 'var(--neutral10)',
            '--color-on-dark'   => 'var(--neutral95)',

            // Bordes
            '--color-border'    => 'var(--neutral90)',
            '--color-border-2'  => 'var(--neutral80)',
            '--color-divider'   => 'var(--neutral85)',

            // Primary
            '--color-primary'        => 'var(--primary40)',
            '--color-primary-hover'  => 'var(--primary30)',
            '--color-primary-light'  => 'var(--primary90)',
            '--color-on-primary'     => '#ffffff',
            '--color-primary-text'   => 'var(--primary40)',

            // Accent - Ahora con colores manuales optimizados
            '--color-accent'         => 'var(--action50)',
            '--color-accent-hover'   => 'var(--action40)',
            '--color-accent-light'   => 'var(--action90)',
            '--color-on-accent'      => '#ffffff', // Siempre blanco (acentos oscuros)
            '--color-accent-text'    => 'var(--accent-text)', // action40 para texto
            '--color-on-accent-icon' => '#ffffff', // Iconos blancos sobre acento

            // Estados semánticos
            '--color-success'        => 'hsla(140, 65%, 45%, 1)',
            '--color-on-success'     => '#ffffff',
            '--color-success-text'   => 'hsla(140, 65%, 35%, 1)',

            '--color-warning'        => 'hsla(25, 85%, 50%, 1)', // Naranja coral, no amarillo
            '--color-on-warning'     => '#ffffff', // Blanco sobre naranja coral
            '--color-warning-text'   => 'hsla(25, 85%, 40%, 1)',

            '--color-error'          => 'hsla(0, 75%, 55%, 1)',
            '--color-on-error'       => '#ffffff',
            '--color-error-text'     => 'hsla(0, 75%, 45%, 1)',

            '--color-info'           => 'hsla(210, 75%, 55%, 1)',
            '--color-on-info'        => '#ffffff',
            '--color-info-text'      => 'hsla(210, 75%, 45%, 1)',

            // Compatibilidad
            '--cta-background' => 'var(--action50)',
            '--cta-text'       => '#ffffff', // Siempre blanco sobre CTA
            '--cta-medium'     => 'var(--action40)',
            '--cta-icon'       => '#ffffff',

            // Especial para texto "Financiado" sobre fondo blanco
            '--color-accent-label' => 'var(--accent-text)',
        ];
    }

    public function print_inline_palette()
    {
        $scheme = get_theme_mod('th360_color_scheme', 'azul');

        if ($scheme === 'personalizado') {
            $hex = get_theme_mod('th360_custom_color', '#2e61ff');
            $palette = self::build_custom_palette_with_good_accent($hex);
            $this->print_palette_css($palette);
            return;
        }

        $palette = self::$palettes[$scheme] ?? self::$palettes['azul'] ?? [];
        $this->print_palette_css($palette);
    }

    /**
     * Para colores personalizados, elegir acento basado en lo que funciona
     */
    protected static function build_custom_palette_with_good_accent(string $hex): array
    {
        [$hPrimary] = self::hex_to_hsl($hex);
        $baseConfig = self::get_closest_config($hPrimary);

        // Sobreescribir configuración con el HUE personalizado
        $config = array_merge($baseConfig, ['primary_hue' => $hPrimary]);

        // Para acento personalizado: usar complementario split (+150° no +180°)
        // Esto da colores más anaranjados/coral en lugar de amarillos
        $accent_hue = self::wrap_hue($hPrimary + 150); // +150° en lugar de +180°

        // Si el color principal es frío (azul, cian, verde), ajustar aún más
        if ($hPrimary >= 160 && $hPrimary <= 260) {
            $accent_hue = self::wrap_hue($hPrimary + 140); // Más cerca del naranja
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
        $tokens = self::build_smart_semantic_tokens();

        return array_merge($primary, $neutral, $accent, $tokens);
    }

    protected static function get_closest_config(float $hue): array
    {
        $hueRanges = [
            [0, 30, 'rojo'],
            [30, 70, 'naranja'],
            [70, 100, 'lima'],
            [100, 160, 'verde'],
            [160, 200, 'cian'],
            [200, 260, 'azul'],
            [260, 330, 'morado'],
            [330, 360, 'rojo'],
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

    protected function print_palette_css(array $palette)
    {
        if (empty($palette)) return;

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

    // ============================================================
    // Helpers
    // ============================================================

    protected static function pick_vars(array $all, string $prefix): array
    {
        $out = [];
        foreach ($all as $k => $v) {
            if (strpos($k, $prefix) === 0) {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    protected static function is_hsla($value): bool
    {
        return (bool)preg_match('/^\s*hsla\(/i', (string)$value);
    }

    protected static function parse_hsla(string $hsla): array
    {
        if (preg_match('/hsla\(\s*([\d.]+)\s*,\s*([\d.]+)%\s*,\s*([\d.]+)%\s*,\s*([\d.]+)\s*\)/i', $hsla, $m)) {
            return [floatval($m[1]), floatval($m[2]), floatval($m[3]), floatval($m[4])];
        }
        return [0.0, 0.0, 0.0, 1.0];
    }

    protected static function build_hsla(float $h, float $s, float $l, float $a): string
    {
        $h = self::wrap_hue($h);
        $s = self::clamp_percent($s);
        $l = self::clamp_percent($l);
        $a = max(0.0, min(1.0, $a));

        $h_str = rtrim(rtrim(number_format($h, 1, '.', ''), '0'), '.');
        $s_str = rtrim(rtrim(number_format($s, 1, '.', ''), '0'), '.');
        $l_str = rtrim(rtrim(number_format($l, 1, '.', ''), '0'), '.');
        $a_str = rtrim(rtrim(number_format($a, 3, '.', ''), '0'), '.');

        return "hsla({$h_str},{$s_str}%,{$l_str}%,{$a_str})";
    }

    protected static function clamp_percent(float $v): float
    {
        return max(0.0, min(100.0, $v));
    }

    protected static function wrap_hue(float $h): float
    {
        $h = fmod($h, 360.0);
        return $h < 0 ? $h + 360.0 : $h;
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

        return [self::wrap_hue((float)$h), (float)($s * 100), (float)($l * 100)];
    }
}
