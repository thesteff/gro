
<!-- Pour detecter le mode de Bootstrap (xs sm md lg) !-->
<script src="<?php echo base_url();?>/ressources/script/bootstrap-toolkit.js" /></script>

<!-- Pour gérer les events des écrans tactiles !-->
<script src="<?php echo base_url();?>/ressources/script/hammer.min.js" /></script>


<script type="text/javascript">


	(function($, viewport){
		$(document).ready(function() {
			
			// On instancie le player
			$player = $("#player");
			
			// On rend chaque ligne cliquable
			setupTriggers();
			
			// On utilise le carousel uniquement en XS
			if(viewport.is('xs')) {
				addCarousel("bottom_bar");
			}
			
			
			/******** ECRAN TACTILES **********/
			var myElement = document.getElementById('bottom_bar');
			var hammertime = new Hammer(myElement);
			hammertime.on('swipeleft swiperight', function(ev) {
				// RIGHT
				if (ev.type == 'swipeleft') goRight("bottom_bar");
				// LEFT
				else if (ev.type == 'swiperight') goLeft("bottom_bar");
			});
			
			
			/******** KEYBOARD **********/
			/*$("body").on("keydown", function(event) {
				// RIGHT
				if (event.which == 39) goRight("bottom_bar");
				// LEFT
				else if (event.which == 37) goLeft("bottom_bar");

			});*/
			
			
			$(window).resize(
				viewport.changed(function() {
					if ( viewport.is('xs') && !$("#bottom_bar #carousel").length) {
						addCarousel("bottom_bar");
					}
					else if ( !viewport.is('xs') && $("#bottom_bar #carousel").length ) {
						removeCarousel("bottom_bar");
					}
				})
			); 
			
		});
	})(jQuery, ResponsiveBootstrapToolkit);
	
	
	
	/******** Permet d'utiliser un Carousel en mode XS **********/	
	function addCarousel($target) {
		//console.log("addCarousel");
		carouselHTML = '<div id="carousel" class="carousel slide" data-ride="carousel" data-interval="false" data-wrap="false" data-keyboard="false">';
		carouselHTML += '<div class="carousel-inner">';
		$("#"+$target+" .item").wrapAll(carouselHTML);
	}


	function removeCarousel($target) {
		//console.log("removeCarousel");
		$("#"+$target+" .item").unwrap();
		$("#"+$target+" .item").unwrap();
	}

	
	function goRight($target) {
		$("#"+$target+" #carousel").carousel("next");
	}
	
	function goLeft($target) {
		$("#"+$target+" #carousel").carousel("prev");
	}
	
	
	
	
	
	function update_player(morceauId = -1, versionId, play = false) {
		
		$("#bottom_block_title").html("...");
		$("#bottom_pdf_block").html("...");
		
		$.post("<?php echo site_url('ajax_version/get_version_infos'); ?>",
			
			{'morceauId': morceauId,
			'versionId': versionId
			},
		
			function (msg) {
				
				$data = JSON.parse(msg);
				
				// Titre
				var compo = "";
				if ($data.version.artisteLabel) compo = " - <small>"+$data.version.artisteLabel+"</small>";
				$("#bottom_block_title").html($data.version.titre+compo);
				
				// Player
				if ($data.version.mp3URL) {
					if ($data.version.collection != "") $subPath = $data.version.collection+"/";
					else $subPath = "";
					console.log("PLAYER.php :: update_player() MP3Path : <?php echo base_url(); ?>/ressources/morceaux/"+$data.version_path+"/"+$subPath+$data.version.mp3URL);
					$player.attr("src",'<?php echo base_url("ressources/morceaux/") ?>/'+$data.version_path+"/"+$subPath+$data.version.mp3URL);
					$player.css("display","block");
					
					// On joue le titre si besoin
					if (play) $player.trigger("play");
					
				}
				else {
					// STOPPER LE PLAYER !!!!!!!!!!
					$player.css("display","none");
				}
				
				// On actualise le block droit
				$("#bottom_list").empty();
				//$("#bottom_list_block").append("<p>Liste des documents</p>");
				$pdf_img = "<img width='12' src='<?php echo base_url(); ?>/images/icons/pdf_icon_16.png' />";
				$.each($data.medias, function($index, $val) {
					$ref_pdf = "<a href='<?php echo base_url('ressources/morceaux/') ?>/"+$data.version_path+"/"+$data.version.collection+"/"+$val.URL.replace(/\s/g,'%20')+"' target='_blanck'>"+$val.URL+"</a>";
					$("#bottom_list").append("<div>"+$pdf_img+$ref_pdf+"<div>");
				});
				
			}
		);
    }
	
	
	/**** PLAY SONG  *****/
	// Généralement, le player est utilisé avec un ou plusieurs tablesorter playable (tab en mémoire dans d'autre Block)
	// Permet de fixer le comportement des clic sur titre de morceaux
	function setupTriggers() {

		$(".is_playable").each(function() {
			// On évite de surcharger les listener s'ils existent déjà
			if ($(this).hasClass("updated")) return;
			
			// tableau normal (on select toute la tr)
			if ( ! $(this).hasClass("firstTD") ) {
				// On vérifie qu'on est bien sur une ligne de morceau (sinon problème lors d'un input sur les filtres de recherche)
				$(this).find("tbody tr[versionId!='-1']:not(.tablesorter-filter-row)").each(function() {
					$(this).on("click", function() {
						// On déselectionne la tr précédente (de tous les tab is_playable)
						$(".is_playable tbody").find(".selected").removeClass("selected");
						// La tr devient selected
						$(this).addClass("selected");
						// On select une potentielle TD d'un tableau firsTD (select qui se transfert sur un autre tableau/autre onglet)
						$(".is_playable.firstTD tbody").find('[versionId="'+$(this).attr("versionId")+'"] td:first-child').addClass("selected");
						// On update le player
						update_player($(this).attr("morceauId"), $(this).attr("versionId"));
					});
				});
			}
			// firstTD (on select que la firstTD)
			else {
				$(this).find("tr[versionId!='-1'] td:first-child").on("click", function() {
					// On déselectionne la td précédente
					$(".is_playable tbody").find(".selected").removeClass("selected");
					// La td devient selected
					$(this).addClass("selected");
					// On select une potentielle TR d'un tableau non firsTD (select qui se transfert sur un autre tableau/autre onglet)
					$(".is_playable:not(.firstTD) tbody").find('[versionId="'+$(this).closest("tr").attr("versionId")+'"]').addClass("selected");
					// On update le player
					update_player($(this).closest("tr").attr("morceauId"), $(this).closest("tr").attr("versionId"));
				});
			}
			// On indique que les listeners ont été créé
			$(this).addClass("updated");
		});
		
					
		// On surcharge le css pour les pauses
		$(".is_playable tbody tr[versionId='-1'] > td").css("background-color","#dddddd");

	}
	
	
	
	/**** PLAY_NEXT  *****/
	function play_next() {
		
		console.log("play_next");
		
		// Si pas de play_all, on s'arrête
		if ($("#bottom_bar #player_block #play_allBtn").hasClass("selected") == false) return;
		
		// On récupère le nombre de track dans la playlist
		$nbSongs = $(".is_playable tbody tr").length;
		
		// On récupère la track courante qu'on déselectionne
		$track = $(".is_playable tbody tr.selected");
		$track.removeClass("selected");
		
		// On récupère la track suivante...
		// ...remise à zéro si dernière piste
		if ($track.index()+1 == $nbSongs) $newTrack = $(".is_playable tbody tr:first-child");
		// ... en fonction du random
		else if ($("#bottom_bar #player_block #randomBtn").hasClass("selected") == true) {
			$rdmTrack = getRandomInt($nbSongs);
			while ($rdmTrack == $track.index()+1) $rdmTrack = getRandomInt($nbSongs);
			$newTrack = $(".is_playable tbody tr:nth-child("+$rdmTrack+")");
		}
		// ...track suivante
		else $newTrack = $track.next();
		
		// On sélectionne la track suivante
		$newTrack.addClass("selected");
		
		// On gère les pauses
		if ($newTrack.attr("versionId") == -1) {
			console.log("PAUSE !!");
			play_next();
			return;
		}
		
		// On joue la nouvelle track suivante (sauf si on vient de finir la playlist)
		$newTrack.addClass("selected");
		update_player($newTrack.closest("tr").attr("morceauId"), $newTrack.closest("tr").attr("versionId"), $newTrack.index() > 0);
	}
	
	
	
	/**** PLAY_ALL  *****/
	function play_all() {
		$("#bottom_bar #player_block #play_allBtn").toggleClass("selected")
	}
	
	/**** RANDOM  *****/
	function random() {
		$("#bottom_bar #player_block #randomBtn").toggleClass("selected")
	}
	
	/**** Fonction utilisée pour le random ****/
	function getRandomInt(max) {
		return Math.floor(Math.random() * Math.floor(max));
	}
	

	
</script>


<!-- ********** BOTTOM BAR *************** !-->
<div id="bottom_bar" class="footer navbar-fixed-bottom navbar-inverse">
	<div class="container">
		<div class="row">
		<div class="col-md-12">
		
			<!--<div id="carousel" class="carousel slide" data-ride="carousel" data-interval="false" data-wrap="false">!-->
			
				<!--<div class="carousel-inner">!-->
			
					<!-- ********** PLAYER *************** !-->
					<div id="player_block" class="item active col-xs-12 col-sm-6 col-md-6 col-lg-7">
					
						<h4 id="bottom_block_title">Player</h4>
						
						<div class="row">
							<div id="player_panel" class="col-xs-11 col-sm-11 col-md-11">
								<audio id="player" controls	onended="play_next()">
									<div class="error">Votre navigateur ne supporte pas la lecture de mp3.</div>
								</audio>
							</div>
							<div id="play_options_panel" class="col-xs-1 col-sm-1 col-md-1">
								<div class="btn-group-vertical btn-group-xs">
									<button id="play_allBtn" class="btn btn-xs" onclick="play_all()"><img src="/images/icons/play_all.png" alt="Play all"></button>
									<button id="randomBtn" class="btn btn-xs" onclick="random()"><img src="/images/icons/random.png" alt="Random"></button>
								</div>
							</div>
						
						</div>
						
					</div>
				
					<!-- ********** LIST BLOCK *************** !-->
					<div id="bottom_list_block" class="item col-sm-6 col-md-6 col-lg-5">
						<h5>Liste des documents</h5>
						<div id="bottom_list"></div>
					</div>
					
				<!--</div>!-->
				
			<!--</div>!-->
				
		</div>
		</div>
	</div>
</div>
