<!-- Editeur html -->
<script src="<?php echo base_url();?>/ressources/script/ckeditor/ckeditor.js"></script>

<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/validator.js"></script>


<script type="text/javascript">

	$(function() {
		
		/******** Bootstrap validator ********/
		$('#news_update_form form').validator();
		$('#news_update_form form').validator().on('submit', function (e) {
			
			if (e.isDefaultPrevented()) {
				// handle the invalid form...
			}
			else {
				// On bloque le comportement par défault du submit
				e.preventDefault();
				// Pas de problem avec le validator
				update_news();
			}
		})
		
	});


	
	
	/****** UPDATE NEWS  *******/
	function update_news() {
	
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/group/ajax_update_news/<?php echo $news_item['id']; ?>",
		
			{	
				'title':$("#news_update_form #title").val(),
				'text_html':CKEDITOR.instances.editor1.getData(),
				'top':$("#news_update_form #news_top").is(":checked"),
			},
		
			function (return_data) {
	
				console.log(return_data);
	
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
<div id="news_update_form" class="container-fluid">
	<form class="form-horizontal">
		
		<!-------- TITRE --------->
		<div class="form-group required">
			<label for="title" class="control-label col-sm-2">Titre</label>
			<div class="col-sm-9">
				<input id="title" class="form-control" required type="text" name="title" value="<?php echo $news_item['title']; ?>" placeholder="Titre de la news" />
			</div>
		</div>
		
		<!-------- DATE --------->
		<!--<div class="form-group required">
			<label for="date_jam" class="control-label col-sm-2 col-xs-3 adjust-xs">Date</label>
			<div class="col-sm-3 col-xs-6">
				<input id="date_jam" class="form-control text-center" required="true" type="text" name="date_jam" value="<?php echo $news_item['date_label']; ?>" autocomplete="off" />
			</div>
		</div>!-->
		
		<!---------- NEWS en TOP  -------->
		<div class="form-group">
			<label for="news_top" class="control-label col-sm-2 adjust-xs">Tête de liste</label>
			<div class="checkbox col-sm-2 col-xs-2">
				<label style="padding-left: 0px">
					<input id="news_top" class="form-control" name="news_top" type="checkbox" value="" <?php if ($news_item['top'] > 0) echo "checked"; ?> />
					<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
				</label>
			</div>
		</div>
		
		<hr>


		<!-------- TEXTE --------->
		<div class="form-group">
			<div class="row">
				<div class="col-sm-12">
					<textarea name="editor1" id="editor1" rows="10" cols="80">
						<?php echo $news_item['text'] ?>
					</textarea>
					<script>
						// Replace the <textarea id="editor1"> with a CKEditor
						// instance, using default configuration.
						CKEDITOR.replace( 'editor1' );
					</script>
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