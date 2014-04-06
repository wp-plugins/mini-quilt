<?php
/*
Plugin Name: Mini Quilt
Plugin URI: http://www.ikirudesign.com/plugins/mini-quilt/
Description: A unique way to show recent or random posts in your sidebar using a visually interesting quilt of your posts with colors derived by the <a href="http://www.ikirudesign.com/themes/kaleidoscope/">Kaleidoscope theme</a>'s color algorithm.
Author: david (b) hayes
Version: 0.8.2
Author URI: http://www.davidbhayes.com/
License: GPL 2.0 (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
*/

// -=- Putting the vital styling for the Mini Quilt in the head of the document
add_action( 'wp_print_styles', 'add_mq_stylesheet' );

function add_mq_stylesheet() {
	$myStyleUrl = WP_PLUGIN_URL . '/mini-quilt/mqstyle.css';
	$myStyleFile = WP_PLUGIN_DIR . '/mini-quilt/mqstyle.css';
	if ( file_exists( $myStyleFile ) ) {
		wp_register_style( 'myStyleSheets', $myStyleUrl );
		wp_enqueue_style( 'myStyleSheets' );
	}
}

// -=- Add our function to the widgets_init hook.
add_action( 'widgets_init', 'mq_load_widgets' );

function mq_load_widgets() {
	register_widget( 'Mini_Quilt_Widget' );
}

// -=- The Class Extension to make the Widget
class Mini_Quilt_Widget extends WP_Widget {
	function Mini_Quilt_Widget() {
		$widget_ops = array( 'classname' => 'mq', 'description' => 'A unique and visually interesting way to highlight recent or random posts.' ); // It's basic settings; below, it's control settings
		$control_ops = array( 'id_base' => 'mq-widget' );
		$this->WP_Widget( 'mq-widget', 'Mini Quilt', $widget_ops, $control_ops ); //makes the widget
	}
	
	function widget( $args, $instance ) {

		extract( $args );
		$widget_title = $instance['widget_title'];
		$rows_to_display = max( $instance['rows_to_display'], 1 ); //using max to keep safe from neg/nonint values
		$columns_to_display = max( $instance['columns_to_display'], 1 );
		$patch_width = $instance['patch_width'];
		$patch_height = $instance['patch_height'];
		$randomize = $instance['randomize'];
		$show_post_titles = $instance['show_post_titles'];

		$posts_to_display = $rows_to_display * $columns_to_display;
		$new_patch_width = max( 0, $patch_width - 10 ); //these correct for the padding which was
		$new_patch_height = max( 0, $patch_height - 4 );// necessary to make the text look ok		
		if ( $new_patch_height < 6 and !$show_post_titles ) { 
			$new_patch_height = 6; 
		}
		$main_width = ( $new_patch_width + 14 ) * $columns_to_display;

		echo $before_widget;
		echo $before_title.$widget_title.$after_title; ?>
		  <ul class="miniquiltbox" style="width: <?php echo $main_width ?>px;">
			<?php
				$recentPosts = new WP_Query();
				if ( $randomize ) {
				  $query = 'showposts='.$posts_to_display.'&ignore_sticky_posts=1&orderby=rand';
				}
				else {
				  $query = 'showposts='.$posts_to_display.'&ignore_sticky_posts=1';
				}
				$recentPosts->query( $query );
			 while ( $recentPosts->have_posts() ) : 
			 	$recentPosts->the_post(); 
				$test_id = get_the_ID(); ?>
				<?php if ( $show_post_titles ) { ?>
					<li><a style="background: #<?php if (is_single($test_id)) {echo 'bbb';} else {echo mq_date_to_color(get_the_time('z'), get_the_time('Y'));}; ?>;   width: <?php echo $new_patch_width; ?>px; height: <?php if ($new_patch_height>0) {echo $new_patch_height.'px';} else {echo 'auto';} ?>;" href="<?php the_permalink() ?>" rel="bookmark" title="&#8220;<?php the_title(); ?>&#8221; from <?php the_time('d M Y'); ?>"><?php the_title(); ?></a></li>
				<?php }
				else { ?>
					<li><a style="background: #<?php if (is_single($test_id)) {echo 'bbb';} else {echo mq_date_to_color(get_the_time('z'), get_the_time('Y'));}; ?>;   width: <?php echo $new_patch_width; ?>px; height: <?php echo $new_patch_height; ?>px;" href="<?php the_permalink() ?>" rel="bookmark" title="&#8220;<?php the_title(); ?>&#8221; from <?php the_time('d M Y'); ?>"></a></li>
			<?php	}
			 endwhile; ?>
		  </ul>
		<?php echo $after_widget;
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		// absint and strip_tags ensure nothing unsavory gets through
		$instance['widget_title'] = strip_tags( $new_instance['widget_title'] );
		$instance['rows_to_display'] = absint( $new_instance['rows_to_display'] );
		$instance['columns_to_display'] = absint( $new_instance['columns_to_display'] );
		$instance['patch_width'] = absint( $new_instance['patch_width'] );
		$instance['patch_height'] = absint( $new_instance['patch_height'] );
		$instance['randomize'] = isset($new_instance['randomize']);
		$instance['show_post_titles'] = isset($new_instance['show_post_titles']);

		return $instance;
	}
	
	function form( $instance ) {
		$defaults = array('widget_title'=>'The Mini Quilt', 'rows_to_display'=>5, 'columns_to_display'=>6, 'patch_width'=>20, 'patch_height'=>20, 'randomize'=>false, 'show_post_titles'=>false);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'widget_title' ); ?>">Title:</label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'widget_title' ); ?>" name="<?php echo $this->get_field_name( 'widget_title' ); ?>" value="<?php echo $instance['widget_title']; ?>" type="text" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'rows_to_display' ); ?>">Quilt Dimensions (rows x cols):</label>
			<input id="<?php echo $this->get_field_id( 'rows_to_display' ); ?>" name="<?php echo $this->get_field_name( 'rows_to_display' ); ?>" value="<?php echo $instance['rows_to_display']; ?>" type="text" size="3" /> x <input id="<?php echo $this->get_field_id( 'columns_to_display' ); ?>" name="<?php echo $this->get_field_name( 'columns_to_display' ); ?>" value="<?php echo $instance['columns_to_display']; ?>" type="text" size="3" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'patch_width' ); ?>">Patch Dimensions (width x height in <strong>px</strong>):</label>
			<input id="<?php echo $this->get_field_id( 'patch_width' ); ?>" name="<?php echo $this->get_field_name( 'patch_width' ); ?>" value="<?php echo $instance['patch_width']; ?>" type="text" size="3" /> x <input id="<?php echo $this->get_field_id( 'patch_height' ); ?>" name="<?php echo $this->get_field_name( 'patch_height' ); ?>" value="<?php echo $instance['patch_height']; ?>" type="text" size="3" />
		</p>
		
		<p>
		<em>To create a Mini Bar: set columns to 1, height to 0, and check Show Post Titles.</em>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['randomize'], 1 ); ?> id="<?php echo $this->get_field_id( 'randomize' ); ?>" name="<?php echo $this->get_field_name( 'randomize' ) ; ?>" />
			<label for="<?php echo $this->get_field_id( 'randomize' ); ?>">Randomize</label>
		</p>
		
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['show_post_titles'], 1 ); ?> id="<?php echo $this->get_field_id( 'show_post_titles' ); ?>" name="<?php echo $this->get_field_name( 'show_post_titles' ) ; ?>" />
			<label for="<?php echo $this->get_field_id( 'show_post_titles' ); ?>">Show Post Titles</label>
		</p>
		
<?php
	}
	
}
// -=- The Kaleidoscope Functions -- These make the colors
function mq_date_to_color( $day, $year ) {

	$red = mq_color_maker( $day, $year, 20, 134 ); //18, 134
	$green = mq_color_maker( $day, $year, 20, 240 ); //20, 240
	$blue = mq_color_maker( $day, $year, 10, 0 ); // 10, 0
	
	return $rgb = "{$red}{$green}{$blue}"; //concanate the calculated colors and return them
}
function mq_color_maker( $day, $year, $broaden = 0, $shift = 0 ) {

	$in_degree = .986 * $day; // from 365.25=>360
	
	/* pshift = 
	New degree value = incoming period shift + degree from year - sine function
	Sine Function = Random value * sine of shifted value 
		> This essentially works to make the coming cosine function stay near its peak for a while
		> based on the magnitude of the random value
	*/
	$pshift = $shift + $in_degree - ( $broaden*sin( (M_PI*( $in_degree+$shift ) )/180) ); 
	
	$year_diff = date( 'Y' ) - $year;
	$hshift = .08 * $year_diff; // to be used for further away years fading
	if ( $hshift > 1 ) { //this assures that the colors are always valid otherwise we could get negative numbers
		$hshift = 1;
	} 
	
	$HBASE = .82; //the center of the function; set between 0 and 2, lower are more saturated (darker)
	if ( $HBASE > 1 ) {
		$HSAFE = 2 - $HBASE; 
		// $hsafe is to ensure no final values greater than 2, which would create invalid colors
	} else {
		$HSAFE = $HBASE;
	}
	
	/* calced_color ==
	Use Cosine to create a weighted set of results between 0 and 2
	Multiply by 127.5 to get results between 0 and 255
	dechex for results between 0 and FF
	+Change the first (and only the first) +/- to toggle fade/darken
	*/ 
	$calced_color = dechex(127.5*(($HBASE-($HSAFE*$hshift))+(($HSAFE-($HSAFE*$hshift))*(cos((M_PI*($pshift))/180))))); 
	
	if ( strlen( $calced_color ) < 2 ) { 
	  $calced_color = '0'.$calced_color; // so add a zero if it's a single digit
	}	
	
	return $calced_color;
}
?>