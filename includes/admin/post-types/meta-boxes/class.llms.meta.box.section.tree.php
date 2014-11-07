<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box Video
*
* diplays text input for oembed video
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_Section_Tree {

	/**
	 * Set up video input
	 *
	 * @return string
	 * @param string $post
	 */
	public static function output( $post ) {
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$parent_course = get_post_meta( $post->ID, '_parent_course', true );
		$course_edit_link = '';
		$course_edit_link_html = '';
		$lessons = '';
		
		$course_args = array(
			'posts_per_page'   => -1,
			'post_status'      => 'publish',
			'orderby'          => 'title',
			'order'            => 'ASC',
			'post_type'        => 'course',
			'suppress_filters' => true 
		); 
		$courses = get_posts($course_args);

		if ($parent_course ) {
			$course = new LLMS_Course($parent_course);
		}
		else {
			//need to check if parent course exists
			foreach ($courses as $key => $value) {

				$course = new LLMS_Course($value->ID);
				$sections = $course->get_sections();

				if (!empty($sections)) {
					if (in_array($post->ID, $sections)) {
						$parent_course = $value->ID;
						break;
					}
				}
			}
		}

		if ($parent_course) {
			$course_edit_link = get_edit_post_link($parent_course);
			$course_edit_link_html = '<a href="' . $course_edit_link .'">(View Course)</a>';
			$course = new LLMS_Course($parent_course);
			$course_tree = $course->get_syllabus();
			foreach ($course_tree as $key => $value) {
				if ($value['section_id'] == $post->ID) {
					LLMS_log($value['lessons']);
					$lessons = $value['lessons'];
				}
			}
		}
		else {
			$args = array(
				'post_type'   => 'lesson',
				'meta_query'  => array(
				array(
					'key' => '_parent_section',
					'value' => $post->ID,
					)
				)
			);
			$lessons_query = get_posts($args);
			$lessons = array();
			$i = 0;
			foreach($lessons_query as $key => $value) {
				$lessons[$i]['lesson_id'] = $value->ID;
				$lessons[$i]['position'] = $i + 1;
				$i++;
			}

		}
		?>

		<div id="llms-access-options">
			<div class="llms-access-option">
				<label class="llms-access-levels-title"><?php _e('Associated Course '. $course_edit_link_html, 'lifterlms') ?></label>
				<select data-placeholder="Choose a course..." style="width:350px;" id="associated_course" single name="associated_course" class="chosen-select">
					<option value="" selected>Select a course...</option>
					<?php foreach($courses as $key => $value) { 
							if ($value->ID == $parent_course) {
					?>
								<option value="<?php echo $value->ID; ?>" selected ><?php echo $value->post_title; ?></option>
							<?php } else { ?>
						<option value="<?php echo $value->ID; ?>"><?php echo $value->post_title; ?></option>
					<?php } } ?>
				</select>
			</div>

			<div class="llms-access-levels">
			
				<span class="llms-access-levels-title"><?php _e( 'Lessons in this section', 'lifterlms' ) ?></span> 
					<?php
					if ($lessons) :
						foreach ($lessons as $key => $value) :
							$lesson = get_post($value['lesson_id']);
							echo '<ul class="llms-lesson-list"><li>';
							echo '<span><a href="' . get_edit_post_link($lesson->ID) . '"><i class="fa fa-book"></i> ' . $lesson->post_title . '</a></span>';
							echo '</li></ul>';
						endforeach;
					endif;
					?>
			</div>
		</div>

		<?php  
	}

	public static function save( $post_id, $post ) {
		global $wpdb;
LLMS_log('the post associated_course started');
		if ($_POST['associated_course']) {

			$parent_course = ( llms_clean( $_POST['associated_course']  ) );
			$lessons = array();

			if ($parent_course) {
				//check if section already belongs to course
				//first check if section has a parent course assigned
				$prev_parent = get_post_meta($post_id, '_parent_course', true);
				if ($prev_parent == $parent_course) {
					return;
				}

				//if no parent course assigned in db then check the courses
				if (!$prev_parent) {
					LLMS_log('no previous parent exists');
					$course_args = array(
						'posts_per_page'   => -1,
						'post_status'      => 'publish',
						'orderby'          => 'title',
						'order'            => 'ASC',
						'post_type'        => 'course',
						'suppress_filters' => true 
					); 
					$courses = get_posts($course_args);
					foreach ($courses as $key => $value) {
						$course = new LLMS_Course($value->ID);
						$sections = $course->get_sections();

						if (!empty($sections)) {
							if (in_array($post->ID, $sections)) {
								$prev_parent = $value->ID;
								LLMS_log('found  the prev parent');
								LLMS_log($prev_parent);
								break;
							}
						}
					}
				}
LLMS_log('parent_course ' . $parent_course);
LLMS_log('prev_course ' . $prev_parent);
				//if section belongs to another course remove it from the previous course
				if ($prev_parent && $prev_parent != $parent_course) {
LLMS_log('starting cool part');
LLMS_log($prev_parent);
					$prev_course = new LLMS_Course($prev_parent);
					$sections = $prev_course->get_sections();

					if (in_array($post_id, $sections)) {
						$pc_syllabus = $prev_course->get_syllabus();
LLMS_log('finding lessons start');
						foreach($pc_syllabus as $key => $value) {
LLMS_log('finding lessons foreach');
							if ($value['section_id'] == $post_id) {
								LLMS_log('finding lessons');
								//LLMS_log($value);
								$lessons = $value['lessons'];
								unset($pc_syllabus[$key]);
								$pc_syllabus  = array_values($pc_syllabus);
								update_post_meta($prev_course->id, '_sections', $pc_syllabus);
							}
						}
					}
				}

				//append section to new course
				$course = new LLMS_Course($parent_course );
				$syllabus = $course->get_syllabus();
				if (!$syllabus) {
					$syllabus = array();
				}
				$section_count = count($syllabus);

				$section_tree = array();
				$section_tree['section_id'] = $post_id;
				$section_tree['position'] = $section_count + 1;

				//find lessons and add them. 
				//check if previous course section area had lessons
				if (empty($lessons)) {
					LLMS_log('lessons is empty');
					$lesson = array();
					$i = 0;
					$lesson_ids = $wpdb->get_col("SELECT post_id
					FROM $wpdb->postmeta WHERE meta_key = '_parent_section'" );

					$position = 0;
					if ($lesson_ids) {
						foreach($lesson_ids as $lesson_id) {
							update_post_meta($lesson_id, '_parent_course', $parent_course);
							$position++;
							$lesson['lesson_id'] = $lesson_id;
							$lesson['position'] = $position;
							$lessons[$i] = $lesson;
							$i++;
						}
					}
				}
				else {
					foreach ($lessons as $key => $value) {
						update_post_meta($value['lesson_id'], '_parent_course', $parent_course);

					}
				}

				//append lessons array
				$section_tree['lessons'] = $lessons;

				//add section array to course and save parent_course variable
				array_push($syllabus, $section_tree);
				LLMS_log('syllabus right before saving');
				LLMS_log($syllabus);
				update_post_meta( $post_id, '_parent_course', $parent_course );
				update_post_meta( $parent_course, '_sections', $syllabus);

			}
		}
		
	}

}