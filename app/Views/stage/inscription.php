<!-- bootstrap datepicker !-->
<script type="text/javascript" src="<?php echo base_url();?>ressources/bootstrap-datepicker-1.6.4/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>ressources/bootstrap-datepicker-1.6.4/locales/bootstrap-datepicker.fr.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>ressources/bootstrap-datepicker-1.6.4/css/bootstrap-datepicker3.css" />

<!-- autoresize texarea !-->
<script type="text/javascript" src="<?php echo base_url();?>ressources/script/autosize.js"></script>


<!-- bootstrapValidator est chargé dans la view de la jam !-->
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.js"></script>!-->


<script type="text/javascript">

	
	$(function() {
		

		$('#stage_inscription_form').bootstrapValidator({
			// To use feedback icons, ensure that you use Bootstrap v3.1.0 or later
			feedbackIcons: {
				valid: 'glyphicon glyphicon-ok',
				invalid: 'glyphicon glyphicon-remove',
				validating: 'glyphicon glyphicon-refresh'
			},
			fields: {
				email: {
					validators: {
						notEmpty: {
							message: 'Vous devez saisir une adresse email'
						},
						emailAddress: {
							message: 'L\'adresse email doit être valide'
						}
					}
				},
				nom: {
					validators: {
						notEmpty: {
							message: 'Vous devez saisir un nom'
						}
					}
				},
				prenom: {
					validators: {
						notEmpty: {
							message: 'Vous devez saisir un prénom'
						}
					}
				},
				naissance: {
					validators: {
						notEmpty: {
							message: 'Vous devez saisir une date de naissance'
						},
						date: {
							format: 'DD/MM/YYYY',
							message: 'La date doit être valide (ex : 11/01/1982)'
						}
					}
				},
				mobile: {
					validators: {
						phone: {
							country: 'FR',
							message: 'Le numéro doit être valide'
						}
					}
				}
			 }
        })
		.on('success.form.bv', function (e) {
			//$('#success_message').slideDown({ opacity: "show" }, "slow") // Do something ...
			$('#stage_inscription_form').data('bootstrapValidator').resetForm();

			// Prevent form submission
			e.preventDefault();
			// Get the form instance
			var $form = $(e.target);
			// Get the BootstrapValidator instance
			var bv = $form.data('bootstrapValidator');
			
			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url(); ?>/Ajax/create_stage_inscription",
			
				// On récupère les données nécessaires
				{
					'stage_id':'<?php echo $stage_item['id'] ?>',
					'id':'<?php echo $this->session->userdata('id') ?>',
					'email':$('#stage_inscription_form #email').val(),
					'nom':$('#stage_inscription_form #nom').val(),
					'prenom':$('#stage_inscription_form #prenom').val(),
					'naissance':$('#stage_inscription_form #naissance').val(),
					'mobile':$('#stage_inscription_form #mobile').val().replace(/\s/g, ''),
					'nb_prat':$('#stage_inscription_form #nb_prat').val(),
					'nb_grp':$('#stage_inscription_form #nb_grp').val(),
					'ecole':$('#stage_inscription_form #ecole').val(),
					'prof':$('#stage_inscription_form #prof').val(),
					'email_tuteur':$('#stage_inscription_form #email_tuteur').val(),
					'tel_tuteur':$('#stage_inscription_form #tel_tuteur').val(),
					'remarque':$('#stage_inscription_form #remarque').val()
				},
				
				// On traite la réponse du serveur			
				function (return_data) {
					
					console.log("RETURN : "+return_data);
					
					$obj = JSON.parse(return_data);
					// On change le curseur
					document.body.style.cursor = 'default';

					// Profil mis à jour
					if ($obj['state']) {
						// Success
						$('#preincsriptionModal').on('hidden.bs.modal', function () {
							$msg = $obj['data'];
							$("#modal_msg .modal-dialog").removeClass("error success");
							$("#modal_msg .modal-dialog").addClass("default");
							$("#modal_msg .modal-dialog").addClass("backdrop","static");
							$("#modal_msg .modal-header").html("Pré-inscription enregistrée");
							$("#modal_msg .modal-body").html($msg);
							$("#modal_msg .modal-footer").html('<button class="btn btn-default" id="modal_close" onclick="javascript:window.location.reload()">Fermer</button>');
							$("#modal_msg").modal('show');
						});
						$("#preincsriptionModal").modal("hide");
					}
					
					// Profil inchangé
					else {
						// Erreur
					}
				}
			);
		});

		// Si on valide, les champs non required ne seront pas remplis
		//$('#stage_inscription_form').bootstrapValidator('validate');

		// On initialise le autoresize
		$('.autosize').autosize({append: "\n"});
		
		
		// On initialise le datepicker
		$('#naissance').datepicker({
			format: "dd/mm/yyyy",
			todayBtn: "linked",
			language: "fr",
			todayHighlight: true,
			startView: 2
		}).on('changeDate', function(e) {
				console.log($('#naissance').val());
				check_majeur();
		});
		
		// Actualise l'affichage du block tuteur en fonction de l'age indiqué
		check_majeur();
	
	});
	
	
	
	// Actualise l'affichage du block tuteur en fonction de l'age indiqué
	function check_majeur() {
		
		// On calcul l'âge
		var birthdate = new Date($("#naissance").datepicker('getDate'));
		var cur = new Date();
		var diff = cur-birthdate;
		var age = Math.floor(diff/31536000000);
		
		// On affiche ou non le block tuteur et on actualise les propriété des inputs à valider
		if (age < 18 && $("#tuteur_block").css("display") == "none") {
			$("#tuteur_block #email_tuteur").removeClass("hidden");
			$("#tuteur_block #email_tuteur").prop('required',true);
			$("#tuteur_block #tel_tuteur").removeClass("hidden");
			$("#tuteur_block #tel_tuteur").prop('required',true);
			$("#tuteur_block").css("display","block");
		}
		else if (age >= 18 && $("#tuteur_block").css("display") == "block") {
			$("#tuteur_block").css("display","none");
			$("#tuteur_block #email_tuteur").addClass("hidden");
			$("#tuteur_block #email_tuteur").prop('required',false);
			$("#tuteur_block #tel_tuteur").addClass("hidden");
			$("#tuteur_block #tel_tuteur").prop('required',false);
		}

		
		// On actualise le validator
		$('#stage_inscription_form').bootstrapValidator('revalidateField', 'naissance');
		
		/*var $template = $('#email'),
			$clone = $template.clone().removeAttr('id'),
			$emailOptions = $clone.find('[name="email"]');*/
			
		/*var $template = $('#mobile'),
			$clone = $template.clone().attr('id','tel_tuteur'),
			$mobileOptions = $clone.find('[name="option[]"]');*/

			
		//$('#stage_inscription_form').bootstrapValidator('addField', $emailOptions);
		//$('#stage_inscription_form').bootstrapValidator('addField', $mobileOptions);*/
		//$('#stage_inscription_form').bootstrapValidator('validate');
	}
	
	

</script>



<!-- Formulaire !-->
<div id="stage_inscription_form" class="container-fluid">
		
	<!-- ****************** INFOS STAGE ***************** !-->
	<div class="block-heading">
		<h3>STAGE</h3>
	</div>
	
	<div id="stage_infos" class="panel panel-default no-border">
	
		<div class="row">
	
			<!-- **** LIEU **** !-->
			<div class="col-sm-6" style="display:flex">
				<!-- Picto !-->
				<div style="align-self:center"><img style="height: 18px; margin:0px 16px;" src="/images/icons/lieu.png" alt="lieu"></div>
				<!-- Block !-->
				<div>
					<!-- Nom du lieu !-->
					<div><h6><b><?php echo $lieu_stage_item['nom']; ?></b></h6></div>
					<!-- On n'affiche pas si pas de donnée !-->
					<?php if ($lieu_stage_item['adresse'] != "" || $lieu_stage_item['web'] != ""): ?>
						<p id="lieu_details" class="soften" style="font-size: 90%">
							<span id="lieu_adresse" style="display:<?php echo $lieu_stage_item['adresse'] == "" ? "none" : "block" ?>"><?php echo $lieu_stage_item['adresse']; ?></span>
							<a id="lieu_web" target="_blanck" style="display:<?php echo $lieu_stage_item['web'] == "" ? "none" : "block" ?>" href="http://<?php echo $lieu_stage_item['web']; ?>"><?php echo $lieu_stage_item['web']; ?></a>
						</p>
					<?php endif; ?>
				</div>
			</div>
			
			
			<!-- **** PLANNING **** !-->
			<div class="col-sm-6">
			
				<div style="display:flex;">
				
					<!-- Picto !-->
					<div style="align-self:center"><img style="height: 14px; margin:0px 16px;" src="/images/icons/cal.png" alt="lieu"></div>
					<!-- Date !-->
					<?php
						$this->load->helper('my_text_helper');
						$month1 = strftime("%b", strtotime($stage_item['date_debut']));
						if (!$this->config->item('online')) $month1 = utf8_encode($month1);
						$month1 =  substr(strtoupper(no_accent($month1)),0,3);
						
						$month2 = strftime("%b", strtotime($stage_item['date_fin']));
						if (!$this->config->item('online')) $month2 = utf8_encode($month2);
						$month2 =  substr(strtoupper(no_accent($month2)),0,3);
					?>
					<div class="date_box panel" style="align-self:center; margin: 20px 10px; padding: 5px">
						<div><small><?php echo $month1; ?></small></div>
						<div><strong><?php echo explode('-', $stage_item['date_debut'])[2] ?></strong></div>
					</div>
					
					<div style="align-self:center"><i class="cr-icon glyphicon glyphicon-arrow-right soften"></i></div>
					
					<div class="date_box panel" style="align-self:center; margin: 20px 10px; padding: 5px">
						<div><small><?php echo $month2; ?></small></div>
						<div><strong><?php echo explode('-', $stage_item['date_fin'])[2] ?></strong></div>
					</div>
				
				</div>
				
			</div>
		
		</div>
		
		<!-- **** FRAIS **** !-->
		<div class="row panel panel-footer" style="padding: 5px 15px 2px 15px">
			<div class="col-sm-12">
				<h5 class="pull-right" style="margin: 0px; font-family: rimouski;"><strong><?php echo $stage_item['cotisation'].' '; ?>&euro;</strong></h5>
			</div>
		</div>
		
	</div>
	
	<!-- TEXTE !-->
	<div class="small">
		<p>Le planning précis du stage sera transmis ultérieurement. Compter des journées de travail complètes (matin + soir).<p>
		<p>Les repas seront pris en autonomie à la Source même.<p>
		<p>Le stage est financé par la commune de Fontaine pour les élèves de la Source.<p>
	</div>
	
	<hr class="dark">

	<p class="note">Merci de compléter les données concernant votre profil</p>
		
		
	<!-- Infos inscription !-->
	<div class="row">
		
		<!-- Pseudo !-->
		<div class="col-sm-10 col-sm-offset-3" style="padding-top:0px; padding-bottom:10px">
			<h3><strong><?php echo $members_item->pseudo; ?></strong></h3>
		</div>
		
		
		<!-- Formulaire !-->
		<div class="container-fluid">
			<form id="stage_inscription_form" class="form-horizontal">

				<!-- Email !-->
				<div class="form-group required">
					<label for="email" class="control-label col-sm-3">Email</label>
					<div class="col-sm-9">
						<input id="email" class="form-control" required="true" type="email" name="email" value="<?php echo $members_item->email; ?>" data-error="Veuillez saisir une adresse email valide" required />
						<div class="help-block with-errors"></div>
					</div>
				</div>
				
				<!-- Nom !-->
				<div class="form-group required">
					<label for="nom" class="control-label col-sm-3">Nom</label>
					<div class="col-sm-9">
						<input id="nom" class="form-control" type="text" name="nom" value="<?php echo $members_item->nom ?>" />
						<div class="help-block with-errors"></div>
					</div>
				</div>
				
				<!-- Prénom !-->
				<div class="form-group required">
					<label for="prenom" class="control-label col-sm-3">Prénom</label>
					<div class="col-sm-9">
						<input id="prenom" class="form-control" type="text" name="prenom" value="<?php echo $members_item->prenom ?>" />
						<div class="help-block with-errors"></div>
					</div>
				</div>
				
				<!-- Date de naissance !-->
					<div class="form-group required">
						<label for="naissance" class="control-label col-sm-3">Date de naissance</label>
						<div class="col-sm-9">
							<input id="naissance" class="form-control" type="text" name="naissance" value="<?php echo empty($members_item->naissance) ? "" : $members_item->naissance ?>" />
						</div>
					</div>
					
				<!-- Mobile !-->
				<div class="form-group">
					<label for="mobile" class="control-label col-sm-3">Mobile</label>
					<div class="col-sm-9">
						<input id="mobile" class="form-control" type="text" name="mobile" value="<?php echo substr($members_item->mobile,0,2).' '.substr($members_item->mobile,2,2).' '.substr($members_item->mobile,4,2).' '.substr($members_item->mobile,6,2).' '.substr($members_item->mobile,8,2) ?>" />
						<div class="help-block with-errors"></div>
					</div>
				</div>

	
				
				<!-- Intrument 1 !-->
				<h4 class="col-sm-offset-3 col-sm-9" style="padding-bottom:10px"><strong>Instrument</strong></h4>
		
				<div class="form-group instruDiv">
					<label class='control-label col-sm-3' style='white-space: nowrap'>Instrument 1</label>
					<div class='btn-group col-sm-5'>
						<div class='btn btn-static instruItem coloredItem'>
							&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $members_item->instrument1; ?>&nbsp;&nbsp;&nbsp;&nbsp;
						</div>
					</div>
				</div>
				

				<!-- Année pratique !-->
				<div class="form-group required">
					<label for="nb_prat" class="control-label col-sm-7">Années de pratique instrumentale</label>
					<div class="col-sm-3">
						<select class="form-control" id="nb_prat">
							<?php
							for ($i = 0; $i<30; $i++) {
								echo "<option>".$i."</option>";
							}
							?>
						</select>
					</div>
				</div>
	
				<!-- Année pratique en groupe !-->
				<div class="form-group">
					<label for="nb_grp" class="control-label col-sm-7">Années de pratique en groupe</label>
					<div class="col-sm-3">
						<select class="form-control" id="nb_grp">
							<?php
							for ($i = 0; $i<30; $i++) {
								echo "<option>".$i."</option>";
							}
							?>
						</select>
					</div>
				</div>
				
				
				<!-- Ecole de musique !-->
				<div class="form-group">
					<label for="ecole" class="control-label col-sm-3">Ecole de musique</label>
					<div class="col-sm-9">
						<input id="ecole" class="form-control" type="text" name="ecole" />
					</div>
				</div>
				
				<!-- Professeur actuel !-->
				<div class="form-group">
					<label for="prof" class="control-label col-sm-3">Professeur actuel</label>
					<div class="col-sm-9">
						<input id="prof" class="form-control" type="text" name="prof" />
					</div>
				</div>
				
				
				<!-- TUTEUR !-->
				<div id="tuteur_block">
				
					<h4 class="col-sm-offset-3 col-sm-9" style="padding-bottom:10px"><strong>Tuteur</strong></h4>
					
					
					<!-- Email tuteur !-->
					<div class="form-group required">
						<label for="email_tuteur" class="control-label col-sm-3">Email tuteur</label>
						<div class="col-sm-9">
							<input id="email_tuteur" class="form-control" type="email" name="email_tuteur" />
							<div class="help-block with-errors"></div>
						</div>
					</div>
					
					<!-- Téléphone tuteur !-->
					<div class="form-group required">
						<label for="tel_tuteur" class="control-label col-sm-3">Téléphone tuteur</label>
						<div class="col-sm-9">
							<input id="tel_tuteur" class="form-control" type="text" name="tel_tuteur" />
							<div class="help-block with-errors"></div>
						</div>
					</div>
					
				</div>
				
				
				<!-------- Remarque --------->
				<div class="form-group">
					<label for="remarque" class="control-label col-sm-3">Remarque</label>
					<div class="row">
						<div class="col-sm-9">
							<textarea id="remarque" class="form-control autosize" name="text" style="resize:none"></textarea>
						</div>
					</div>
				</div>

				
				<hr class="dark">
				
				<!--
				<div class="small">
					<p>Votre inscription au stage sera validée sur réception du chèque qui vous sera demandé lors de l'envoie du formulaire ci-contre.</p>
				</div>!-->
				
				<div class="form-group">
					<input class="pull-right btn btn-primary" type="submit" name="submit" value="Envoyer" />
				</div>
				
				
			</form>
		</div>
			
	</div>
</div>

