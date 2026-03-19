<?php
/**
 * Página de error 404
 *
 * @package gv360
 */
defined( 'ABSPATH' ) or die( 'Acceso directo no permitido.' );

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style>
        body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    background-color: #d7d7d7;
    font-family: Arial, sans-serif;
}

.error-404__container {
    text-align: center;
}

.error-404__logo img {
    width: 100%; /* Ajusta este valor para cambiar el tamaño del logo */
}

.error-404__message {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 5em; /* Ajusta este valor para cambiar el tamaño del texto */
    color: #333; /* Color del texto "Error 404" */
    position: relative;
    margin-top: 20px;
}

.error-404__message::after {
    content: '';
    position: absolute;
    right: -20px;
    width: 8px;
    height: 1em;
    background-color: #002879;
    animation: blink 1s infinite;
}

@keyframes blink {
    0% { opacity: 1; }
    50% { opacity: 0; }
    100% { opacity: 1; }
}

.error-404__buttons {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 20px;
}

.error-404__button {
    padding: 10px 20px;
    border: none;
    border-radius: 25px; /* Añade bordes redondeados a los botones */
    background-color: #007BFF;
    color: white;
    cursor: pointer;
    text-decoration: none;
}

    </style>
</head>
<body <?php body_class(); ?>>
<div class="error-404__container">
        <?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) : ?>
            <div class="error-404__logo">
                <?php the_custom_logo(); ?>
            </div>
        <?php endif; ?>
        <div class="error-404__message">
            <span>Error 404</span>
        </div>
        <div class="error-404__buttons">
            <a href="<?php echo home_url(); ?>" class="error-404__button error-404__button--home">Volver a la home</a>
            <a href="<?php echo get_post_type_archive_link('coche'); ?>" class="error-404__button error-404__button--stock">Ver stock de coches</a>
        </div>
    </div>
</body>
</html>


