<!-- Editeur html -->
<script src="<?php echo base_url();?>ressources/script/ckeditor/ckeditor.js"></script>

<!-- autoresize texarea !-->
<script type="text/javascript" src="<?php echo base_url();?>ressources/script/autosize.js"></script>

<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>ressources/script/validator.js"></script>




<script type="text/javascript">

	$(function() {
		
		/******** Bootstrap validator ********/
		$('#send_mail_form form').validator();
		$('#send_mail_form form').validator().on('submit', function (e) {
			
			if (e.isDefaultPrevented()) {
				// handle the invalid form...
			}
			else {
				// On bloque le comportement par défault du submit
				e.preventDefault();
				// Pas de problem avec le validator
				send_mail();
			}
		})

		
		// On initialise le autoresize
		$('.autosize').autosize({append: "\n"});		
		
	});


	
	
	/****** UPDATE NEWS  *******/
	function send_mail() {
	
	
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/group/ajax_send_mail",
		
			{	
				'subject':$("#send_mail_form #subject").val(),
				'text_html':CKEDITOR.instances.editor.getData()
			},
		
			function (return_data) {
	
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Email bien envoyé
				if ($obj['state'] == 1) {
					$("#MailModal").modal('hide').on('hidden.bs.modal', function () {
						$("#modal_msg .modal-dialog").removeClass("error");
						$("#modal_msg .modal-dialog").addClass("success");
						$("#modal_msg .modal-header").html("Email envoyé.");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<button type="button" class="btn" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
						$("#modal_msg").modal('show').on('hidden.bs.modal', function () {
							$(this).unbind();
						});
						$(this).unbind();
					});
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
<div id="send_mail_form" class="container-fluid">
	<form class="form-horizontal">
		
		<!-------- TITRE --------->
		<div class="form-group required">
			<label for="subject" class="control-label col-sm-1">Sujet</label>
			<div class="col-sm-11">
				<input id="subject" class="form-control" required type="text" name="subject" value="Grenoble Reggae Orchestra ~ " placeholder="Sujet du mail" />
			</div>
		</div>

		
		<!-------- TEXTE --------->
		<div class="form-group">
			<div class="row">
				<div class="col-sm-12">
					<textarea name="editor" id="editor" rows="10" cols="80">
					</textarea>
					<script>
						// Replace the <textarea id="editor"> with a CKEditor
						// instance, using default configuration.
						CKEDITOR.replace( 'editor' );
					</script>
				</div>
			</div>
		</div>
	

		<hr>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			<button type="submit" class="btn btn-primary">Envoyer</button>
		</div>

	</form>
</div>