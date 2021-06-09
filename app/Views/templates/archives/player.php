<script type="text/javascript">
	
	$(document).ready(function() {
		
	});
	
	
	function update_player(morceauId = -1, versionId, play) {
		
		//console.log("PLAYER.php :: update_player() versionId : "+versionId);
		//console.log("PLAYER.php :: update_player() morceauId : "+morceauId);
		$player = $("#player");
		
		$("#bottom_block_title").html("...");
		$("#bottom_pdf_block").html("...");
		
		$.post("<?php echo site_url(); ?>/ajax/get_version_infos",
			
			{'morceauId': morceauId,
			'versionId': versionId
			},
		
			function (msg) {
				
				//console.log("PLAYER.php :: update_player() msg : "+msg);
				$data = JSON.parse(msg);
				
				// Titre
				var compo = "";
				if ($data.version.artisteLabel) compo = " - <small>"+$data.version.artisteLabel+"</small>";
				$("#bottom_block_title").html($data.version.titre+compo);
				
				// Player
				if ($data.version.mp3URL) {
					if ($data.version.groupe != "") $subPath = $data.version.groupe+"/";
					else $subPath = "";
					//console.log("PLAYER.php :: update_player() MP3Path : <?php echo base_url(); ?>ressources/morceaux/"+$data.version_path+"/"+$subPath+$data.version.mp3URL);
					$player.attr("src",'<?php echo base_url(); ?>ressources/morceaux/'+$data.version_path+"/"+$subPath+$data.version.mp3URL);
					$player.css("display","block");
				}
				else {
					// STOPPER LE PLAYER !!!!!!!!!!
					$player.css("display","none");
				}
				
				// On actualise le block droit
				$("#bottom_list").empty();
				//$("#bottom_list_block").append("<p>Liste des documents</p>");
				$pdf_img = "<img width='12' src='<?php echo base_url(); ?>images/icons/pdf_icon_16.png' />";
				$.each($data.medias, function($index, $val) {
					$ref_pdf = "<a href='<?php echo base_url(); ?>ressources/morceaux/"+$data.version_path+"/"+$data.version.groupe+"/"+$val.URL.replace(/\s/g,'%20')+"' target='_blanck'>"+$val.URL+"</a>";
					$("#bottom_list").append("<div>"+$pdf_img+$ref_pdf+"<div>");
				});
				
			}
		);
    }
	
	
	/**** PLAY SONG  *****/
	// Généralement, le player est utilisé avec un tablesorter playable
	// Permet de fixer le comportement des titre de morceaux
	function song_update(tag) {
		//console.log("song_update : "+tag);
		if (tag != "firstTD") {
			$(".is_playable tbody tr[versionId!='-1']").on("click", function() {
				// On déselectionne la tr précédente
				$(this).closest("tbody").find(".selected").removeClass("selected");
				// La tr devient selected
				$(this).addClass("selected");
				update_player($(this).attr("morceauId"), $(this).attr("versionId"));
			});
		}
		// On traite le cas du tableau d'inscription où seule la première TD réagit
		else if (tag == "firstTD") {
			$(".is_playable tbody tr[versionId!='-1'] td:first-child").on("click", function() {
				// On déselectionne la td précédente
				$(this).closest("tbody").find(".selected").removeClass("selected");
				// La td devient selected
				$(this).addClass("selected");
				update_player($(this).closest("tr").attr("morceauId"), $(this).closest("tr").attr("versionId"));
			});
		}
					
		// On surcharge le css pour les pauses
		$(".is_playable tbody tr[versionId='-1'] > td").css("background-color","#dddddd");

	}
	
	
	/**** PLAY_NEXT  *****/
	function play_next() {
	
		if ($selected_item >= 0 && $selected_item < $(".listTab tbody tr").length)  {
			set_select($selected_item);
			
			// On saute les pauses
			if ($(".listTab tbody tr:nth-child("+$selected_item+")").attr("versionId") == -1) play_next();
			
			// On sélectionne le titre suivant et on le joue
			else update_player($(".listTab tbody tr:nth-child("+$selected_item+")").prop("id"),true);
		}
		// On a fini de lire la liste et on selectionne le premier titre
		else {
			set_select(0);
			update_player($(".listTab tbody tr:nth-child("+$selected_item+")").prop("id"),false);
		}
	
	}
	
	
</script>


<!-- ********** PLAYER *************** !-->
<?php if ($this->config->item("bootstrap") == true): ?>

	<div id="bottom_bar" class="footer navbar-fixed-bottom navbar-inverse">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
			
					<div class="row">
					
						<div id="player_block" class="col-xs-12 col-sm-6 col-md-6 col-lg-7">
						
							<h4 id="bottom_block_title">Player</h4>
							<audio id="player" controls	onended="play_next()">
								<div class="error">Votre navigateur ne supporte pas la lecture de mp3.</div>
							</audio>
							<!--<div><small>play all</small></div>!-->
						</div>
					
					
						<div id="bottom_list_block" class="hidden-xs col-xsm-6 col-md-6 col-lg-5">
							<h5>Liste des documents</h5>
							<div id="bottom_list"></div>
						</div>
						
					</div>
					
				</div>
			</div>
		</div>
	</div>

<?php else: ?>
	<div class="bottom_block">

		<div class="bottom_block_content">
		
			<div>
				<h3 id="bottom_block_title">Player</h3>
				<audio id="player" controls	onended="play_next()">
					<div class="error">Votre navigateur ne supporte pas la lecture de mp3.</div>
				</audio><br>
				<!--<div><small>play all</small></div>!-->
			</div>
			
			
			<div id="bottom_list_block">
				<p>Liste des documents</p>
			</div>

		</div>
	</div>
<?php endif; ?>