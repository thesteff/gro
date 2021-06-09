
<!-- Pour les easing effects !-->
<script src="<?php echo base_url();?>ressources/jquery-ui-1.12.1.custom/jquery-ui.min.js" /></script>

<!-- Pour gérer les events des écrans tactiles !-->
<script src="<?php echo base_url();?>ressources/script/hammer.min.js" /></script>

<!-- <script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js'> !-->

<!-- Pour gérer les anim Tween !-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.20.4/TweenMax.min.js" /></script>

<script type="text/javascript">

	$(function() {

		// Index principal
		// 0 : logo
		// 1 : playlist
		// 2 : titles (pause à gérer)
		// nbSetx2 +1 : members
		// nbSetx2 +2 : logoEnd 
		mainIndex = 0;
		
		setIndex = 1;
	
		// Taille de la playlist
		nbSongs = $("#playlist_body tr").length;
		
		// Nombre de set (nb pause + 1)
		nbSets = $("#playlist_body tr[versionId=-1]").length + 1;
	
		// Index de la playlist
		playlistIndex = 0;
		
		// Counter des div qui sont créées
		divCounter = 1;
		
		// Thread d'animation
		var interval;
		
		// Listener pour capter quand l'utilisateur sort du fullscreen
		if (document.addEventListener) {
			document.addEventListener('webkitfullscreenchange', screenChange);
			document.addEventListener('mozfullscreenchange', screenChange);
			document.addEventListener('fullscreenchange', screenChange);
			document.addEventListener('MSFullscreenChange', screenChange);
		}
		

		/******** ECRAN TACTILES **********/
		var myElement = document.getElementById('presentationBlock');
		var hammertime = new Hammer(myElement);
		hammertime.on('swipeleft swiperight', function(ev) {			
			// Valides que si fullscreen
			if ($("#presentationPanel").css('display') == 'block') {
				// RIGHT
				if (ev.type == 'swipeleft') goRight();
				// LEFT
				else if (ev.type == 'swiperight') goLeft();
			}
		});


	
		/******** KEYBOARD **********/
		$("body").on("keydown", function(event) {

			/*console.log($(".selected").length+"   "+$("body #mail_block :focus").length);
			console.log(event.which);*/

			/*
			up = 38
			down = 40*/
	
			// Touche valides que si fullscreen
			if ($("#presentationPanel").css('display') == 'block') {
				/*console.log("=======================");
				console.log("== mainIndex : "+mainIndex);
				console.log("== playlistIndex : "+playlistIndex);*/
				
				// RIGHT
				if (event.which == 39) goRight();
				// LEFT
				else if (event.which == 37) goLeft();
			}

		});
	
	});
	
	function goRight() {
		// On avance dans les parties
		if (mainIndex == 0) mainIndex++;
		else if (mainIndex%2 != 0) mainIndex++;	// On avance forcément le mainIndex si on est sur l'affichage de playlist
		else if (playlistIndex == nbSongs) mainIndex++;	// On arrive en bout de playlist...
		
		// On défile la playlist
		if (playlistIndex <= nbSongs && mainIndex > 1) playlistIndex++;

		// On entre sur une page playlist
		if ((mainIndex%2 == 0 && mainIndex != 0) && ($("#playlist_body tr").eq(playlistIndex-1).attr('id') == '-1')) {
			mainIndex++;
			setIndex++;
			$("#playlistDiv").remove();
		}

		refresh("right");
	}
	
	function goLeft() {
		// On entre sur une page playlist
		if ((mainIndex%2 == 0 && mainIndex != 0) && (playlistIndex == 1 || $("#playlist_body tr").eq(playlistIndex-2).attr('id') == '-1') || playlistIndex == nbSongs+1) {
			mainIndex--;
			$("#playlistDiv").remove();
		}
		// On sort de la playlist
		else if (mainIndex%2 != 0) {
			mainIndex--;
			if (setIndex > 1) setIndex--;
			//if (playlistIndex > 0) playlistIndex--;
		}
		
		// On défile la playlist
		if ((mainIndex%2 == 0 && mainIndex != 0) || playlistIndex == 1 || $("#playlist_body tr").eq(playlistIndex-2).attr('id') == '-1') playlistIndex--;
		
		refresh("left");
	}
	

	
	// ************  Actualise l'affichage de la présentation en fonction du playlistIndex
	function refresh(action) {
		
		/*console.log("== action : "+action);
		console.log("== mainIndex : "+mainIndex);
		console.log("== playlistIndex : "+playlistIndex);*/
			
		// On récupère les variables à afficher
		title = $("#playlist tbody tr:nth-child("+playlistIndex+") td:nth-child(1)").html();
		artist = $("#playlist tbody tr:nth-child("+playlistIndex+") td:nth-child(2)").html();
		year = $("#playlist tbody tr:nth-child("+playlistIndex+") td:nth-child(3)").html();
		if (playlistIndex < nbSongs) nextTitle = $("#playlist tbody tr:nth-child("+(playlistIndex+1)+") td:nth-child(1)").html();
		//console.log("== "+title+" / "+artist+" / "+year);
		
		
		/*********** FADE OUT *********/
		// On efface le logo si besoin
		if ( (mainIndex != 0 && mainIndex != nbSets*2 +2) && $("#presentationPanel #logoDiv").css('display') == 'block') {
			$("#presentationPanel #logoDiv").fadeOut('fast');
			$("#presentationPanel #wwwDiv").fadeOut('fast');
			$("#presentationPanel #creditsPanel").fadeOut('fast');
		}
		
		// On efface la playlist si besoin
		if ( (mainIndex %2 == 0) && $("#presentationPanel #playlistDiv").css('display') == 'block') {
			$("#presentationPanel #playlistDiv").fadeOut('fast');
		}
		// On efface les crédits si besoin
		if ( (mainIndex %2 == 0) && $("#presentationPanel #creditsPanel").css('display') == 'block') {
			$("#presentationPanel #creditsPanel").fadeOut('fast');
			//clearInterval(interval);
			
		}
		
		
		/*********** RIGHT *********/
		// On effectue l'animation de transition NEXT
		if (action == "right") {
			
			// On déplace les div précédentes hors écran
			if (divCounter > 1) {
				precDiv = divCounter -1;
				$("#mainBlock"+precDiv).animate({ "left": "-=2000px" }, 600, "easeOutExpo", function() {
					$(this).remove();
				});
				$("#nextSongDiv"+precDiv).fadeOut('fast');
			}

			// On fade la nouvelle div
			if (mainIndex%2 == 0 && mainIndex != 0 && playlistIndex <= nbSongs) {
				$("#presentationPanel #centerDiv").append("<div id='mainBlock"+divCounter+"'><div id='songTitle"+divCounter+"' style='display:none'></div><div id='artist"+divCounter+"' style='display:none'></div></div>");
				$("#songTitle"+divCounter).append(title.toUpperCase()).fadeIn('slow');
				$("#artist"+divCounter).append(artist).fadeIn('slow');
			}
		}
		
		
		/*********** LEFT *********/
		// On effectue l'animation de transition PREC
		else if (action == "left") {
			
			// On fade la div précédente
			if (divCounter > 1) {
				precDiv = divCounter -1;
				$("#songTitle"+precDiv).fadeOut('fast');
				$("#artist"+precDiv).fadeOut('fast');
				$("#nextSongDiv"+precDiv).fadeOut('fast');
			}
			
			
			// On detecte si on arrive sur une pause
			if ($("#playlist_body tr").eq(playlistIndex-1).attr('id') == '-1') {
				$("#playlistTitleDiv span").empty().append("Partie "+setIndex);
			}
			else {
				// On affiche la nouvelle div (en réalité celle d'avant dans la liste des songs)
				if (mainIndex%2 == 0 && playlistIndex > 0) {
					$("#presentationPanel #centerDiv").append("<div id='mainBlock"+divCounter+"' style='left:-200vw'><div id='songTitle"+divCounter+"'>"+title.toUpperCase()+"</div><div id='artist"+divCounter+"'>"+artist+"</div></div>");
					$("#mainBlock"+divCounter).animate({ "left": "+=150vw" }, 600, "easeOutExpo");
				}
			}
		}
		
		/*********** LOGO *********/
		// Premier affichage (pas d'action)
		if (mainIndex == 0)  {
			$("#presentationPanel #logoDiv").fadeIn('slow');
		}
		
		/*********** PLAYLIST *********/
		else if (mainIndex%2 != 0 && playlistIndex < nbSongs) {
			
			if ($("#playlistDiv").length == 0) {

				$("#presentationPanel").append("<div id='playlistDiv' style='display:none'></div>");
				
				// Text
				$("#playlistDiv").append("<div id='playlistTitleDiv'>Grenoble Reggae Orchestra <span>Partie "+setIndex+"<span></div>");
				$("#playlistDiv").append("<div id='playlistLogoTextDiv'>"+$("#playlist").attr('name').replace(/\s/g, '')+"</div>");
				
				// Playlist
				$table = "<table>";
				$table += "<tbody>";
				realIndex = 0;
				tempIndex = 0;
				tempSetIndex = 1;
				$("#playlist_body tr").each( function (index) {
					realIndex++;
					tempIndex++;
					if ($(this).attr("id") != '-1') {
						if (tempSetIndex == setIndex) {
							label = "<div id='songTD"+realIndex+"' style='display:none'>"+$(this).children("td").first().html().toUpperCase()+"&nbsp;&nbsp;|&nbsp;&nbsp;"+$(this).children("td").eq(1).html()+"</div>";
							$table += "<tr><td>"+(tempIndex)+".&nbsp;</td><td>"+label+"</td></tr>";
						}
					}
					// On arrive sur une pause
					else {
						tempSetIndex++;
						tempIndex = 0;
					}
					
					// Si on depasse le setIndex, on peut breaker
					if (tempSetIndex > setIndex) return false;
				});
				$table += "</tbody>";
				$table += "</table>";
				$("#playlistDiv").append($table);
				
				// On fade les songTitle
				$('#playlistDiv table div[id^="songTD"]').each( function (index) {
					$(this).delay((index)*100).fadeIn('slow');
				});
				
			}
			
			// Logo
			if ($("input#night_mode").prop('checked')) png = 'logoLion_nightMode.png';
			else png = 'logoLion.png';
			$("#playlistDiv").append("<div id='playlistLogoDiv'><img style='width:30vw' src='/ressources/global/"+png+"'></div>");
			
			
			$("#playlistDiv").fadeIn('slow');
		}
		
		
		/*********** CREDITS *********/
		//else if (playlistIndex > nbSongs && $("#presentationPanel #logoDiv").css('display') == 'none')  {
		else if (playlistIndex > nbSongs && mainIndex%2 != 0)  {
			
			$("#presentationPanel").append("<div id='creditsPanel' style='display:block; opacity: 0'></div>");
				
			// Text
			if ($("#creditsDiv").length == 0) {
	
				
				credits = "";
				$("#members_body tr").each( function (index) {
					credits += "<div style='opacity:0'>"+$(this).children('td').eq(0).html()+"</div>";
				});
				
				$("#creditsPanel").append("<div id='creditsDiv'>"+credits+"</div>");
				
				// On récupère la height des credits (si display = 0 ça marche pas...)
				//console.log($("#creditsPanel #creditsDiv").height());
				//console.log($(window).height());
				$divHeight = $("#creditsDiv").height();
				
				// Permet d'ajuster la vitesse de défilement en fonction de la taille du texte de crédits
				factor = Math.round($divHeight/$(window).height());
				//console.log("factor : "+factor);
				
				TweenLite.to("#creditsDiv", 18*factor, {top: -$divHeight, ease: Power0.easeNone});
				fadeInText();	
			}

			$("#creditsPanel").css({'opacity': 100, 'display': 'none'});
			$("#creditsPanel").fadeIn('slow');
		}
		
		/*********** FIN de l'évènement *********/
		else if (playlistIndex > nbSongs && mainIndex%2 == 0 && $("#presentationPanel #logoDiv").css('display') == 'none')  {
			$("#presentationPanel #logoDiv").fadeIn('slow');
			$("#presentationPanel #wwwDiv").delay(800).fadeIn('slow');
		}
		
		// **************
		
		// On fade la nouvelle nextSongDiv
		if (playlistIndex > 0 && playlistIndex < nbSongs && $("#playlist_body tr").eq(playlistIndex-1).attr('id') != '-1') {
			$("#presentationPanel").append("<div id='nextSongDiv"+divCounter+"'><div id='nextSongTitle' style='display:none'></div>");
			$("#nextSongDiv"+divCounter+" #nextSongTitle").append("<i class='glyphicon glyphicon-arrow-right'></i> "+nextTitle.toUpperCase()).delay(400).fadeIn('slow');
		}
		
		
		// On augmente le counter de div animée
		if (action == "left" || action == "right") divCounter++;
		

	}
	
	
	// ************  Fade effects
	function fadeInText() {

		var pos1 = parseInt($("#creditsDiv").css('top'),10);
		var pos2 = 0;
		var index = 0;
		var index2 = 0;
		var divHeight = parseInt($("#creditsDiv div").eq(index).outerHeight(),10);
		interval = setInterval(function() {
			//console.log("top : "+$("#creditsDiv").css('top'));
			//console.log("pos : "+pos1);
			// FadeIN
			//console.log($("#creditsDiv div").eq(index).offset().top);
			//if (pos1+divHeight > parseInt($("#creditsDiv").css('top'),10)) {
			if (index < $("#creditsDiv div").length && $(window).height() > $("#creditsDiv div").eq(index).offset().top) {
				//$("#creditsDiv div").eq(index).css("color",'red');
				$("#creditsDiv div").eq(index).css({'opacity': 100, 'display': 'none'});
				$("#creditsDiv div").eq(index).fadeIn(3000);
				index++;
				pos1 -= divHeight;
				//console.log("index : "+index);
				//console.log("height : "+$("#creditsDiv div").eq(index).height());
			}
			// FadeOUT
			if (index2 < $("#creditsDiv div").length && $("#creditsDiv div").eq(index2).offset().top < divHeight*2) {
				//$("#creditsDiv div").eq(index).css("color",'red');
				$("#creditsDiv div").eq(index2).fadeTo(3000,0);
				index2++;
				pos2 -= divHeight;
				//console.log("index2 : "+index2);
				//console.log("height : "+$("#creditsDiv div").eq(index).height());
			}
			
		}, 200);
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
	

	// ************  On gère les changement d'état de l'écran
	function screenChange() {
		if (!document.webkitIsFullScreen && !document.mozFullScreen && document.msFullscreenElement == null) {
			// On efface le panel de présentation
			$("#presentationPanel").css('display','none');
			//clearInterval(interval);
		}
		else {
			// On affiche le panel de présentation
			$("#presentationPanel").css('display','block');
			
			// Mode Nuit
			if ($("input#night_mode").prop('checked')) {
				$("#presentationBlock").addClass('nightMode');
				// On change l'image du logo
				$("#presentationBlock #logoDiv img.nightMode").css('display','block');
				$("#presentationBlock #logoDiv img.normalMode").css('display','none');
			}
			else {
				$("#presentationBlock").removeClass('nightMode');
				// On change l'image du logo
				$("#presentationBlock #logoDiv img.nightMode").css('display','none');
				$("#presentationBlock #logoDiv img.normalMode").css('display','block');
			}
			
			// On affiche la première page si besoin
			if (playlistIndex == 0) refresh();
		}
	}
	

</script>


<?php if ($playlist_item['list']) : ?>

	<!--------------- VARIABLES CACHEES pour JAVASCRIPT ------------------->
	
	<!-- **** LISTE DES MORCEAUX **** !-->
	<?php if ($playlist_item != "null" && $playlist_item['list'] != 0): ?>

		<table id="playlist" class="hidden" name="<?php echo $playlist_item['infos']['title'] ?>">
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
	
	<!-- **** LISTE PARTICIPANTS **** !-->
	<?php if ($list_members != "null"): ?>

		<table id="members" class="hidden">
			<thead>
				<tr>
					<th>Pseudo</th>
					<th>idInstru1</th>
				</tr>
			</thead>
			<tbody id="members_body">
			<?php foreach ($list_members as $key=>$ref): ?>
				<tr id="<?php echo $ref->memberId ?>">
					<td><?php echo $ref->pseudo ?></td>
					<td><?php echo $ref->idInstru1 ?></td>
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
		<div class="panel-heading panel-bright title_box">
			<h4><a href="<?php echo site_url().'/jam/'.$jam_item['slug']; ?>"><?php echo $jam_item['title']; ?></a> <small>:</small> présentation</h4>
		</div>

		<div class="row panel-body">
		<div class="col-lg-12">
		
			<!-- Options !-->
			<form class="form-horizontal">				
				
				<div class="form-group">
					<label for="night_mode" class="control-label col-sm-2">Mode nuit</label>
					<div class="checkbox col-sm-2">
						<label style="padding-left: 0px">
							<input id="night_mode" class="form-control" name="night_mode" type="checkbox" />
							<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
						</label>
					</div>
				</div>
			
			</form>
			
	
			<!-- Btn LAUNCH PRES !-->
			<button class="btn btn-primary center-block" onclick="javascript:launch_pres()"><i class='glyphicon glyphicon-expand soften' style="color:white;"></i>&nbsp;&nbsp;Lancer la présentation</button>

		</div>
		</div>


	</div>
	
	
	
	<!-- SLIDE BLOCK !-->
	<div id="presentationBlock">
	
		<div class="row">
		<div id="presentationPanel" style="display:none">

			<div id="centerDiv"></div>
			<!--<div id='logoDiv' style="display:none"><img style='height:50vw' src='/ressources/global/logo.png'></div> !-->
			<div id='logoDiv' style="display:none">
				<img class='normalMode' style='height:50vw' src='/ressources/global/logo.png'>
				<img class='nightMode' style='height:50vw; display:none' src='/ressources/global/logo_nightMode.png'>
			</div>
			<div id='wwwDiv' style="display:none">www.le-gro.com</div>
			
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