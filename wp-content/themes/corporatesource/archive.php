<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Corporate_Source
 */

get_header(); ?>

	<?php
    /**
    * Hook - corporatesource_page_wrp_before.
    *
    * @hooked corporatesource_page_wrp_before - 11
    */
    do_action( 'corporatesource_page_wrp_before' );
    ?>

         
		<?php
		if ( have_posts() ) :

			if ( is_home() && ! is_front_page() ) : ?>
					<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
			<?php
			endif;

			/* Start the Loop */
			while ( have_posts() ) : the_post();

				/*
				 * Include the Post-Format-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
				 */
				get_template_part( 'template-parts/content', get_post_format() );

			endwhile;
			
			/**
			* Hook - corporatesource_posts_loop_navigation.
			*
			* @hooked corporatesource_posts_loop_navigation - 10
			*/
			do_action('corporatesource_posts_loop_navigation');
		

		else :

			get_template_part( 'template-parts/content', 'none' );

		endif; ?>
	<?php
    /**
    * Hook - corporatesource_page_wrp_after.
    *
	* @hooked corporatesource_blog_main_end - 10
	* @hooked corporatesource_blog_widgets - 20
	* @hooked corporatesource_page_wrp_after - 30
    */
    do_action( 'corporatesource_page_wrp_after' );
    ?>		
<?php

get_footer();
