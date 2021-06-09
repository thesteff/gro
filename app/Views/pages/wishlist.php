<?php
	// On récupère les variables de sessions
	$session = \Config\Services::session();
?>


<script type="text/javascript">

	function add_wish() {
		
		$wish_titre = $('#wish_titre').val();
		$wish_url = $('#wish_url').val();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/add_wish",
		
			{
			'slugJam':'null',
			'login':'<?php echo $session->login; ?>',
			'titre':$wish_titre,
			'url':$wish_url
			},
	
			function (msg) {
				// On insère le wish_elem
				$("#wishlist").append('<div class=\"soften\"><small><?php echo $session->login; ?> à proposé :</small></br><a href=\"'+$wish_url+'\" target=\"_blanck\">'+$wish_titre+'</a></div><hr>');
				// On clean les champs de formulaires
				$("#wish_titre").val("");
				$("#wish_url").val("");
			}
		);
    }
	
 </script>

 
 
 <!-- Span caché pour obtenir les infos du membres -->
<span id="logged" style="display:none"><?php if (isset($member)) echo $member->pseudo; ?></span>



<div class="panel panel-default row">

	<!-- Header !-->
	<div class="row">
		<h4 class="panel-heading">Wishlist</h4>
	</div>


	<div class="row panel-body nopaddingtop">
		<div class="col-lg-12">
		
			<?php if ($session->logged == true) : ?>
				<div id="head_wishlist" >
					<p>Nous travaillons régulièrement étoffer notre répertoire. Vous pouvez nous suggérer de nouveaux titres ci-dessous en postant simplement un lien vers de l'audio.</p>
					<p>Sélectionner si possible un titre reggae possédant :<br>
						<ul><li>des soufflants</li><li>des choeurs</li><li>dont les paroles sont trouvables sur internet</li></ul>
					</p>
				</div>
			<?php endif; ?>
			
			<?php if ($session->logged == false) : ?>
				<div id="head_wishlist" >
					Nous travaillons régulièrement étoffer notre répertoire. Vous pouvez nous suggérer de nouveaux titres en vous inscrivant au site puis en postant vos références sur cette page.
				</div>
			<?php endif; ?>

		</div>
	</div>

		
		
	<!-- Affichage de la wishlist -->
	<div class="container">

		<div class="row panel-body nopaddingtop">
		<div class="col-lg-12">		
		
			<?php if ($wishlist != "null"): ?>
				<?php foreach ($wishlist as $wish_elem): ?>
				<div class="row panel-body" style="background-color:rgba(255,255,255,0.5); padding-top:15px;">
				<div class="col-lg-12">	
					<div class="soften" style="font-family:rimouski"><small><?php echo $wish_elem['pseudo'] ?> à proposé :</small></br>
						<a href="<?php echo $wish_elem['url'] ?>" target="_blanck"><?php echo $wish_elem['titre'] ?></a>
					</div>
				</div>
				</div>
				<?php endforeach; ?>
			<?php endif; ?>
			
		</div>
		</div>
		
		
		<!-- Form pour rajouter un souhait !-->
		<?php if ($session->logged == true) : ?>
		<div class="row panel-body nopaddingtop">
		<div class="col-lg-12">		
			
			
			<div class="row panel-body" style="background-color:rgba(255,255,255,0.5); padding-top:15px;">
			<div class="col-lg-12">
				<h5 class="panel-heading" style="padding-top:0px">Votre proposition</h5>
				
				<div class="panel-body row">
					<form class="form-horizontal" action="javascript:add_wish()">
					
						<!-- Titre !-->
						<div class="form-group required">
							<label for="wish_titre" class="control-label col-sm-1">Titre</label>
							<div class="col-sm-11">
								<input id="wish_titre" class="form-control" type="text" name="wish_titre" value="" required/>
							</div>
						</div>
						
						<!-- URL !-->
						<div class="form-group required">
							<label for="wish_url" class="control-label col-sm-1">URL</label>
							<div class="col-sm-11">
								<input id="wish_url" class="form-control" type="url" name="wish_url" value="" required/>
							</div>
						</div>
						
						<!-- Proposer !-->
						<input class="btn btn-default pull-right" type="submit" name="submit" value="Proposer" />
					</form>
				</div>
				
			</div>
			</div>
			
			
		</div>
		</div>
		<?php endif; ?>
		
	</div>
	
</div>