
<script type="text/javascript">

	// Bootstrap s'occupe de la validation (email valide, pas de champs vide)
	function send_message() {

		// On crée le corps de l'email envoyé à l'admin
		$message = "<h4>Un visiteur du site le-gro.com a cherché à vous contacter :</h4>";
		$message += "<p>"+$('#contactForm #message').val()+"</p>";
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/send_email",
		
			// On récupère les données nécessaires
			{'subject':'Formulaire de contact',
			'name':$('#contactForm #name').val(),
			'message': $message,
			'from':$('#contactForm #email').val(),
			'reply_to':$('#contactForm #email').val(),
			'adresses': JSON.stringify(["contact@le-gro.com"])
			},
			
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					// Email bien envoyé
					$("#dialog_msg .modal-dialog").addClass("default");
					$("#dialog_msg .modal-header").html("Message envoyé !");
					$("#dialog_msg .modal-footer").html('<a href="/">Retour à l\'accueil</a>');
				}
				else {
					// Erreur
					$("#dialog_msg .modal-dialog").addClass("error");
					$("#dialog_msg .modal-header").html("Erreur !");
					$("#dialog_msg .modal-footer").html('<a href="#" data-dismiss="modal">Fermer</a>');
				}
				
				$("#dialog_msg .modal-body").html($obj['data']);
				$("#dialog_msg").modal('show');
		
			}
		);

		
		
	}
	
</script>

<!-- ****************************************************** !-->

<div class="row">

	<!-- Block principal !-->
	<div class="col-md-9 col-lg-9 panel panel-default">
		
		
			<!-- Header !-->
			<div class="row">
				<h4 class="panel-heading">Contact</h4>
			</div>
			
			
			<!-- Formulaire !-->
			<div id="contactForm" class="container-fluid">
				<form class="form-horizontal" action="javascript:send_message()">

					<!-- Nom !-->
					<div class="form-group required">
						<label for="name" class="control-label col-sm-2">Nom</label>
						<div class="col-sm-10">
							<input id="name" class="form-control" required="true" type="text" name="name" value="<?php echo set_value('name');?>" />
						</div>
					</div>

					<!-- Email !-->
					<div class="form-group required">
						<label for="email" class="control-label col-sm-2">Email</label>
						<div class="col-sm-10">
							<input id="email" class="form-control" required="true" type="email" name="email" value="<?php echo set_value('email');?>" />
						</div>
					</div>
					
					<!-- Message !-->
					<div class="form-group required">
						<label for="message" class="control-label col-sm-2">Message</label>
						<div class="col-sm-10">
							<textarea id="message" class="form-control vresize" required="true" name="message"></textarea>
						</div>
					</div>
					
					<!-- Envoyer !-->
					<input class="btn btn-default pull-right" type="submit" value="Envoyer"/>

				</form>
			</div>
			
	
	</div>
	
	<!-- Block de droite !-->
	<div class="col-md-3 col-lg-3">		<!-- On sépare col et panel pour avoir un pad !-->
		<div class="panel panel-default">
			<div class="panel-body" style="padding-top:15px">	<!-- On rajoute le padding top !-->
				Pour nous contacter, vous pouvez utiliser le formulaire ci-<span class="hidden-xs hidden-sm">contre</span><span class="hidden-md hidden-lg">dessus</span> ou bien nous écrire à l'adresse <a href="mailto:contact@le-gro.com">contact@le-gro.com</a>.
			</div>
		</div>
	</div>

</div>


<!-- Dialogue box de resultat !-->
<div id="dialog_msg" class="modal fade" role="dialog" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header lead"></div>
			<div class="modal-body"></div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>

