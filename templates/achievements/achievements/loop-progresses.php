<?php
/**
 * Progress loop
 *
 * @package Achievements
 * @subpackage ThemeCompatibility
 */
?>

<?php do_action( 'dpa_template_before_progress_loop_block' ); ?>

<h1 class="dpa-single-progress-loop-heading"><?php _e( 'Unlocked By', 'dpa' ); ?></h1>

<?php do_action( 'dpa_template_before_progress_loop' ); ?>

<ul class="dpa-single-progress-unlockedby">

	<?php while ( dpa_progress() ) : dpa_the_progress(); ?>

		<?php dpa_get_template_part( 'loop-single-progress' ); ?>

	<?php endwhile; ?>

</ul>

<?php do_action( 'dpa_template_after_progress_loop' ); ?>

<?php do_action( 'dpa_template_after_progress_loop_block' ); ?>
