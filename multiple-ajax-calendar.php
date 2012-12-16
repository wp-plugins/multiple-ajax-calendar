<?php
/*
Plugin Name: Multiple Ajax Calendar
Plugin URI: http://thesquaremedia.com/blog/plugins/multiple-ajax-calendar/
Description: The wordpress calendar widget enhanced to allow multiple instances of it in one page. 
Version: 1.0
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Author: Xavier Serrano
Author URI: http://thesquaremedia.com/blog/
*/

class MultipleAjaxCalendarWidget extends WP_Widget {
	var $category_ids = array();

	function MultipleAjaxCalendarWidget() {
		$widget_ops  = array( 'classname' => 'multiple_ajax_calendar_widget', 'description' => __( 'Ajax Calendar that Allows you to add more than one instance of it.', 'multiple-ajax-calendar' ) );
		$control_ops = array( 'width' => 300, 'height' => 300 );

		$this->WP_Widget( 'multiple-ajax-calendar', __( 'Multiple Ajax Calendar', 'multiple-ajax-calendar' ), $widget_ops, $control_ops );

		add_action( 'template_redirect', array( &$this, 'template_redirect' ) );
		wp_enqueue_script('jquery');
	}
	
	function template_redirect() {
		global $variable,$jVariable,$widget_id;
		if ( is_date() && isset( $_GET['ajax'] ) && $_GET['ajax'] == 'true' ) {
			$settings = $this->get_settings();
			$settings = $settings[$this->number];
			$search=array("-","_"); 
			$replace=array("",""); 
			$widget_id=$_GET['widget_id'];
			$variable=str_replace($search,$replace,$_GET['widget_id']);
			$jVariable='#'.$_GET['widget_id'];
			$instance     = wp_parse_args( $settings, array( 'title' => __( 'AJAX Calendar', 'ajax-calendar' ) ) );
			echo $this->getMultipleAjaxCalendar();
			die();
		}
	}
	
	/**
	 * Display the widget
	 *
	 * @param string $args Widget arguments
	 * @param string $instance Widget instance
	 * @return void
	 **/
	function widget( $args, $instance ) {
		extract( $args );
		$search=array("-","_"); 
		$replace=array("",""); 
		global $variable,$jVariable,$widget_id;
		$widget_id=$args['widget_id'];
		$variable=str_replace($search,$replace,$args['widget_id']);
		$jVariable='#'.$args['widget_id'];
		$instance     = wp_parse_args( (array)$instance, array( 'title' => __( 'AJAX Calendar', 'ajax-calendar' ), 'category_id' => '' ) );
		$title        = apply_filters( 'widget_title', $instance['title'] );
		
		echo $before_widget;
	
		if ( $title )
			echo $before_title . stripslashes( $title ) . $after_title;

		echo $this->getMultipleAjaxCalendar();

		// MicroAJAX: http://www.blackmac.de/index.php?/archives/31-Smallest-JavaScript-AJAX-library-ever!.html
?>
<script type="text/javascript">

function calendar_AJAX_<?php echo $variable; ?>(theURL,action,wID){  
					jQuery.ajax({
						type	: "GET",
                        url     : theURL,
						data: { ajax: action, widget_id: wID },
                        success : function(response) {
                            // The server has finished executing PHP and has returned something,
                            // so display it!
                            jQuery("<?php echo $jVariable; ?> .wp-calendar").html(response);
                        }
                    });
				  }
</script>
<?php
		// After
		echo $after_widget;
	}
	
	function getMultipleAjaxCalendar() {
		global $wpdb, $m, $monthnum, $year, $wp_locale, $posts,$variable,$jVariable,$widget_id;
		
		$text = get_calendar( true, false );
	
		
		$text = str_replace( 'id="wp-calendar"', 'class="wp-calendar"', $text );
		
		$text = str_replace( '<td colspan="3" id="next"><a', '<td colspan="3" id="next"><a onclick="calendar_AJAX_'.$variable.'(jQuery(this).attr(\'href\'),true,\''.$widget_id.'\'); return false"', $text );
		$text = str_replace( '<td colspan="3" id="prev"><a', '<td colspan="3" id="prev"><a onclick="calendar_AJAX_'.$variable.'(jQuery(this).attr(\'href\'),true,\''.$widget_id.'\'); return false"', $text );
		return $text;
	}
		
	
	/**
	 * Display config interface
	 *
	 * @param string $instance Widget instance
	 * @return void
	 **/
	function form( $instance ) {
		$instance = wp_parse_args( (array)$instance, array( 'title' => __( 'Multiple Ajax Calendar', 'multiple-ajax-calendar' ) ) );

		$title        = stripslashes( $instance['title'] );

		?>
<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'multiple-ajax-calendar' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label><br /></p>
		<?php
	}
		
	/**
	 * Save widget data
	 *
	 * @param string $new_instance
	 * @param string $old_instance
	 * @return void
	 **/
	function update( $new_instance, $old_instance ) {
		$instance     = $old_instance;
		$new_instance = wp_parse_args( (array)$new_instance, array( 'title' => __( 'Multiple Ajax Calendar', 'multiple-ajax-calendar' ) ) );

		$instance['title']        = wp_filter_nohtml_kses( $new_instance['title'] );
		
		return $instance;
	}
}

function register_multiple_ajax_calendar_widget() {
	register_widget( 'MultipleAjaxCalendarWidget' );
}

add_action( 'widgets_init', 'register_multiple_ajax_calendar_widget' );

