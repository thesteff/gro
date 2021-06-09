<!-- autoresize texarea !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/autosize.js"></script>
<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/validator.js"></script>


<script type="text/javascript">

	$(function() {
		
		/******** Bootstrap validator ********/
		$('#genFileForm form').validator();
		$('#genFileForm form').validator().on('submit', function (e) {
			
			if (e.isDefaultPrevented()) {
				// handle the invalid form...
			}
			else {
				// On bloque le comportement par défault du submit
				e.preventDefault();
				// Pas de problem avec le validator
				//update_repetition();
			}
		})
		
		// On initialise le autoresize
		//$('.autosize').autosize({append: "\n"});
		autosize($('.autosize'));
		

		// On fixe le comportement des select créés dynamiquement par le popup_upload/popup_generate
		$("#genFileForm").on("change", "select", function() {

			
			// On gère le accessType et le pdfType
			if ($(this).attr("id") == "accessType" || $(this).attr("id") == "pdfType") {

				// On rend la fonction dynamique
				$pre = $(this).attr("id").substr(0,$(this).attr("id").length-4)
				
				// offset
							$offset = $(this).attr("id") == "pdfType" ? '5' : '3';

				// On remove la select du accessId
				//$(this).parent().find("#"+$pre+"Id").parents(".form-group").next(".form-group");
				$(this).parents(".form-group").next("[id$='Group']").remove();
				
				//console.log($(this).parent().find("#"+$pre+"Id").parents(".form-group"));
				
				// On doit récupérer la list des access possibles					
				// On change le curseur
				document.body.style.cursor = 'progress';
				
				// Requète ajax au serveur
				$.post("<?php echo site_url('ajax/get_access_elem'); ?>",
				
					{'accessType': $(this).val()},
			
					function (msg) {
						
						// On rétablit le pointeur
						document.body.style.cursor = 'default';

						if (msg != "empty") {
							// On récupère les éléments "option"
							$list_item = JSON.parse(msg);
							$option = "";
							$.each($list_item, function(index, elem) {
								if (index == 0) $option += "<option value='"+elem.id+"' selected>"+elem.name+"</option>";
								else $option += "<option value='"+elem.id+"'>"+elem.name+"</option>";
							});
							
							// On créé le select
							var $new_select = '<div id="'+$pre+'Group" class="form-group">';
							$new_select += "<div class='col-sm-5 col-sm-offset-"+$offset+"'>";
							$new_select += '<select id="'+$pre+'Id" class="form-control">';
							$new_select += $option;
							$new_select += '</select>';
							$new_select += '</div></div>';
							$("#"+$pre+"Type").parents(".form-group").after($new_select);
						}
					}
				);
			}
			
			
			// On gère le fileType du popup_generate
			else if ($(this).attr("id") == "fileType") {
				
				// On retire le pdf_form si besoin
				if ($("#pdf_form") && $(this).val() != "pdf") $("#pdf_form").remove();

				// Si rien n'est selectionné, le boutton générer est désactivé
				if ($(this).val() == '') $("#genFileForm #submit").addClass("disabled");
				else {
					// On active le boutton générer
					$("#genFileForm #submit").removeClass("disabled");
										
					// On actualise le textFile
					$("#genFileForm #textFile").empty();
					if ($(this).val() == 'zip')
						$("#genFileForm #textFile").append("Zip rassemblant tous les morceaux de la jam au format mp3.");

					// On traite le cas pdf
					else if ($(this).val() == 'pdf') {
						$("#genFileForm #textFile").append("Pdf rassemblant toutes les partitions de la jam au format pdf avec un sommaire.");
						
						// On créé le select pour les media
						var $new_select = "<div id='pdf_form'>";
						
						$new_select += "<div class='form-group'>";
						$new_select += "<label for='pdfType' class='control-label col-sm-5'><small>Sélection de pdf pour chaque morceau</small></label>";
						$new_select += "<div class='col-sm-5'>";
						$new_select += '<select id="pdfType" class="form-control">';
							$new_select += "<option value='all'>Tous les documents</option>";
							$new_select += "<option value='cat'>Catégorie d'instrument</option>";
							$new_select += "<option value='instru'>Instrument</option>";
						$new_select += '</select></div>';
						$new_select += '</div>';
						
						// On créé le select pour le classement des titres du pdf
						$new_select += "<div id='alpha' class='form-group'>";
						$new_select += "<label for='alphaType' class='control-label col-sm-5'><small>Ordre des pdf</small></label>";
						$new_select += "<div class='col-sm-5'>";
						$new_select += '<select id="alphaType" class="form-control">';
							$new_select += "<option value='none'>Ordre de la playlist</option>";
							$new_select += "<option value='asc'>Ordre alphabétique</option>";
						$new_select += '</select></div>';
						$new_select += '</div>';
						
						$new_select += '</div>';

						$(this).parents(".form-group").after($new_select);
					}
				}
			}
			
		});
	});

	
	
	/****** GENERATE FILE  *******/
	function generate_file() {
		
		//console.log("********* generate_file");
		
		// On récupère l'id de la playlist
		$playlistId = $("#playlistBlock table").attr("playlistId");

		// On change le curseur
		$("body").addClass("wait");
		
		// On ferme la modal de génération de fichier
		$("#genFileModal").modal("hide");
		
		// On ouvre une modal de wait
		$("#modal_msg").modal({backdrop: 'static', keyboard: false });
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-header").html("En attente");
		$("#modal_msg .modal-body").html("Merci de bien vouloir patienter, l'opération peut prendre un certains temps...");
		$("#modal_msg .modal-footer").empty();
		$("#modal_msg").modal('show');
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_jam/generate_playlist_file",
		
			{
			'playlistId':$playlistId,
			'file_type':$("#genFileForm #fileType").val(),
			'sort':$("#genFileForm #alphaType").val() ? $("#genFileForm #alphaType").val() : 0,
			'fileName': $("#genFileForm [name=fileName]").val(),
			'parentId':'<?php echo $jam_item['id']; ?>',
			'accessType': $("#genFileForm #accessType").val(),
			'accessId': $("#genFileForm #accessId").val() ? $("#genFileForm #accessId").val() : 0,
			'pdfType': $("#genFileForm #pdfType").val() ? $("#genFileForm #pdfType").val() : 0,    // pour les selection de media
			'pdfId': $("#genFileForm #pdfId").val() ? $("#genFileForm #pdfId").val() : 0,
			'memberId': '<?php echo $member->id; ?>',
			'text': $("#genFileForm #textFile").val(),
			'admin': $("#genFileForm #admin").val() ? $("#genFileForm #admin").val() : 0
			},

			function (return_data) {
				
				console.log(return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				$("body").removeClass("wait");
				$("#modal_wait_msg").modal("hide");
				
				// Modal
				if ($obj['state'] == 1) {
					
					update_ressource_panel();
					
					$("#modal_msg .modal-dialog").addClass("success");
					$("#modal_msg .modal-dialog").removeClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Création de fichier réussie !");
					$("#modal_msg .modal-body").html("La création du fichier <b>"+$obj['data']['name']+"</b> a réussie !");
					$("#modal_msg .modal-footer").html("<button type='button' class='btn btn-default' data-dismiss='modal'>Fermer</button>");
				}
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html("<button type='button' class='btn btn-default' data-dismiss='modal'>Fermer</button>");
				}
				
				$("#modal_msg").modal("show");
			}
			
		);
		
	}
	
	
 </script>

 

<!-- Formulaire !-->
<div id="genFileForm" class="container-fluid">
	<form class="form-horizontal">
		
		<!-------- FILENAME --------->
		<div class="form-group required">
			<label for="fileName" class="control-label col-sm-3">Fichier</label>
			<div class="col-sm-9">
				<input id="lieu" class="form-control" type="text" name="fileName" value='<?php echo date("Y-m-d")."_".$playlist_item['infos']['slug']; ?>' autofocus required />
			</div>
		</div>
		
		<!-------- FILE TYPE --------->
		<div class="form-group required">
			<label for="fileType" class="control-label col-sm-3">Type</label>
			<div class="col-sm-5">
				<select id="fileType" class="form-control" name="fileType">
					<option disabled selected value> -- type de fichier -- </option>
					<option value='zip'>zip</option>
					<option value='pdf'>pdf</option>
				</select>
			</div>
		</div>
		
		<hr>
		
		<!-------- CATEGORY --------->
		<!--<div class="form-group required">
			<label for="instru_catId" class="control-label col-sm-3">Catégorie</label>
			<div class="col-sm-5">
				<select id="instru_catId" class="form-control" name="instru_catId">
					<option value="-1">générale</option>
					<?php if (isset($instru_cat)): ?>
						<?php foreach ($instru_cat as $cat): ?>
							<option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
						<?php endforeach ?>
					<?php endif ?>
				</select>
			</div>
		</div>!-->
		
		
		<!---------- ADMIN  -------->
		<div class="form-group">
			<label for="admin" class="control-label col-sm-3 adjust-xs">Administrateurs</label>
			<div class="checkbox col-sm-5 ">
				<label style="padding-left: 0px">
					<input id="admin" class="form-control" name="admin" type="checkbox" value="" />
					<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
				</label>
			</div>
		</div>
		
		
		<!-------- ACCESS TYPE --------->
		<div class="form-group">
			<label for="accessType" class="control-label col-sm-3">Accés/Visibilité</label>
			<div class="col-sm-5">
				<select id="accessType" class="form-control" name="accessType">
					<option value='tous'>Tous</option>
					<option value='cat'>Catégorie d'instrument</option>
					<option value='instru'>Instrument</option>
					<option value='tache'>Tâche</option>
				</select>
			</div>
		</div>
		
		
		<hr>
		
		<!-------- TEXTE --------->
		<div class="form-group">
			<label for="textFile" class="control-label col-sm-3">Description</label>
			<div class="col-sm-9">
				<textarea id="textFile" class="form-control autosize" name="textFile" placeholder="Descriptions du fichier" style="resize:none"></textarea>
			</div>
		</div>


		<hr>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			<button id="submit" type="button" class="btn btn-primary disabled" onclick="javascript:generate_file()">Générer</button>
		</div>

	</form>
</div>