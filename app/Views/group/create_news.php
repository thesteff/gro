<!-- Editeur html -->
<script src="<?php echo base_url();?>/ressources/script/ckeditor/ckeditor.js"></script>

<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/validator.js"></script>


<script type="text/javascript">

	$(function() {

		/******** Bootstrap validator ********/
		$('#news_create_form form').validator();
		$('#news_create_form form').validator().on('submit', function (e) {
			
			if (e.isDefaultPrevented()) {
				// handle the invalid form...
			}
			else {
				// On bloque le comportement par défault du submit
				e.preventDefault();
				// Pas de problem avec le validator
				create_news();
			}
		})
		
	});



	/****** CREATE NEWS  *******/
	function create_news() {
	
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url('group/ajax_create_news'); ?>",
		
			{	
				'title':$("#news_create_form #title").val(),
				'text_html':CKEDITOR.instances.create_editor.getData(),
				'top':$("#news_create_form #news_top").is(":checked")
			},
		
			function (return_data) {
	
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					window.location.reload();
				}
				else {
					$("#updateModal").modal('hide');
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
<div id="news_create_form" class="container-fluid">
	<form class="form-horizontal">
		
		<!-------- TITRE --------->
		<div class="form-group required">
			<label for="title" class="control-label col-sm-2">Titre</label>
			<div class="col-sm-9">
				<input id="title" class="form-control" required type="text" name="title" value="" placeholder="Titre de la news" />
			</div>
		</div>
		
		<!---------- NEWS en TOP  -------->
		<div class="form-group">
			<label for="news_top" class="control-label col-sm-2 adjust-xs">Tête de liste</label>
			<div class="checkbox col-sm-2 col-xs-2">
				<label style="padding-left: 0px">
					<input id="news_top" class="form-control" name="news_top" type="checkbox" value="" />
					<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
				</label>
			</div>
		</div>
		
		<hr>


		<!-------- TEXTE --------->
		<div class="form-group">
			<div class="row">
				<div class="col-sm-12">
					<textarea name="create_editor" id="create_editor" rows="10" cols="80">
					</textarea>
					<script>
						// Replace the <textarea id="create_editor"> with a CKEditor
						// instance, using default configuration.
						CKEDITOR.replace( 'create_editor' );
					</script>
				</div>
			</div>
		</div>
	

		<hr>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			<button type="submit" class="btn btn-primary">Ajouter</button>
		</div>

	</form>
</div>