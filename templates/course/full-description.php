<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit; 

global $post, $course;

?>
<div class="llms-full-description">
	hello
	<?php echo apply_filters( 'lifterlms_full_description', $post->post_content ) ?>

</div>