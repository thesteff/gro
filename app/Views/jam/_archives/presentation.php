
<!-- Pour les easing effects !-->
<script src="<?php echo base_url();?>ressources/jquery-ui-1.12.1.custom/jquery-ui.min.js" /></script>

<!-- <script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js'> !-->

<script type="text/javascript">

	$(function() {

		// Index de la playlist
		mainIndex = 0;
	
		/******** KEYBOARD **********/
		$("body").on("keydown", function(event) {

			/*console.log($(".selected").length+"   "+$("body #mail_block :focus").length);
			console.log(event.which);*/

			/*
			up = 38
			down = 40*/
	

			// RIGHT
			if (event.which == 39) {
				mainIndex++;
				refresh("right");
			}
			// LEFT
			else if (event.which == 37) {
				mainIndex--;
				refresh("left");
			}

		});
	
	});
	
	
	// ************  Actualise l'affichage de la présentation en fonction du mainIndex
	function refresh(action) {
		
		// On récupère les variables à afficher
		title = $("#playlist tbody tr:nth-child("+mainIndex+") td:nth-child(1)");
		artist = $("#playlist tbody tr:nth-child("+mainIndex+") td:nth-child(2)");
		year = $("#playlist tbody tr:nth-child("+mainIndex+") td:nth-child(3)");
		
		// On effectue l'animation de transition NEXT
		if (action == "right") {
			$("#songTitle").animate({ "left": "-=2000px" }, 600, "easeOutExpo");
			$("#artist").animate({ "left": "-=2000px" }, 650, "easeOutQuart", function() {
				$("#songTitle").css({'left': 0 , 'display': 'none' });
				$("#artist").css({'left': 0 , 'display': 'none' });
				$("#songTitle").empty().append($("#playlist tbody tr:nth-child("+mainIndex+") td:nth-child(1)").html().toUpperCase()).fadeIn('slow');
				$("#artist").empty().append($("#playlist tbody tr:nth-child("+mainIndex+") td:nth-child(2)").html()).fadeIn(800);
			});
		}
		// On effectue l'animation de transition PREC
		else if (action == "left") {
			$("#artist").fadeOut('fast');
			$("#songTitle").fadeOut('fast', "easeOutQuart", function() {
				$("#songTitle").css({'left': -2000 , 'display': 'block' });
				$("#artist").css({'left': -2000 , 'display': 'block' });
				$("#songTitle").empty().append($("#playlist tbody tr:nth-child("+mainIndex+") td:nth-child(1)").html().toUpperCase());
				$("#artist").empty().append($("#playlist tbody tr:nth-child("+mainIndex+") td:nth-child(2)").html());
				$("#songTitle").animate({ "left": "+=2000px" }, 600, "easeOutExpo");
				$("#artist").animate({ "left": "+=2000px" }, 600, "easeOutExpo");
			});
		}
		
		
		// On affiche les données
		//$("#songTitle").empty().append($("#playlist tbody tr:nth-child("+mainIndex+") td:nth-child(1)").html().toUpperCase().fadeIn());
		//$("#artist").empty().append($("#playlist tbody tr:nth-child("+mainIndex+") td:nth-child(2)").html());
	}
	
	
	
	// ************  Active le FullScreenMode et lance la présentation
	function launch_pres() {
		
		var element = document.getElementById("presentationBlock");;
		
		// Supports most browsers and their versions.
		var requestMethod = element.requestFullScreen || element.webkitRequestFullScreen || element.mozRequestFullScreen || element.msRequestFullScreen;

		if (requestMethod) { // Native full screen.
			requestMethod.call(element);
		}
		else if (typeof window.ActiveXObject !== "undefined") { // Older IE.
			var wscript = new ActiveXObject("WScript.Shell");
			if (wscript !== null) {
				wscript.SendKeys("{F11}");
			}
		}
	}
	

</script>


<?php if ($playlist_item['list']) : ?>

	<!-- VARIABLES CACHEES pour JAVASCRIPT -->
	<?php if ($playlist_item != "null" && $playlist_item['list'] != 0): ?>

		<!-- **** LISTE DES MORCEAUX **** !-->
		<table id="playlist" class="hidden">
			<thead>
				<tr>
					<th>Titre</th>
					<th>Compositeur</th>
					<th>Année</th>
				</tr>
			</thead>
			<tbody id="playlist_body">
			<?php foreach ($playlist_item['list'] as $key=>$ref): ?>
				<tr id="<?php echo $ref->versionId ?>" versionId="<?php echo $ref->versionId ?>">
				<?php if ($ref->versionId != -1): ?>
					<td><?php echo $ref->titre ?></td>
					<td><?php echo $ref->artisteLabel ?></td>
					<td><?php echo $ref->annee ?></td>
				<?php else: ?>
					<td colspan=3>PAUSE</td>
				<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>



<!-- CONTAINER GLOBAL !-->
<div class="row">

	<!-- PANEL !-->
	<div class="panel panel-default">

		<!-- Header !-->
		<div class="row panel-heading panel-bright title_box">
			<h4><a href="<?php echo site_url().'/jam/'.$jam_item['slug']; ?>"><?php echo $jam_item['title']; ?></a> <small>:</small> présentation</h4>
		</div>

		
		<!-- Options !-->
		<div class="row">
		<div class="panel-body col-lg-12">
			
			<button class="btn btn-primary center-block" onclick="javascript:launch_pres()"><i class='glyphicon glyphicon-expand soften' style="color:white;"></i>&nbsp;&nbsp;Lancer la présentation</button>

		</div>
		</div>


	</div>
	
	
	
	<!-- SLIDE BLOCK !-->
	<div id="presentationBlock">
	
		<div class="row">
		<div id="presentationPanel">

			<div id="songTitle">TEST
			</div>
			<div id="artist">Test
			</div>
			
		</div>
		</div>

	</div>
	
	
	
</div>  <!-- GLOBAL CONTENT !-->

<?php else:?>
<div class="panel-default">
	<div class="panel-body">
		<i class='glyphicon glyphicon-warning-sign'></i>&nbsp; Pas de playlist sélectionnée.
	</div>
</div>
<?php endif; ?>