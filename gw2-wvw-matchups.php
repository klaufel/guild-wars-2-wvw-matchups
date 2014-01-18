<?php
/*
Plugin Name: Guild Wars 2 - WvW Matchups
Plugin URI: http://wordpress.org/plugins/guild-wars-2-wvw-matchups
Description: Plugin to display the live scores of WvW matchups in Guild Wars 2. Working with the API developed by ArenaNet.
Version: 1.1
Author: klaufel
Author URI: http://www.klaufel.com
License: GPLv2 or later
*/

if ( ! defined( 'WPINC' ) ) { die; }

wp_register_style('guild-wars-2-wvw-matchups', plugins_url('gw2-wvw-matchups.css',__FILE__ ));
wp_enqueue_style('guild-wars-2-wvw-matchups');

class wvw_matchups_widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'wvw_matchups_widget', // Base ID
			'GW2 - WvW Matchups', // Name
			array( 'description' => __( 'Use this widget to display the live scores of WvW matchups in Guild Wars 2', 'text_domain' ), ) // Args
		);
	}

	// Front-end display of widget.
	public function widget( $args, $instance ) {
		extract( $args );
		require_once 'src/PhpGw2Api/Service.php';
		$service = new PhpGw2Api\Service(__DIR__ . '/cache', 3600);
		$service->returnAssoc(true);
		$home_world = $instance['sample_dropdown'];	
		$lang = $instance['lang'];	
		$a = $service->getMatches();	
		foreach ($a as $v1) {
		    foreach ($v1 as $v2) {
		    	if(($v2['red_world_id'] == $home_world)||($v2['blue_world_id'] == $home_world)||($v2['green_world_id'] == $home_world)) {
		    		$wvw_match_id = $v2['wvw_match_id'];	
					$red_world_id = $v2['red_world_id'];
					$blue_world_id = $v2['blue_world_id'];
					$green_world_id = $v2['green_world_id'];
					if($home_world == $red_world_id) { $home_world_red_class = "home-world"; }
					if($home_world == $blue_world_id) { $home_world_blue_class = "home-world"; }
					if($home_world == $green_world_id) { $home_world_green_class = "home-world"; }
		    	}
				
		    }
		}
		$item = $service->getMatchDetails(array('match_id' => $wvw_match_id));	
		$a = $service->getWorldNames(array('lang' => $lang));
		foreach ($a as $v1) {
	    	if($v1['id'] == $red_world_id) { $red_world_name = $v1['name']; }
			if($v1['id'] == $blue_world_id) { $blue_world_name = $v1['name']; }
			if($v1['id'] == $green_world_id) { $green_world_name = $v1['name']; }
		}
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		$arra_world = array(
			"red" => array($item['scores'][0], "red", $red_world_name, $home_world_red_class),
			"blue" => array($item['scores'][1], "blue", $blue_world_name, $home_world_blue_class),
			"green" => array($item['scores'][2], "green", $green_world_name, $home_world_green_class)
		);
		arsort($arra_world);
		?>		
		<div id="gw2-wvw-matchups">			
			<?php
			foreach ($arra_world as $z1) {    
				?>
				<div class="match <?php echo $z1[1]; ?>">
					<span class="world <?php echo $z1[3]; ?>"><?php echo $z1[2]; ?></span>
					<span class="points"><?php echo number_format($z1[0]);?></span>
				</div>
				<?php
			}
			?>
		</div>
		<?php		
		echo $after_widget;
	}

	// Back-end widget form.
	public function form( $instance ) {
		require_once 'src/PhpGw2Api/Service.php';
		$service = new PhpGw2Api\Service(__DIR__ . '/cache', 3600);
		$service->returnAssoc(true);		
		if ( isset( $instance[ 'title' ] ) ) { $title = $instance[ 'title' ]; }
		else { $title = __( 'WvW Score', 'text_domain' ); }
		$sample_dropdown = esc_attr( $instance[ 'sample_dropdown' ] );
		$lang = esc_attr( $instance[ 'lang' ] );
		?>
		<p>
			<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('lang'); ?>"><?php _e( 'Languaje:' ); ?></label>
			<select name="<?php echo $this->get_field_name('lang'); ?>" id="<?php echo $this->get_field_id('lang'); ?>" class="widefat">
			<?php
			$prueba = array(en, fr, de, es);
			foreach($prueba as $valor ) {			
				?><option value="<?php echo $valor; ?>"<?php selected( $instance['lang'], $valor ); ?>><?php _e( $valor, 'dxbase' ); ?></option><?php	
			} 	
			?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('sample_dropdown'); ?>"><?php _e( 'World:' ); ?></label>
			<select name="<?php echo $this->get_field_name('sample_dropdown'); ?>" id="<?php echo $this->get_field_id('sample_dropdown'); ?>" class="widefat">
			<?php
			$prueba = $service->getWorldNames();	
			foreach($prueba as $valor ) {			
				?><option value="<?php echo $valor['id']; ?>"<?php selected( $instance['sample_dropdown'], $valor['id'] ); ?>><?php _e( $valor['name'], 'dxbase' ); ?></option><?php	
			} 	
			?>
			</select>
		</p>
		<?php
	}

	// Sanitize widget form values as they are saved.
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['sample_dropdown'] = strip_tags($new_instance['sample_dropdown']);
		$instance['lang'] = strip_tags($new_instance['lang']);
		return $instance;
	}

} // class wvw_matchups_widget

// register wvw_matchups_widget
add_action( 'widgets_init', function() { register_widget( 'wvw_matchups_widget' ); } );