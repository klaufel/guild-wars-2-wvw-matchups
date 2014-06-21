<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" lang="es-ES">
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" lang="es-ES">
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html lang="es-ES">
<!--<![endif]-->
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width" />



<link rel='stylesheet' id='open-sans-css'  href='//fonts.googleapis.com/css?family=Open+Sans%3A300italic%2C400italic%2C600italic%2C300%2C400%2C600&#038;subset=latin%2Clatin-ext&#038;ver=3.8.1' type='text/css' media='all' />

<link rel='stylesheet' id='twentytwelve-fonts-css'  href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700&#038;subset=latin,latin-ext' type='text/css' media='all' />
<link rel='stylesheet' id='twentytwelve-style-css'  href='http://127.0.0.1/wordpress/wp-content/themes/twentytwelve/style.css?ver=3.8.1' type='text/css' media='all' />
<link rel='stylesheet' id='guild-wars-2-wvw-matchups-css'  href='http://localhost/wordpress/wp-content/plugins/guild-wars-2-wvw-matchups/gw2-wvw-matchups.css?ver=3.9.1' type='text/css' media='all' />
</head>

<body class="custom-font-enabled" style="width:30%; margin: 0 auto; background: #fff;">
	
<?php 
require (dirname(__FILE__).'/vesu/SDK/Gw2/Gw2SDK.php');
require (dirname(__FILE__) .'/vesu/SDK/Gw2/Gw2Exception.php');

use vesu\SDK\Gw2\Gw2SDK;
use vesu\SDK\Gw2\Gw2Exception;

	$cachedir = dirname(__FILE__).'/cache';
		if (substr(decoct(fileperms($cachedir)),2) != '777') {
			$gw2 = new Gw2SDK; 
		} else {
			$gw2 = new Gw2SDK(dirname(__FILE__).'/cache');
		}		
		
				
				
				
		$world_home_id = 2013;	
		
		$matches = $gw2->getMatchByWorldId($world_home_id);
		
		
		?><div class="gw2-wvw-matchups"><?php
		
		// Scores del partido
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
		
		// Show objectives table if selected in the widget
		
		
		if($instance['objectives'] = true) {
			
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
					<span class="world" style="background: none;">6</span>
					<span class="camp" title="Camp">Camp</span>
					<span class="tower" title="Tower">Tower</span>
					<span class="keep" title="Keep">Keep</span>
					<span class="castle" title="Castle">Castle</span>
				</li>
			<?php		
			foreach ($objectives_sort as $objective) {				    
				?>
				<li class="objective <?php echo $objective['world_home']; ?>">
					<span class="world <?php echo $objective['world_color']; ?>">-</span>
					<span><?php echo $objective['camp']; ?></span>
					<span><?php echo $objective['tower']; ?></span>
					<span><?php echo $objective['keep']; ?></span>
					<span><?php echo $objective['castle']; ?></span>
				</li>	
				<?php
			}
			?></ul><?php	
		}
		
		
		
		
		
		
		
	
		
		?>
		
		</div>


</body>

</html>