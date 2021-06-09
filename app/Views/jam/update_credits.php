<!-- autoresize texarea !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/autosize.js"></script>

<!-- Editeur html -->
<script src="<?php echo base_url();?>/ressources/script/ckeditor/ckeditor.js"></script>

<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/validator.js"></script>


<script type="text/javascript">

	$(function() {
		
		/******** Bootstrap validator ********/
		$('#update_credits_form form').validator();
		$('#update_credits_form form').validator().on('submit', function (e) {
			
			if (e.isDefaultPrevented()) {
				// handle the invalid form...
			}
			else {
				// On bloque le comportement par défault du submit
				e.preventDefault();
				// Pas de problem avec le validator
				update_credits(CKEDITOR.instances.credits_textarea.getData());
			}
		})

		// On initialise le autoresize
		$('.autosize').autosize({append: "\n"});
		
	});
	
	
	// On initialise les textarea
	CKEDITOR.replace( 'credits_textarea', {
		customConfig: '/ressources/script/ckeditor/config_light2.js'
	});


	
	
	/****** UPDATE CREDITS  *******/
	function update_credits($new_html) {
	
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_jam/update_credits",
		
			{	
				'jamId':"<?php echo $jam_item['id']; ?>",
				'credits':$new_html,
			},
		
			function (return_data) {
	
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				$("#updateCreditsModal").modal("hide");
				if ($obj['state'] == 1) {
					 refresh_credits($new_html);
				}
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
					$("#modal_msg").modal('show');
				}
			}
		);
    }
	
	
 </script>


<!-- Formulaire !-->
<div id="update_credits_form" class="container-fluid">
	<form class="form-horizontal">
		
		<!-------- TEXTE --------->
		<div class="form-group">
			<div class="row">
				<div class="col-sm-12">
					<textarea id="credits_textarea" class="form-control" name="text" placeholder="Crédits" style="resize:none"><?php echo $jam_item['credits_html'] ?></textarea>
				</div>
			</div>
		</div>

		<hr>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			<button type="submit" class="btn btn-primary">Modifier</button>
		</div>

	</form>
</div>