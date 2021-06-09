
<!-- bootstrap datepicker !-->
<script type="text/javascript" src="<?php echo base_url("ressources/bootstrap-datepicker-1.6.4/js/bootstrap-datepicker.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url("ressources/bootstrap-datepicker-1.6.4/locales/bootstrap-datepicker.fr.min.js"); ?>"></script>
<link rel="stylesheet" href="<?php echo base_url("ressources/bootstrap-datepicker-1.6.4/css/bootstrap-datepicker3.css"); ?>"/>

<!-- bootstrapValidator !-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.js"></script>

<!-- Bootstrap-select !-->
<link rel="stylesheet" href="<?php echo base_url("ressources/bootstrap-select/bootstrap-select.min.css" ); ?>"/>
<script type="text/javascript" src="<?php echo base_url("ressources/bootstrap-select/bootstrap-select.min.js"); ?>"></script>

<!-- Cropper js !-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.7/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.7/cropper.min.css"/>


<script type="text/javascript">

	$(function() {

		$('#profil_form').bootstrapValidator({
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
				// le widget du datepicker assure la saisie d'une date   :-)
				/*naissance: {
					validators: {
						date: {
							format: 'DD/MM/YYYY',
							message: 'La date doit être valide'
						}
					}
				},*/
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
        .on('success.form.bv', function(e) {
            //$('#success_message').slideDown({ opacity: "show" }, "slow") // Do something ...
            $('#profil_form').data('bootstrapValidator').resetForm();

            // Prevent form submission
            e.preventDefault();
            // Get the form instance
            var $form = $(e.target);
            // Get the BootstrapValidator instance
            var bv = $form.data('bootstrapValidator');

			console.log($('#profil_form #genre').val());
			
            // Requète ajax au serveur
			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url('ajax_members/update_member'); ?>",
			
				// On récupère les données nécessaires
				{
					'id':'<?php echo $member_item->id ?>',
					'email':$('#profil_form #email').val(),
					'nom':$('#profil_form #nom').val(),
					'prenom':$('#profil_form #prenom').val(),
					'genre':$('#profil_form #genre').val(),
					'naissance':$('#profil_form #naissance').val(),
					'mobile':$('#profil_form #mobile').val().replace(/\s/g, ''),
					'allowMail':$('#profil_form #allowMail').is(':checked') ? "1" : "0",
					'freqRecapMail':$('#profil_form #freqRecapMail').val()
				},
				
				// On traite la réponse du serveur			
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					// On change le curseur
					document.body.style.cursor = 'default';

					// Profil mis à jour
					if ($obj['state']) {
						// Success
						$("#modal_msg .modal-dialog").removeClass("error");
						$("#modal_msg .modal-dialog").addClass("success");
						$("#modal_msg .modal-header").html("Profil mis à jour !");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<button class="btn btn-default" id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
					}
					
					// Profil inchangé
					else {
						// Erreur
						$("#modal_msg .modal-dialog").removeClass("success");
						$("#modal_msg .modal-dialog").addClass("error");
						$("#modal_msg .modal-header").html("Erreur !");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
					}
					$("#modal_msg").modal("show");
				}
			);
        });
		
		
		
		$('#pass_form').bootstrapValidator({
        // To use feedback icons, ensure that you use Bootstrap v3.1.0 or later
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            pass: {
                validators: {
                    notEmpty: {
                        message: 'Vous devez saisir votre mot de passe actuel'
                    },
					stringLength: {
                        min: 6,
                        message: 'Votre mot de passe contient au moins 6 caractères'
                    }
				}
            },
            pass2: {
                validators: {
                    notEmpty: {
                        message: 'Vous devez saisir votre nouveau mot de passe'
                    },
                    stringLength: {
                        min: 6,
                        message: 'Votre nouveau mot de passe doit contenir au moins 6 caractères'
                    }
                }
            }
         }
        })
        .on('success.form.bv', function(e) {
            //$('#success_message').slideDown({ opacity: "show" }, "slow") // Do something ...
            $('#pass_form').data('bootstrapValidator').resetForm();

            // Prevent form submission
            e.preventDefault();
            // Get the form instance
            var $form = $(e.target);
            // Get the BootstrapValidator instance
            var bv = $form.data('bootstrapValidator');

			
            // Requète ajax au serveur
			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url('ajax_members/update_pass_member'); ?>",
			
				// On récupère les données nécessaires
				{
					//'memberLogin':$("#memberLogin"),
					'memberId':'<?php echo $member_item->id ?>',
					'pass':$('#pass_form #pass').val(),
					'pass2':$('#pass_form #pass2').val()
				},
				
				// On traite la réponse du serveur			
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					// On change le curseur
					document.body.style.cursor = 'default';

					// Profil mis à jour
					if ($obj['state']) {
						// Success
						$("#modal_msg .modal-dialog").removeClass("error");
						$("#modal_msg .modal-dialog").addClass("success");
						$("#modal_msg .modal-header").html("Mot de passe mis à jour");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
					}
					
					// Profil inchangé
					else {
						// Erreur
						$("#modal_msg .modal-dialog").removeClass("success");
						$("#modal_msg .modal-dialog").addClass("error");
						$("#modal_msg .modal-header").html("Erreur !");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
					}
					$("#modal_msg").modal("show");
				}
			);
        });
		
		
		
		// On initialise le datepicker
		$('#naissance').datepicker({
			format: "dd/mm/yyyy",
			todayBtn: "linked",
			language: "fr",
			todayHighlight: true,
			startView: 2
		});
		
		
		// On initialise le selectpicker du genre
		$('#profil_form #genre').selectpicker('val', '<?php echo $member_item->genre ?>');
		
		
		
		// On initialise le selectpicker du freqRecapMail
		$('#profil_form #freqRecapMail').selectpicker('val', '<?php echo $member_item->freqRecapMail ?>');
		
		// On initialise son affichage en fonction de allowMail
		if ($('#profil_form #allowMail').prop("checked")) 
			$('#profil_form #freqRecapMail').parents(".form-group").first().removeClass("hidden");
		
		// On actualise son affichage en fonction de allowMail		
		$('#profil_form #allowMail').on("change", function() {
			$('#profil_form #freqRecapMail').parents(".form-group").first().toggle();
		});
		

		
		// Validation dynamique de l'email par Ajax
		$("#profil_form #email").change(function() {
		
			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url('ajax_members/check_email'); ?>",
			
				// On récupère les données nécessaires
				{'email':$('#profil_form #email').val()
				},
				
				// On traite la réponse du serveur
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					document.body.style.cursor = 'default';

					// Si le pseudo est déjà pris
					if ($obj['state']) {
						$("#modal_msg").attr("target","email");
						$("#modal_msg .modal-dialog").addClass("error");
						$("#modal_msg .modal-dialog").removeClass("success");
						$("#modal_msg .modal-header").html("Erreur");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<button class="btn btn-default" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
						$("#modal_msg").modal("show");
					}
				}
			);

		});
		
	
		// Dialog box pour la validation dynamique
		$('#modal_msg').on('hidden.bs.modal', function () {
			$target = $("#modal_msg").attr("target");
			if ($target) {
				$('#profil_form').data('bootstrapValidator').resetForm();
				$('#profil_form #'+$target).val('<?php echo $member_item->email; ?>'); 	// à priori on check que l'email dynamiquement
				$('#profil_form #'+$target).focus();
				$("#modal_msg").attr("target","");
			}
		});
		
		
		// ***************** AVATAR ****************
		// *****************************************
		
		var image = document.getElementById('imageAvatar');
		
		// On charge l'image de l'avatar
		$.ajax({
			url:'<?php echo base_url("images/avatar"); ?>'+'/'+$("#memberLogin").attr("idKey")+'.png',
			type:'HEAD',
			success: function() {
				$("#avatar img").prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+$("#memberLogin").attr("idKey")+'.png');
			}
		});
		
		
		$("#avatar").hover(function() {
			$(this).find("button").fadeIn("fast");
		},function() {
			$(this).find("button").fadeOut("fast");
		});
		
		// ***** Cropper options ******
		$("#updateAvatarModal #btnOptions button").on('click', function(event) {
			$target = $(event.target);
			
			//console.log("btn : "+$target.parents("button").prop("id"));
			
			switch($target.parents("button").prop("id")) {
				case "btnRotate1":
					cropper.rotate(45);
					break;
				case "btnRotate2":
					cropper.rotate(-45);
					break;
				case "btnInvert1":
					cropper.scaleX(cropper.getImageData().scaleX * -1);
					break;
				case "btnInvert2":
					cropper.scaleY(cropper.getImageData().scaleY * -1);
					break;
				case "btnReset":
					cropper.reset();
					break;
				default:
				// code block
			} 
		});
		
		
		// **** SEND new avatar ***
		$("#updateAvatarModal #btnSend").on('click', function(event) {

			// On récupère la nouvelle image
			$croppedImg = cropper.getCroppedCanvas({
				width: 128,
				height: 128,
				minWidth: 128,
				minHeight: 128,
				fillColor: '#fff',
				imageSmoothingEnabled: true,
				imageSmoothingQuality: 'high',
			});
			
			
			// On change le curseur
			document.body.style.cursor = 'progress';
			
			// Upload cropped image to server if the browser supports `HTMLCanvasElement.toBlob`.
			// The default value for the second parameter of `toBlob` is 'image/png', change it if necessary.
			canvas = cropper.getCroppedCanvas({
				width: 256,
				height: 256,
			});
			
			
			canvas.toBlob(function (blob) {
				var formData = new FormData();

				formData.append('file', blob);
				formData.append("memberId",'<?php echo $member_item->id ?>');
				
				$.ajax('<?php echo site_url(); ?>/ajax_members/update_avatar', {
					method: 'POST',
					data: formData,
					processData: false,
					contentType: false,

					/*xhr: function () {
						var xhr = new XMLHttpRequest();

						xhr.upload.onprogress = function (e) {
							var percent = '0';
							var percentage = '0%';

							if (e.lengthComputable) {
								percent = Math.round((e.loaded / e.total) * 100);
								percentage = percent + '%';
								$progressBar.width(percentage).attr('aria-valuenow', percent).text(percentage);
							}
						};

						return xhr;
					},*/

					success: function (return_data) {
						console.log('Upload success : '+return_data);
						
						// On change le curseur
						document.body.style.cursor = 'default';
						
						// On ferme la modal (ce qui détruit le cropper)
						$("#updateAvatarModal").modal("hide");
						
						// On actualise l'image courante avec la nouvelle
						$("#avatar img").prop("src",canvas.toDataURL());
					},

					error: function () {
						console.log('Upload error');
					},
				});
			});
			
		});
		
			
		// **************** CROPPER MODAL
		// On attend que la modal soit complètement ouverte
		$("#updateAvatarModal").on('shown.bs.modal', function() {
			// On instancie le cropper
			cropper = new Cropper(image, {
				aspectRatio: 1
			});
		});
		
		// On détruit le cropper à la fermeture
		$("#updateAvatarModal").on('hidden.bs.modal', function () {
			cropper.destroy();
			cropper = null;
		});
		
		
		// **************** File Selected
		$("#fileSelectInput").on("change", function(e) {
			// On ouvre la modal d'update
			$("#updateAvatarModal").modal();
			// On récupère l'image select
			image.src = URL.createObjectURL(event.target.files[0]);
		});
		
		
		
		// ****** INSTRUMENTS MODALS ********
		$("[id$='InstruModal'").on("show.bs.modal", function(e) {
			var link = $(e.relatedTarget);
			$(this).find(".modal-content").load(link.attr("href"));
		});
		
		
		/**************************** Instrument IHM ***************************/
		// On affiche la liste d'instruments joués par le membre
		show_instruList();
		
	});
	
	
	// ************ AVATAR ************
	// Pour ouvrir une fenêtre d'exploration afin de selec un fichier
	function browse(target) {
		//$("#modal_msg").modal('hide');
		$(target).trigger('click');
	}
	
	
	/************************ Validation Mail ***********************/
	//////////////////////////////////////////////////////////////////
	
	function sendValidationMail() {
		// On change le curseur
		document.body.style.cursor = 'wait';

		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_members/sendValidationMail'); ?>",
		
			{
				'memberId' : <?php echo $member_item->id; ?>
			},
	
			function (return_data) {
				
				console.log(return_data);
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					$("#modal_msg .modal-dialog").addClass("success");
					$("#modal_msg .modal-dialog").removeClass("error");
					$("#modal_msg .modal-header").html("Email envoyé");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<button class="btn btn-default" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
					$("#modal_msg").modal("show");		
				}
				
				// Erreur
				else {
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-header").html("Erreur");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<button class="btn btn-default" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
					$("#modal_msg").modal("show");
				}
				
				
		});
	}
	
	
	//////////////////////////////////////////////////////////////
	/************************ INSTRUMENT ***********************/
	//////////////////////////////////////////////////////////////
	
	// On affiche la liste d'instruments joués par le membre
	function show_instruList() {
		
		// On change le curseur
		document.body.style.cursor = 'wait';

		// On récupère le memberId
		$memberId = <?php echo $member_item->id ?>;

		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_instruments/get_member_instruments'); ?>",
		
			{
				'memberId' : $memberId
			},
	
			function (return_data) {
							
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// On vide la liste
					$("#listInstruDiv").empty();
					
					// On remplit la liste
					$div = "";
					for (i = 0; i < $obj['data'].length; i++) {
						$div += "<div class='form-group instruDiv'>";
							$div += "<label class='control-label col-sm-2' style='white-space: nowrap'>Instrument "+(i+1)+"</label>";
							$div += "<div class='btn-group col-sm-5'>";
								<!-- Label !-->
								$div += "<div class='btn btn-static instruItem coloredItem' instruId='"+$obj['data'][i]["instruId"]+"' instruName='"+$obj['data'][i]["instruName"]+"'>&nbsp;&nbsp;&nbsp;&nbsp;"+$obj['data'][i]["instruName"]+"&nbsp;&nbsp;&nbsp;&nbsp;</div>";
								<!-- Modifier -->
								$div += '<button class="btn btn-default update_btn" href="<?php echo site_url("members/update_instrument/").$member_item->slug ?>/'+$obj['data'][i]["instruId"]+'" data-remote="false" data-toggle="modal" data-target="#updateInstruModal"><i class="glyphicon glyphicon-pencil"></i></button>';

								<!-- Supprimer -->
								$div += '<button class="btn btn-default delete_btn" title="Supprimer instrument"><i class="glyphicon glyphicon-trash"></i></button>';
							$div += "</div>";
						$div += "</div>";
					}
					$("#listInstruDiv").append($div);
					
					// ADMIN IHM
					// On fixe le comportement des bouttons d'admin de delete
					$('#listInstruDiv .delete_btn').each(function(index) {
						$(this).on("click", function() {
							$instruId = $(this).siblings(".instruItem").attr("instruId");
							$instruName = $(this).siblings(".instruItem").attr("instruName");
							popup_delete_instru($instruId, $instruName);
						});
					});
					
					// On affiche la liste
					$("#listInstruDiv").fadeIn("fast");
				}
				else console.log("error");
			}
		);

		
	}

	
	// ******** DELETE INSTRUMENT *********/
	function popup_delete_instru($instruId, $instruName) {
		$text = "Etes-vous sûr de vouloir supprimer <b>"+$instruName+"</b> de votre liste d'instrument ?";
		$confirm = "<div class='modal-footer'>";
			$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
			$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_instru(\""+$instruId+"\")'>Supprimer</button>";
		$confirm += "</div>";
		
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-dialog").addClass("backdrop","static");
		$("#modal_msg .modal-header").html("Supprimer l'instrument");
		$("#modal_msg .modal-body").html($text);
		$("#modal_msg .modal-footer").html($confirm);
		$("#modal_msg").modal('show');
	}
	
	
	function delete_instru($instruId) {
	
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		// On récupère l'id du membre
		$memberId = <?php echo $member_item->id ?>;

		$.post("<?php echo site_url('ajax_instruments/delete_member_instrument'); ?>",
		
			{
				'instruId' : $instruId,
				'memberId' : $memberId
			},
	
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// On ferme la modal
					$("#modal_msg").modal('toggle');
					
					// On sort d'un eventuel update
					//escape_update();
					
					// L'instrument à été supprimé de la bd => on actualise l'affichage
					show_instruList();
					
					// On sort d'un éventuel add new
					//escape_new();
					
				}
				else console.log("error");
			}
		);
	}
	
	
</script>



<div class="row">

	<!-- Block principal !-->
	<div class="col-md-9 col-lg-9 panel panel-default">
		
	
			<div id="profil-title" class="row">
	
				<!-- Avatar !-->
				<div id="avatar" class="col-sm-3 text-center" style="padding-top:15px; padding-bottom:25px">
					<img class='img-circle img-thumbnail' src='<?php echo base_url("images/icons/avatar1.png"); ?>' width="128" height="128">
					<!-- Upload -->
					<button class="fileUpload btn btn-xs btn-primary" onclick="javascript:browse('#profil-title #fileSelectInput')" title="Envoyer une image"><i class="glyphicon glyphicon-upload"></i></button>

				</div>
				
				<!-- Input invisible qui se charge de récupérer le fichier !-->
				<input id="fileSelectInput" type="file" name="file" accept="image/*" style="display:none" />
				
				<!-- Pseudo !-->
				<div class="col-sm-9 bottom-align-text "style="padding-bottom:15px">
					<h2><strong><?php echo $member_item->pseudo; ?></strong></h2>
				</div>
				
			</div>
	
			<!-- Formulaire !-->
			<div class="container-fluid">
				<form id="profil_form" class="form-horizontal">

					<!-- Email !-->
					<div class="form-group required">
						<label for="email" class="control-label col-sm-2">Email</label>
						<div class="col-sm-10">
							<input id="email" class="form-control" required="true" type="email" name="email" value="<?php echo $member_item->email; ?>" />
						</div>
					</div>
					
					<!-- Nom !-->
					<div class="form-group">
						<label for="nom" class="control-label col-sm-2">Nom</label>
						<div class="col-sm-10">
							<input id="nom" class="form-control" type="text" name="nom" value="<?php echo $member_item->nom; ?>" />
						</div>
					</div>
					
					<!-- Prénom !-->
					<div class="form-group">
						<label for="prenom" class="control-label col-sm-2">Prénom</label>
						<div class="col-sm-10">
							<input id="prenom" class="form-control" type="text" name="prenom" value="<?php echo $member_item->prenom ?>" />
						</div>
					</div>
					
					<!-- Date de naissance !-->
					<div class="form-group">
						<label for="naissance" class="control-label col-sm-2">Date de naissance</label>
						<div class="col-sm-10">
							<input id="naissance" class="form-control" type="text" name="naissance" value="<?php echo empty($member_item->naissance) ? "" : $member_item->naissance ?>" />
						</div>
					</div>
					
					<!-- Genre !-->
					<div class="form-group">
						<label for="genre" class="control-label col-sm-2">Genre</label>
						<div class="col-sm-10">
							<select id="genre" class="form-control selectpicker" name="genre" data-style="btn-default">
								<option value="0">Non spécifié</option>
								<option value="1">Homme</option>
								<option value="2">Femme</option>
							</select>
						</div>
					</div>
					
					
					<!-- Mobile !-->
					<div class="form-group">
						<label for="mobile" class="control-label col-sm-2">Mobile</label>
						<div class="col-sm-10">
							<input id="mobile" class="form-control" type="text" name="mobile" value="<?php echo $member_item->mobile != '' ? substr($member_item->mobile,0,2).' '.substr($member_item->mobile,2,2).' '.substr($member_item->mobile,4,2).' '.substr($member_item->mobile,6,2).' '.substr($member_item->mobile,8,2) : "" ?>" />
						</div>
					</div>


					<!-- Allow Mail !-->
					<div class="form-group">
						<label for="allowMail" class="control-label col-sm-2">Autoriser les emails</label>
						<div class="checkbox col-sm-10">
							<label>
								<input id="allowMail" type="checkbox" value="" <?php if ($member_item->allowMail == "1") echo "checked"; ?>>
								<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
							</label>
						</div>
					</div>
					
					
					<!-- freqRecapMail !-->
					<div class="form-group hidden">
						<label for="freqRecapMail" class="control-label col-sm-2">Notifications par mail</label>
						<div class="checkbox col-sm-10">
							<select id="freqRecapMail" class="form-control selectpicker" name="freqRecapMail" data-style="btn-default">
								<option value="0">Jamais</option>
								<option value="1">Tous les jours</option>
								<option value="2">Toutes les semaines</option>
								<option value="3">Tous les mois</option>
							</select>
						</div>
					</div>
					
					
	
					<!-- Envoyer !-->
					<input id="update" class="btn btn-primary pull-right" type="submit" value="Modifier"/>
					
					<!--Valid Mail !-->
					<?php if ($member_item->validMail == 0) : ?>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<div class="alert alert-danger small">
									<p><span class="alert-danger glyphicon glyphicon-warning-sign"></span>&nbsp;<strong>Profil non validé !</strong></p>
									<p>Votre adresse email n'a pas encore été validée. Pour activer votre compte et profiter des fonctionnalités du site, merci de cliquer sur le lien présent dans le mail de validation.</p>
									<br>
									<div class="btn btn-xs btn-danger pull-right small" onclick="javascript:sendValidationMail()">Envoyer mail de validation</div>
								</div>
							</div>
						</div>
					<?php endif ?>
					
				</form>
			</div>
			
			
			<!-- INSTRUMENTS !-->
			<h4 class="col-sm-offset-2 col-sm-10" style="padding-bottom:10px"><strong>Instrument</strong></h4>
			
			<div class="container-fluid" style="padding-bottom:15px">
				<form id="instruments" class="form-horizontal" action="javascript:void(0)">
		
					<!-- LISTE des instruments joués par le membre !-->
					<div id="listInstruDiv" style="display:none; margin-right: 15px">
					</div>

					<!-- ADD INSTRUMENT !-->
					<button class="btn btn-default col-sm-offset-2 col-sm-10" href="<?php echo site_url("members/add_instrument/") ?><?php echo $member_item->slug ?>" data-remote="false" data-toggle="modal" data-target="#addInstruModal">Ajouter un instrument</button>

				</form>

			</div>			
			
			
			
			<!-- PASSWORD !-->
			<div class="container-fluid">
				<form id="pass_form" class="form-horizontal" action="javascript:update_password()">
		
					
					<h4 class="col-sm-offset-2 col-sm-10" style="padding-bottom:10px"><strong>Sécurité</strong></h4>
					
					<!-- Pass !-->
					<div class="form-group">
						<label for="pass" class="control-label col-sm-2">Mot de passe</label>
						<div class="col-sm-10">
							<input id="pass" class="form-control" type="password" name="pass" value="" autocomplete="off" placeholder="Mot de passe actuel" />
						</div>
					</div>
					
					<!-- New Pass !-->
					<div class="form-group">
						<label for="pass2" class="control-label col-sm-2">Nouveau mot de passe</label>
						<div class="col-sm-10">
							<input id="pass2" class="form-control" type="password" name="pass2" value="" autocomplete="off"  placeholder="Nouveau mot de passe" />
						</div>
					</div>
					
					
					<!-- Infos admin !-->
					<?php if ($isSuperAdmin == 1) : ?>
						<div class="col-sm-offset-2 col-sm-10 alert alert-success">
							<strong>Vous êtes super administrateur du site</strong>
						</div>
					<?php endif; ?>
					
					<!-- Envoyer !-->
					<input id="update_pass" class="btn btn-primary pull-right" type="submit" value="Modifier"/>
					
					
				</form>

			</div>	
			
	</div>
		
	<!-- Block de droite !-->
	<div class="col-md-3 col-lg-3">		<!-- On sépare col et panel pour avoir un pad !-->
		<div class="panel panel-default">
			<div class="panel-body" style="padding-top:15px">	<!-- On rajoute le padding top !-->
				Attention lorsque vous modifiez votre profil, cela peut annuler vos données d'inscription à une Jam (avec un changement d'instrument par exemple).
			</div>
		</div>
	</div>
		
		
</div>



<!-- Dialogue box de resultat !-->
<div id="modal_msg" class="modal fade" role="dialog" data-keyboard="true" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header lead"></div>
			<div class="modal-body"></div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>


<!-- ******** MODAL ADD INSTRUMENT ******* !-->
<div id="addInstruModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		...
	</div>
	</div>
</div>

<!-- ******** MODAL UPDATE INSTRUMENT ******* !-->
<div id="updateInstruModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		...
	</div>
	</div>
</div>

<!-- ******** MODAL UPDATE AVATAR ******* !-->
<div id="updateAvatarModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		<div class="modal-header lead">Modifier l'image du profil</div>
		<div class="modal-body">
		
			<!-- CROPPER !-->
			<div class="row text-center">
				<img id="imageAvatar">
			</div>
			
			<!-- OPTION !-->
			<div id="btnOptions" class="row text-center panel-body">
			
				<!-- Rotate !-->
				<div class="btn-group">
					<button id="btnRotate1" type="button" class="btn btn-xs btn-primary">
						<span title="Tourner l'image">
							<image src='<?php echo base_url("images/icons/rotate1.png"); ?>' width='20px' height='20px' css="filter: invert(1)">
						</span>
					</button>
					<button id="btnRotate2" type="button" class="btn btn-xs btn-primary">
						<span title="Tourner l'image">
							<image src='<?php echo base_url("images/icons/rotate2.png"); ?>' width='20px' height='20px'>
						</span>
					</button>
				</div>
				
				<!-- Miror !-->
				<div class="btn-group">
					<button id="btnInvert1" type="button" class="btn btn-xs btn-primary">
						<span title="Inverser l'image">
							<image src='<?php echo base_url("images/icons/miror1.png"); ?>' width='20px' height='20px'>
						</span>
					</button>
					<button id="btnInvert2" type="button" class="btn btn-xs btn-primary">
						<span title="Inverser l'image">
							<image src='<?php echo base_url("images/icons/miror2.png"); ?>' width='20px' height='20px'>
						</span>
					</button>
				</div>
				
				<!-- Reset !-->
				<button id="btnReset" type="button" class="btn btn-xs btn-primary">
					<i class="glyphicon glyphicon-refresh"></i>
				</button>
				
			</div>
				
				
			<!-- Envoyer !-->
			<div class="row text-center">
				<button id="btnSend" type="button" class="btn btn-primary">
					Envoyer
				</button>
			</div>
			
		</div>
	</div>
	</div>
</div>