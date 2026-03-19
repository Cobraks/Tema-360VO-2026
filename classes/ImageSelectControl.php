<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (class_exists('WP_Customize_Control')) {

    class E360VO_ImageSelectControl extends WP_Customize_Control
    {
        public $type = 'image_select'; // Puedes cambiarle el nombre si quieres

        public function render_content()
        {
            if (empty($this->choices)) {
                return;
            }

            // Título
            if (!empty($this->label)) {
                echo '<span class="customize-control-title">' . esc_html($this->label) . '</span>';
            }

            // Descripción
            if (!empty($this->description)) {
                echo '<span class="description customize-control-description">' . esc_html($this->description) . '</span>';
            }

            echo '<div id="input_' . esc_attr($this->id) . '" class="e360vo-logo-shape-control">';

            // Recorremos las opciones
            foreach ($this->choices as $value => $choice) {
                $checked     = ($this->value() === $value) ? ' checked="checked"' : '';
                $label_text  = isset($choice['label']) ? $choice['label'] : '';
                $inline_style = isset($choice['style']) ? $choice['style'] : '';

                echo '<label style="display:inline-block; margin: 10px; text-align: center;">';

                // Radio input
                echo '<input type="radio" 
                             value="' . esc_attr($value) . '" 
                             name="_customize-radio-' . esc_attr($this->id) . '" '
                    . $checked;
                $this->link();
                echo ' />';

                // En lugar de <img>, usamos un <div> con el estilo
                echo '<div style="' . esc_attr($inline_style) . '; margin-bottom: 5px;"></div>';
                echo '<span>' . esc_html($label_text) . '</span>';

                echo '</label>';
            }

            echo '</div>';
        }
    }
}
