<?php
/*
Plugin Name: Guild Wars 2 - WvW Matchups
Version: 2.0
Plugin URI: http://wordpress.org/plugins/guild-wars-2-wvw-matchups
Description: Plugin to display the live scores of WvW matchups in Guild Wars 2. Working with the API developed by ArenaNet.
Author: klaufel
Author URI: http://www.klaufel.com
License: GPLv2 or later
*/

if ( ! defined( 'WPINC' ) ) { die; }

function gw2_wvw_matchups_load_textdomain() {
	load_plugin_textdomain('gw2-wvw-matchups', false, dirname(plugin_basename( __FILE__ ) ) . '/languages/');
}
add_action( 'plugins_loaded', 'gw2_wvw_matchups_load_textdomain' );

wp_register_style('gw2-wvw-matchups', plugins_url('gw2-wvw-matchups.css',__FILE__ ));
wp_enqueue_style('gw2-wvw-matchups');

require (dirname(__FILE__).'/vesu/SDK/Gw2/Gw2SDK.php');
require (dirname(__FILE__) .'/vesu/SDK/Gw2/Gw2Exception.php');

use vesu\SDK\Gw2\Gw2SDK;
use vesu\SDK\Gw2\Gw2Exception;

class gw2_wvw_matchups_widget extends WP_Widget {

	public function __construct() {
		parent::__construct(
	 		'gw2_wvw_matchups',
			__('Guild Wars 2 - WvW Matchups', 'gw2-wvw-matchups'),
			array('description' => __('Displays real-time scores of WvW matchup in Guild Wars 2', 'gw2-wvw-matchups' ))
		);
	}	


	/**
	 * Widget - display front-end result of API
	 */
	public function widget($args, $instance) {
		extract($args);
		
		$cachedir = dirname(__FILE__).'/cache';
		if (substr(decoct(fileperms($cachedir)),2) != '777') { $gw2 = new Gw2SDK; }
		else { $gw2 = new Gw2SDK(dirname(__FILE__).'/cache'); }		
		
				
		$world_home_id = $instance['world'];	
		$lang = $instance['lang'];
		$skin = $instance['skin'];
		
		if (!$skin) { $skin = "vivid-colors"; } 
		
				
		$matches = $gw2->getMatchByWorldId($world_home_id);
		
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		if (!empty($title)) { echo $before_title . $title . $after_title; }
			
		foreach($matches as $match) { $scores = $gw2->getScoresByMatchId($match->wvw_match_id); } 

		// Consulto el nombre del mundo mediante el ID (temporal API Anet caida)
		$world_names_dir = file_get_contents(dirname(__FILE__).'/world_names.json');
		$world_names = json_decode($world_names_dir,true);
		foreach($world_names as $world) {
			if($world['id'] == $match->red_world_id) { $red_world_name = $world['name']; }
			if($world['id'] == $match->blue_world_id) { $blue_world_name = $world['name']; }
			if($world['id'] == $match->green_world_id) { $green_world_name = $world['name']; }
			
			if($match->red_world_id == $world_home_id) { $red_world_home = "home-world"; } else { $red_world_home = ""; }
			if($match->blue_world_id == $world_home_id) { $blue_world_home = "home-world"; } else { $blue_world_home = ""; }
			if($match->green_world_id == $world_home_id) { $green_world_home = "home-world"; } else { $green_world_home = ""; }  
		}		

		// Ordeno todos los resultados			
		$match_sort = array(
			"red" => array("world_score" => $scores[0], "world_color" => "red", "world_name" => $red_world_name, "world_home" => $red_world_home),
			"blue" => array("world_score" => $scores[1], "world_color" => "blue", "world_name" => $blue_world_name, "world_home" => $blue_world_home),
			"green" => array("world_score" => $scores[2], "world_color" => "green", "world_name" => $green_world_name, "world_home" => $green_world_home)
		);

		// Ordeno de mayor puntuación a menor
		arsort($match_sort);			
		
		echo '<div class="gw2-wvw-matchups '.$skin.'">';
		
		// Imprimo los resultados y les doy formato
		?><ul class="gw2-matchups"><?php		
		foreach ($match_sort as $match_detail) {				    
			?>
			<li class="match <?php echo $match_detail['world_color']; ?>">
				<span class="world <?php echo $match_detail['world_home']; ?>"><?php echo $match_detail['world_name']; ?></span>
				<span class="points"><?php echo number_format($match_detail['world_score']); ?></span>
			</li>						
			<?php
		}
		?></ul><?php

		/**
		 * Show objectives table if selected in the widget		
		 */
		if($instance['objectives']) {
			
			$objectives = $gw2->getMatchDetails($match->wvw_match_id);
		
			$red_camp = 0; $blue_camp = 0; $green_camp = 0;		
			$red_tower = 0; $blue_tower = 0; $green_tower = 0;
			$red_keep = 0; $blue_keep = 0; $green_keep = 0;
			$red_castle = 0; $blue_castle = 0; $green_castle = 0;
			
			foreach($objectives->maps as $v) {
				foreach($v->objectives as $z) {							
					$objective_id = $z->id;
					$objective_owner = $z->owner;
					$objective_name	= $gw2->parseObjectiveName($objective_id);
					if($objective_id < 62) {							
						if($objective_owner == "Red") {					
							if($objective_name == "Tower") { $red_tower++; }
							elseif($objective_name == "Keep") { $red_keep++; }	
							elseif($objective_name == "Castle") { $red_castle++; }		
							else { $red_camp++; }					
						}	
						if($objective_owner == "Green") {												
							if($objective_name == "Tower") { $green_tower++; }
							elseif($objective_name == "Keep") { $green_keep++; }	
							elseif($objective_name == "Castle") { $green_castle++; }	
							else { $green_camp++; }			
						}	
						if($objective_owner == "Blue") {					
							if($objective_name == "Tower") { $blue_tower++; }
							elseif($objective_name == "Keep") { $blue_keep++; }
							elseif($objective_name == "Castle") { $blue_castle++; }		
							else { $blue_camp++; }				
						}
					}						
				}							
			}
	
			// Ordered by the score all results
			$objectives_sort = array(
				"red" => array("world_score" => $scores[0], "world_color" => "red", "camp" => $red_camp, "tower" => $red_tower, "keep" => $red_keep, "castle" => $red_castle, "world_home" => $red_world_home),
				"blue" => array("world_score" => $scores[1], "world_color" => "blue", "camp" => $blue_camp, "tower" => $blue_tower, "keep" => $blue_keep, "castle" => $blue_castle, "world_home" => $blue_world_home),
				"green" => array("world_score" => $scores[2], "world_color" => "green", "camp" => $green_camp, "tower" => $green_tower, "keep" => $green_keep, "castle" => $green_castle, "world_home" => $green_world_home)
			);				
			arsort($objectives_sort);
			
			// Print and formating the objectives result
			?>
			<ul class="gw2-objectives">
				<li class="leyend">
					<span class="world"><?php _e('Worlds', 'gw2-wvw-matchups'); ?></span>
					<span class="camp" title="<?php _e('Campament', 'gw2-wvw-matchups'); ?>"><?php _e('Campament', 'gw2-wvw-matchups'); ?></span>
					<span class="tower" title="<?php _e('Tower', 'gw2-wvw-matchups'); ?>"><?php _e('Tower', 'gw2-wvw-matchups'); ?></span>
					<span class="keep" title="<?php _e('Keep', 'gw2-wvw-matchups'); ?>"><?php _e('Keep', 'gw2-wvw-matchups'); ?></span>
					<span class="castle" title="<?php _e('Castle', 'gw2-wvw-matchups'); ?>"><?php _e('Castle', 'gw2-wvw-matchups'); ?></span>
				</li>
			<?php		
			foreach ($objectives_sort as $objective) {				    
				?>
				<li class="objective <?php echo $objective['world_home']; ?>">
					<span class="world <?php echo $objective['world_color']; ?>"><?php echo $objective['world_color']; ?></span>
					<span><?php echo $objective['camp']; ?></span>
					<span><?php echo $objective['tower']; ?></span>
					<span><?php echo $objective['keep']; ?></span>
					<span><?php echo $objective['castle']; ?></span>
				</li>	
				<?php
			}
			?></ul><?php	
		} // END Objectives Table
		
		echo '</div>'.$after_widget;
	}


	/**
	 * Back-end widget form.
	 */
	public function form( $instance ) {
		if (isset( $instance['title'])) { $title = $instance[ 'title' ]; }
		else { $title = __('WvW Score', 'gw2-wvw-matchups'); }
		$world = esc_attr( $instance['world']);
		$lang = esc_attr($instance['lang']);
		$objectives = esc_attr($instance['objectives']);
		?>
		<p>
			<label for="<?php echo $this->get_field_name('title'); ?>"><?php _e('Title:', 'gw2-wvw-matchups'); ?></label> 
			<input name="<?php echo $this->get_field_name('title'); ?>" id="<?php echo $this->get_field_id('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" class="widefat" />
		</p>		
		<p>
			<label for="<?php echo $this->get_field_id('lang'); ?>"><?php _e('Language:', 'gw2-wvw-matchups'); ?> <small><?php _e('(English only temporarily)', 'gw2-wvw-matchups'); ?></small></label>
			<select name="<?php echo $this->get_field_name('lang'); ?>" id="<?php echo $this->get_field_id('lang'); ?>" class="widefat">
			<?php		
				$languages = array(
					"en" => array("lang_id" => "en", "lang_name" => __('English (EN)', 'gw2-wvw-matchups')),
					"fr" => array("lang_id" => "fr", "lang_name" => __('French (FR)', 'gw2-wvw-matchups')),
					"de" => array("lang_id" => "de", "lang_name" => __('German (DE)', 'gw2-wvw-matchups')),
					"es" => array("lang_id" => "es", "lang_name" => __('Spanish (SP)', 'gw2-wvw-matchups'))
				);
				foreach($languages as $lang) {
					?><option value="<?php echo $lang['lang_id']; ?>" <?php selected($instance['lang'], $lang['lang_id']); ?>><?php echo $lang['lang_name']; ?></option><?php	
				} 	
			?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('world'); ?>"><?php _e('World:', 'gw2-wvw-matchups'); ?></label>
			<select name="<?php echo $this->get_field_name('world'); ?>" id="<?php echo $this->get_field_id('world'); ?>" class="widefat">
			<?php
				$world_names_dir = file_get_contents(dirname(__FILE__).'/world_names.json');
				$world_names = json_decode($world_names_dir,true);
				foreach($world_names as $world) {
					?><option value="<?php echo $world['id']; ?>"<?php selected($instance['world'], $world['id']); ?>><?php echo $world['name']; ?></option><?php			  
				} 
			?>			
			</select>
		</p>
		<p>
			<input name="<?php echo $this->get_field_name('objectives'); ?>" id="<?php echo $this->get_field_id('objectives'); ?>" type="checkbox" value="1" <?php checked( '1', $objectives ); ?> />
			<label for="<?php echo $this->get_field_id('objectives'); ?>"><?php _e('Show objectives table?', 'gw2-wvw-matchups'); ?></label>
		</p>    
		<?php $cachedir = dirname(__FILE__).'/cache'; if (substr(decoct(fileperms($cachedir)),2) != '777') : ?>			
			<p style="background: red; color: #fff; padding: 5px; margin-top: 20px;"><b>IMPORTANT:</b> To reduce load time you should look at the permissions on the folder 'cache' in the plugin directory `/wp-content/plugins/guild-wars-2-wvw-matchups/cache`. You have to give write permissions (777).</p>
		<?php endif; ?>
		<?php
	}


	/**
	 * Sanitize widget form values as they are saved.
	 */
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['world'] = strip_tags($new_instance['world']);
		$instance['lang'] = strip_tags($new_instance['lang']);
		$instance['objectives'] = strip_tags($new_instance['objectives']);
		return $instance;
	}

}

// Load Widget - GW2 WvW Matchups
add_action('widgets_init', function() { register_widget('gw2_wvw_matchups_widget'); } );