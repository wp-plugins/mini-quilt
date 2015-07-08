<?php 
class Mini_Quilt_Widget extends WP_Widget {
	function __construct() {
		$widget_ops = array( 
			'classname' => 'mq', 
			'description' => 'A unique and visually interesting way to highlight recent or random posts.' 
		);
		$control_ops = array( 'id_base' => 'mq-widget' );
		parent::__construct( 'mq-widget', 'Mini Quilt', $widget_ops, $control_ops ); //makes the widget
	}
	
	function widget( $args, $instance ) {
		echo $args['before_widget'];
		echo $args['before_title'].$instance['widget_title'].$args['after_title']; 
		echo self::show_widget_body($instance);
		echo $args['after_widget'];
	}

	function show_widget_body($instance) {
		$rows = max( $instance['rows_to_display'], 1 ); 
		$columns = max( $instance['columns_to_display'], 1 );	
		$posts_to_display = $rows * $columns;
		$show_post_titles = $instance['show_post_titles'];

		$patch_width = max( 0, $instance['patch_width'] ); //these correct for the padding which was
		$main_width_padding = 1 * 2;
		$main_width_border = 1 * 2;
		$main_width_correction = $main_width_padding + $main_width_border;
		$main_width = $patch_width * $columns + $main_width_correction;


		$patch_height = max( 0, $instance['patch_height'] );
		if ( !$show_post_titles ) { 
			$patch_height = max( 6, $patch_height ); 
		}
		$patch_height_text = $patch_height.'px';
		if ( $patch_height === 0 ) {
			$patch_height_text = 'auto';	
		}

		$query = 'showposts='.$posts_to_display.'&ignore_sticky_posts=1';
		if ( $instance['randomize'] ) {
		  $query .= '&orderby=rand';
		}
		$recentPosts = new WP_Query( $query );
		
		echo '<ul class="miniquiltbox" style="box-sizing: border-box; width: '.$main_width.'px;">';
		while ( $recentPosts->have_posts() ) : 
		 	$recentPosts->the_post(); 
			if (is_single(get_the_ID())) {
				$background_color = 'bbb';
			} else {
				$background_color = mq_date_to_color(
					get_the_time('z'),
					get_the_time('Y')
				);
			}; 
			$inline_style = "box-sizing: border-box; 
				background: #{$background_color};
				width: {$patch_width}px;
				height: {$patch_height_text};";
			?>
			<li>
				<a style="<?php echo $inline_style; ?>" href="<?php the_permalink() ?>" title="&rdquo;<?php the_title_attribute(); ?>&ldquo; from <?php the_time('d M Y'); ?>">
					<?php if ( $show_post_titles ) { 
						the_title();
					}?>
				</a>
			</li>
<?php 
		endwhile; 
		echo '</ul>';
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
		$defaults = array(
			'widget_title'=>'The Mini Quilt',
			'rows_to_display'=>5,
			'columns_to_display'=>6,
			'patch_width'=>20,
			'patch_height'=>20,
			'randomize'=>false,
			'show_post_titles'=>false
		);
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