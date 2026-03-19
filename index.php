<?php
/**
 * @package 360vo-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
get_header(); ?>

<main>
    <section>
        <div class="container">
            <?php
            if ( have_posts() ) :
                while ( have_posts() ) : the_post();
                    get_template_part( 'template-parts/content', get_post_type() );
                endwhile;
            else :
                get_template_part( 'template-parts/content', 'none' );
            endif;
            ?>
        </div>
    </section>
</main>







<link rel="preconnect" href="https://rsms.me/">
<link rel="stylesheet" href="https://rsms.me/inter/inter.css">

<br><br>
Claro, aquí tienes algunos ejemplos de párrafos en HTML con las clases que mencionaste:

<p class="display_large">Este es un ejemplo de texto con la clase Display Large. Es un texto grande y llamativo que se utiliza para llamar la atención del usuario.</p>
<p class="display_medium">Este es un ejemplo de texto con la clase Display Medium. Es un texto de tamaño mediano que se utiliza para llamar la atención del usuario.</p>
<p class="display_small">Este es un ejemplo de texto con la clase Display Small. Es un texto pequeño que se utiliza para llamar la atención del usuario.</p>

<p class="headline_large">Este es un ejemplo de texto con la clase Headline Large. Es un texto grande y llamativo que se utiliza como titular.</p>
<p class="headline_medium">Este es un ejemplo de texto con la clase Headline Medium. Es un texto de tamaño mediano que se utiliza como titular.</p>
<p class="headline_small">Este es un ejemplo de texto con la clase Headline Small. Es un texto pequeño que se utiliza como titular.</p>

<p class="title_large">Este es un ejemplo de texto con la clase Title Large. Es un texto grande y llamativo que se utiliza como título.</p>
<p class="title_medium">Este es un ejemplo de texto con la clase Title Medium. Es un texto de tamaño mediano que se utiliza como título.</p>
<p class="title_small">Este es un ejemplo de texto con la clase Title Small. Es un texto pequeño que se utiliza como título.</p>

<p class="label_large">Este es un ejemplo de texto con la clase Label Large. Es un texto grande y llamativo que se utiliza como etiqueta.</p>
<p class="label_medium">Este es un ejemplo de texto con la clase Label Medium. Es un texto de tamaño mediano que se utiliza como etiqueta.</p>
<p class="label_small">Este es un ejemplo de texto con la clase Label Small. Es un texto pequeño que se utiliza como etiqueta.</p>

<p class="body_large">Este es un ejemplo de texto con la clase Body Large. Es un texto grande y fácilmente legible que se utiliza para el cuerpo del contenido.</p>
<p class="body_medium">Este es un ejemplo de texto con la clase Body Medium. Es un texto de tamaño mediano y fácilmente legible que se utiliza para el cuerpo del contenido.</p>
<p class="body_small">Este es un ejemplo de texto con la clase Body Small. Es un texto pequeño y fácilmente legible que se utiliza para el cuerpo del contenido.</p>


<p>Según ChatGPT, para móvil:</p>
<ul>
  <li class="display_medium">h1: display medium o small</li>
  <li class="headline_medium">h2: headline medium o small</li>
  <li class="body_large">Párrafo introducción body large</li>
  <li class="body_medium">Cuerpo body medium</li>
</ul>
<h1>Título h1</h1>
<h1>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quis in consectetur iste totam doloremque, quae perspiciatis </h1>
<h2>Título h2</h2>
<h2>Lorem ipsum dolor sit amet consectetur adipisicing elit. Saepe officia modi earum iusto fugit, quae sed sit atque consectetur quas perferendis maxime magnam? Deserunt recusandae dolore quisquam sit eius repellat.</h2>
<p>Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quis in consectetur iste totam doloremque, quae perspiciatis fugiat quos aut. Ullam culpa ex in magni? Quos deserunt aliquid nihil rerum aliquam.</p>




<?php get_footer(); ?>