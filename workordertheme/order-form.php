<?php
/**
 * Template name: Order Form
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
acf_form_head();
get_header('order'); ?>

	<div <?php generate_do_attr( 'content' ); ?>>
		<main <?php generate_do_attr( 'main' ); ?>>
			<?php
			/**
			 * generate_before_main_content hook.
			 *
			 * @since 0.1
			 */
			do_action( 'generate_before_main_content' );

			if ( generate_has_default_loop() ) {
				while ( have_posts() ) :

					the_post(); ?>

					
					<div id="page-sub-header">
			            <div class="container">
							<a href="/"><img class="headerimg" src="/wp-content/uploads/2022/08/download.png"></a>
			                <h1><?php the_title(); ?></h1>
			                <?php the_content(); ?>
			            </div>
			        </div>

			        <div id="prijava">
			            <?php acf_form(array(
					        'post_id'       => 'new_post',
					        'field_groups' => array('group_61dec795352cf'),
					        'updated_message' => __("You have successfully sent request, thank you. We will contact you shortly.", 'acf'),
					        'new_post'      => array(
					            'post_type'     => 'radni_nalog',
					            'post_status'   => 'draft',
					            'post_author'	=> 17,
					            'post_title' => $_POST['acf']['field_619f695f095a8'] . ' ' . $_POST['acf']['field_619f69e2095a9'] . ' - ' . $_POST['acf']['field_619f6a57e89ef'],
					            'recaptcha' => true
					        ),
					        'submit_value'  => 'Send Request'
					    )); ?>
			        </div>


			<?	endwhile; 
			}

			/**
			 * generate_after_main_content hook.
			 *
			 * @since 0.1
			 */
			do_action( 'generate_after_main_content' );
			?>
		</main>
	</div>

	<?php
	/**
	 * generate_after_primary_content_area hook.
	 *
	 * @since 2.0
	 */
	do_action( 'generate_after_primary_content_area' );
?>