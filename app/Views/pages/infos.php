<script type="text/javascript">

		/* ******************* Gestion des tableaux ****************/
	$(function() {
		
		right_place();
		
		$(window).resize(function() { //Fires when window is resized
			right_place();
		});
		
		function right_place() {
			var width = (window.innerWidth > 0) ? window.innerWidth : screen.width;
			if (width <= 768) {
				var detach = $("#infos_carousel").detach();
				detach.insertAfter($("#cadre p:first-child"));
				$("#infos_carousel").removeClass("pull-right");
			}
			else {
				var detach = $("#infos_carousel").detach();
				detach.insertAfter($("#cadre p:nth-child(3)"));
				$("#infos_carousel").addClass("pull-right");
			}
			// On affiche la div si hidden
			if ($("#infos_carousel").css("display") == "none") $("#infos_carousel").css("display","block");
		}
		
		// Contrôles au clavier
		$(document).bind('keyup', function(e) {
			if(e.which == 39){
				$('#infos_carousel').carousel('next');
			}
			else if(e.which == 37){
				$('#infos_carousel').carousel('prev');
			}
		});
		
		// Contrôles au click
		$('#infos_carousel').click(function() {
			$('#infos_carousel').carousel('next');
		});
		
	});

</script>

<div class="panel panel-default row">

	<!-- Header !-->
	<div class="row">
		<h4 class="panel-heading">Le Grenoble Reggae Orchestra</h4>
	</div>

	<div class="row panel-body">
		<div id="cadre" class="col-lg-12">
		
			<p><b>est un collectif de musiciens œuvrant pour la redécouverte et le partage de la musique reggae via l’organisation de scènes ouvertes.</b></p>

			<p><b>Le GRO rassemble plus d'une centaine de musiciens amoureux du reggae.</b> Certains sont professionnels, d’autres sont amateurs, mais tous sont actifs dans les musiques actuelles (jazz, rock, reggae, gospel, ...). La plupart d’entre eux font partie de l’un d’un des innombrables groupes de la région grenobloise, notamment connue pour son attachement au reggae.</p>
			
			<p>Réunis sous la houlette du GRO, association loi 1901, ces musiciens passionnés ont pour objectif <b>rendre hommage à cette musique</b> extrêmement populaire en explorant son répertoire (Bob Marley, Gladiators, Steel Pulse, Aswad…), <b>en l’interprétant au format XL </b>(plus de 10 musiciens sur scène) <b>et en ouvrant la scène aux volontaires !</b></p>

			<div id="infos_carousel" class="carousel slide pull-right" data-ride="carousel" style="display:none">
				<!-- Indicators -->
				<ol class="carousel-indicators">
					<li data-target="#infos_carousel" data-slide-to="0" class="active"></li>
					<li data-target="#infos_carousel" data-slide-to="1"></li>
					<li data-target="#infos_carousel" data-slide-to="2"></li>
					<li data-target="#infos_carousel" data-slide-to="3"></li>
					<li data-target="#infos_carousel" data-slide-to="4"></li>
				</ol>

				<!-- Wrapper for slides -->
				<div class="carousel-inner" role="listbox">

					<div class="item active">
						<img src="<?php echo base_url("images/infos/1.jpg"); ?>" alt="GRO #1" width="600px">
					</div>

					<div class="item">
						<img src="<?php echo base_url("images/infos/2.jpg"); ?>" alt="GRO #2" width="600px">
					</div>

					<div class="item">
						<img src="<?php echo base_url("images/infos/3.jpg"); ?>" alt="GRO #3" width="600px">
					</div>

					<div class="item">
						<img src="<?php echo base_url("images/infos/4.jpg"); ?>" alt="GRO #4" width="600px">
					</div>
					
					<div class="item">
						<img src="<?php echo base_url("images/infos/5.jpg"); ?>" alt="GRO #5" width="600px">
					</div>

				</div>
			</div>

		
			<p>Afin de partager le plaisir jubilatoire d’interpréter cette musique, ils invitent tous les musiciens amateurs, quel que soit leur niveau, à monter sur scène à leurs côtés (ou à leur place).</p>
			<p>Depuis une <b>première jam mythique organisée en 2011</b> à l'occasion des 30 ans de la disparition de Bob Marley, le GRO s'est développé en enrichissant son répertoire (plus de <b><a href="<?php echo site_url('repertoire') ?>">130 références</a></b> jouées) ainsi qu'en diversifiant son action (encadrement de <b>stages de préparation</b> depuis 2014).</p>
			<p><b>Sur scène, le GRO accueille les instruments suivants :</b><br>
			basse / batterie / clavier / guitare / percussions / chant (lead & choeur) / soufflants.</p>

			<p>Le projet est né de l’initiative de <a href="http://s.plotto.free.fr/" target="_blanck">Stéphane Plotto.</a></p>
		</div>
	</div>
</div>