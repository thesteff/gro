
<!-- flexdatalist pour les input !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.js"></script>
<link href="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.css" rel="stylesheet" type="text/css" />


<!-- Bootstrap-select !-->
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/bootstrap-select/bootstrap-select.min.css" />
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-select/bootstrap-select.min.js"></script>



<script type="text/javascript">

	$(function() {
		
		
		// **************** FLEXDATALIST ********************
		
		// On rempli le flexdatalist des titres
		$('#titreInput').flexdatalist({
			 minLength: 0,
			 selectionRequired: true,
			 data: [{ 'id':'-1', 'titre':'morceau non défini', 'compositeur':'', 'date':'' },
					<?php foreach ($list_morceaux as $morceau): ?>
						{ 'id':'<?php echo $morceau->morceauId ?>', 
							'titre':'<?php echo addslashes(htmlspecialchars($morceau->titre)) ?>', 
							'compositeur':'<?php echo addslashes(htmlspecialchars($morceau->artisteLabel)) ?>',
							'date':'<?php echo addslashes(htmlspecialchars($morceau->annee)) ?>'
							},
					<?php endforeach ?>
					],
			 searchIn: 'titre',
			 visibleProperties: ["titre","compositeur","date"],
			 searchByWord: true,
			 valueProperty: ['id','titre']	// on envoie ces valeurs quand on appelle la méthode val()
		});


		//$('#titreInput').flexdatalist('value',340);
		//$('#titreInput').flexdatalist('value',125);
		//morceauSelected();
		
		
		// ****** DYNAMIC MODAL ********
		$("[id$='Modal']").on("show.bs.modal", function(e) {
			var link = $(e.relatedTarget);
			$(this).find(".modal-body").load(link.attr("href"));
		});
		
		
		
		// **************** Titre
		// On fait un input via un select dans les results de flexdatalist (ou remise à "")
		$("#titreInput").on("change:flexdatalist", function( event, set ) {
			if (set.text == "") hide_all();
			else morceauSelected();
		});
		
		
		$("#titreInput").on("after:flexdatalist.search", function( event, key, data, items ) {
			// Par défaut on hide dès qu'on change le input
			hide_all();
		});		
		
		
		
		// **************** Version Block
		// Changement dans le version block
		$("#versionContent [id$='Input']").on("change", function( event ) {
			update_version();
		});



		// **************** Upload MP3
		$("#mp3Upload").on("change", function() {
			// On update la version
			update_version();
		});
		
		// **************** Update MP3 Input
		$("#mp3Block #updateBtn").on("click", function() {
			$("#mp3Block #mp3URLInput").prop("readonly", null)
		});
		
		
		// **************** Upload Media model
		$("#fileUploadInput").on("change", function() {
			
			// On récupère le nom de fichier
			$new_val = $(this).val().substring($(this).val().lastIndexOf("\\")+1,$(this).val().length);
			$parentDiv = $(this).closest(".panel-body").parent();
			
			console.log("fileUploadInput CHANGE : "+$new_val+"     "+$parentDiv.attr("id"));
		
			// Nouveau media => on upload
			if ($parentDiv.attr("id") == "divMediaModel") {
			
				// On récupère les infos du média	
				var formData = new FormData();
				formData.append('file', $('#divMediaModel #fileUploadInput')[0].files[0]);

				// On ajoute les infos la version
				formData.append("versionId", $version_selected);
				
				// On change le curseur
				document.body.style.cursor = 'progress';
				
				$.ajax({
				
					type: 'POST',
					url: "<?php echo site_url('ajax_version/create_media'); ?>",
					data: formData,
					contentType: false,
					processData: false, 
					cache: false,
					success: function(return_data) {
						
						// On change le curseur
						document.body.style.cursor = 'default';
						
						get_medias();
						
					},

					xhr: function() {
						var xhr = new window.XMLHttpRequest();
						//Upload progress
						xhr.upload.addEventListener("progress", function(evt){
							if (evt.lengthComputable) {
								var percentComplete = evt.loaded / evt.total;
								//Do something with upload progress
								//console.log(percentComplete);
							}
						}, false);
						//Download progress
						xhr.addEventListener("progress", function(evt){
							if (evt.lengthComputable) {
								var percentComplete = evt.loaded / evt.total;
								//Do something with download progress
								//console.log(percentComplete);
							}
						}, false);
						return xhr;
					}
					
				});
			}
		});
				
		
		// Data des versions
		$versions_data = [];
		$version_selected = "";
		$media_selected = "";
		
	});
	
	
	/**********************************************************************/
	/**********************************************************************/
	
	function hide_all() {
		
		// On désactive les bouton d'admin de morceau
		$("#morceauBlock #adminBtn button").each( function() {
			$(this).prop('disabled', true);
		});
		
		// On hide la div d'info
		$("#morceauInfos").fadeOut("fast");
		// On reset les blocks version
		$("#versionBar").empty();
		//$("#versionContent").empty();
		$("#versionBlock").css("display","none");
		// On reset le media block
		$("#mediaBlock").css("display","none");
		$("#media_menubar").css("display","none");
		
		$versions_data = [];
		$version_selected = "";
		$media_selected = "";
	}
	
	
	
	/*************************    MORCEAUX   ******************************/
	/**********************************************************************/	
	
	
	function morceauSelected() {
		// On récupère les infos du morceau
		$morceauObject = JSON.parse($("#titreInput").val());

		// On change le curseur
		document.body.style.cursor = 'progress';
		
		// On hide les infos du morceau
		$("#morceauInfos").css('display','none');
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('/ajax_morceau/get_morceau'); ?>",
		
			{
			'morceauId': $morceauObject['id']
			},
	
			function (return_data) {
				
				//console.log(return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Morceau FOUND !
				if ($obj['state'] == 1) {
					
					// On récupère les infos
					$data = JSON.parse(return_data);

					// On actualise l'affichage infos du morceau
					if ($obj['data'].artiste.id != -1) $("#morceauInfos #morceauInfosCompo").html($obj['data'].artiste.label);
					else $("#morceauInfos #morceauInfosCompo").html("");
					if ($obj['data'].morceau.annee != 0) $("#morceauInfos #morceauInfosDate").html($obj['data'].morceau.annee);
					else $("#morceauInfos #morceauInfosDate").html("");
					if ($obj['data'].artiste.id != -1 || $obj['data'].morceau.annee != 0) $("#morceauInfos").fadeIn("fast");
					
					// On active les btn d'admin
					$("#morceauBlock #adminBtn button").each( function() {
						$(this).prop('disabled', false);
					});
					
					// On update le updateBtn
					$("#morceauBlock #adminBtn #updateBtn").attr("href","<?php echo site_url('morceau/update/'); ?>"+$morceauObject['id']);

					// On reset le block version et on charge les versions
					$("#versionBar").empty();
					get_versions();
				}
				
				// Erreur : pas de morceau trouvé
				else {
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html("<button type='submit' class='btn btn-default' data-dismiss='modal'>Fermer</button>");
					$("#modal_msg").modal('show');
				}
			}
		);	
	}
	
	
	
	/********** DELETE MORCEAU ***************/
	function popup_delete_morceau() {
		
		// On récupère les infos de la playlist		
		$titreObject = JSON.parse($("#morceau #titreInput").val());
		
		$text = "Etes-vous sûr de vouloir supprimer le morceau \"<b>"+$titreObject['titre']+"</b>\" ?";
		$confirm = "<div class='modal-footer'>";
			$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
			$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_morceau("+$titreObject['id']+")'>Supprimer</button>";
		$confirm += "</div>";
		
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-dialog").addClass("backdrop","static");
		$("#modal_msg .modal-header").html("Supprimer le morceau");
		$("#modal_msg .modal-body").html($text);
		$("#modal_msg .modal-footer").html($confirm);
		$("#modal_msg").modal('show');
	}
	
	
	
	function delete_morceau($morceauId) {
		
		// On change le curseur
		document.body.style.cursor = 'progress';

		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_morceau/delete_morceau'); ?>",
		
			{
			'morceauId': $morceauId
			},
	
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					
					// On hide le panel Version
					$("#versionBar").css("display","none");
					$("#versionBlock").css("display","none");					
					
					$("#modal_msg .modal-dialog").removeClass("error");
					$("#modal_msg .modal-dialog").addClass("success");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Playlist supprimée !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html("<button type='button' class='btn btn-default' onclick='javascript:location.reload()'>Fermer</button>");
				}
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html("<button type='submit' class='btn btn-default' data-dismiss='modal'>Fermer</button>");
				}
				$("#modal_msg").modal('show');
			}
		);
	}
	
	
	/**********************************************************************/
	/*************************    VERSIONS   ******************************/
	/**********************************************************************/	
	
	// On récupère les versions en fonction du morceau selectionné
	function get_versions() {
		
		// On récupère les infos du morceau
		$morceauObject = JSON.parse($("#titreInput").val());
		
		// On change le curseur
		document.body.style.cursor = 'progress';

		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_version/get_versions'); ?>",
		
			{
			'morceauId': $morceauObject['id']
			},
	
			function (return_data) {
				
				console.log("get_versions : "+return_data);
				$obj = JSON.parse(return_data);
				$versions_data = $obj['data'];
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
				
					// On clean la versionBar
					$("#versionBar").empty();
				
					// On remplit la version bar en mettant selected le premier
					$.each($versions_data, function($index, $value) {
						active = "";
						// Si pas de version selected, on selectionne la première version
						if ($index == 0 && $version_selected == "") {
							$version_selected = $value.id;
							active = "active";
						}
						else if ($value.id == $version_selected) active = "active";
						
						// On ajoute l'item à la bar
						$("#versionBar").append("<li class='"+active+"' id='"+$value.id+"'><a href='#'>"+$value.collection+"</a></li>");
					});
					
					
					// On set le onclick sur les items de la version menubar
					$("#versionBar li a").on("click", function() {
						
						// Return si click sur la version selected
						if ($(this).parent().hasClass("active")) return;
						
						// On récupère l'index de l'item dans la menu bar
						else $index = $(this).parent().index();
						
						// On deselectionne tout
						$version_selected = "";
						$(this).parent().parent().children(".active").removeClass("active");

						// On selectionne l'item cliqué
						$(this).parent().addClass("active");
						$version_selected = $(this).parent().attr("id");
						
						// On populate le version block
						show_version($index);
						
						// On remplit le liste de media en fonction de version selected
						get_medias();

					});
					
					// On populate le version block
					show_version($("#versionBar li.active").index());
					
					// On peut créer des medias
					//$("#media_menubar").css("display","inline-flex");
					$("#media_menubar").css("display","block");
					
					// On remplit le liste de media en fonction de version selected
					get_medias();
				}
				// Erreur : pas de version trouvé
				else {
					console.log("get_versions : "+$obj['data']);
					
					// On n'affiche aucun version mais il faut pouvoir en créer une
					show_version(-1);
				}
			}
		);	
	}

	
	
	function update_version() {
		
		// Alert si aucun collectione n'a été identifié
		if ($("#collectionInput").val() == "") {
			console.log("Pour créer une version, vous devez d'abord indiquer un collection !");
			return;
		}
		
		// On créé l'objet qui sera envoyé au serveur	
		var formData = new FormData();
		
		// On récupère le fichier
		formData.append('file', $('#mp3Block input[type=file]')[0].files[0]);
		
		// On récupère les inputs et checkbox
		$("#versionContent [id$='Input']").each(function() {
			if ($(this).attr("type") == "checkbox") formData.append($(this).attr("id").replace("CbInput",""), $(this).prop("checked"));
			else formData.append($(this).attr("id").replace("Input",""), $(this).val());
		});
		
		// On ajoute les infos du morceau id (bd) + label (realpath) et de la version à updater
		formData.append("morceauId", JSON.parse($("#titreInput").val())['id']);
		formData.append("versionId", $version_selected);
		
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		$.ajax({
		
			type: 'POST',
			url: "<?php echo site_url(); ?>/ajax_version/update_version",
			data: formData,
			contentType: false,
			processData: false, 
			cache: false,
			success: function(return_data) {
				
				$obj = JSON.parse(return_data);
				$version = $obj['data'];
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					
					// On actualise le versions_data
					$index = $("#versionBar .active").index();
					$versions_data.splice($index,1,$version);
					
					// S'il y a eu envoi de fichier, on actualise l'ui
					if ($('#mp3Block input[type=file]').val() != '') {
						fakePath = $('#mp3Upload').val();
						$('#mp3URLInput').val(fakePath.substring(fakePath.lastIndexOf("\\")+1,fakePath.length));
						$("#mp3Block #deleteBtn").prop('disabled', false);
						$("#mp3Block #updateBtn").prop('disabled', false);
						// On clean l'input file
						clearInputFile($("#mp3Upload")[0]);
					}
					// Sinon il y a potentiellement eu changement de nom de fichier mp3
					else {
						$("#mp3Block #mp3URLInput").prop('readonly', true);
					}
						
				}
				else {
					console.log("Erreur : update_version");
					console.log(return_data);
				}
				
			},

			xhr: function() {
				var xhr = new window.XMLHttpRequest();
				//Upload progress
				xhr.upload.addEventListener("progress", function(evt){
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						//Do something with upload progress
						//console.log(percentComplete);
					}
				}, false);
				//Download progress
				xhr.addEventListener("progress", function(evt){
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						//Do something with download progress
						//console.log(percentComplete);
					}
				}, false);
				return xhr;
			}
			
		});
	
	}
	


	/********** DELETE VERSION ***************/
	function popup_delete_version() {
		
		// On récupère les infos du morceau et de la version	
		$titreObject = JSON.parse($("#morceau #titreInput").val());
		$versionId = $("#versionBar li.active").attr("id");
		$versionLabel = $("#versionBar li.active a").html();
		
		$text = "Etes-vous sûr de vouloir supprimer la version <b>\""+$versionLabel+"\"</b> du morceau \"<b>"+$titreObject['titre']+"</b>\" ainsi que tous les fichiers qui y sont associés ?";
		$confirm = "<div class='modal-footer'>";
			$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
			$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_version("+$versionId+")'>Supprimer</button>";
		$confirm += "</div>";
		
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-dialog").addClass("backdrop","static");
		$("#modal_msg .modal-header").html("Supprimer la version");
		$("#modal_msg .modal-body").html($text);
		$("#modal_msg .modal-footer").html($confirm);
		$("#modal_msg").modal('show');
	}
	
	
	
	function delete_version($versionId) {
		
		console.log("delete_version : "+$versionId);
		
		// On change le curseur
		document.body.style.cursor = 'progress';

		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_version/delete_version'); ?>",
		
			{
			'versionId': $versionId
			},
	
			function (return_data) {
				
				console.log(return_data)
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					
					// On hide le panel Version
					$("#versionBar").css("display","none");
					$("#versionBlock").css("display","none");
					
					$("#modal_msg .modal-dialog").removeClass("error");
					$("#modal_msg .modal-dialog").addClass("success");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Version supprimée !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html("<button type='button' class='btn btn-default' onclick='javascript:location.reload()'>Fermer</button>");
				}
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html("<button type='submit' class='btn btn-default' data-dismiss='modal'>Fermer</button>");
				}
				$("#modal_msg").modal('show');
			}
		);
	}
	
	
	
	/**********  MP3 ***************/
	function popup_upload_mp3() {
		
		// On Upload
		if ($("#mp3URLInput").val() == '') {
			browse("#mp3Upload");
		}
		
		else {
			$text = "Si vous envoyez un nouveau fichier, celui déjà présent sur le serveur sera supprimé.";
			$confirm = "<div class='modal-footer'>";
				$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
				$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:browse(\"#mp3Upload\")'>Continuer</button>";
			$confirm += "</div>";
			
			$("#modal_msg .modal-dialog").removeClass("error success");
			$("#modal_msg .modal-dialog").addClass("default");
			$("#modal_msg .modal-dialog").addClass("backdrop","static");
			$("#modal_msg .modal-header").html("Fichier déjà existant !");
			$("#modal_msg .modal-body").html($text);
			$("#modal_msg .modal-footer").html($confirm);
			$("#modal_msg").modal('show');
		}
	}
	
	
	function popup_delete_mp3() {
		
		// On récupère les infos du morceau et de la version	
		$titreObject = JSON.parse($("#morceau #titreInput").val());
		$fileName = $("#mp3URLInput").val();
		
		$text = "Etes-vous sûr de vouloir supprimer le fichier \"<b>"+$fileName+"</b>\" associé à cette version de \"<b>"+$titreObject['titre']+"</b>\" ?";
		$confirm = "<div class='modal-footer'>";
			$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
			$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_file(\""+$fileName+"\",\"mp3URL\")'>Supprimer MP3</button>";
		$confirm += "</div>";
		
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-dialog").addClass("backdrop","static");
		$("#modal_msg .modal-header").html("Supprimer le MP3");
		$("#modal_msg .modal-body").html($text);
		$("#modal_msg .modal-footer").html($confirm);
		$("#modal_msg").modal('show');
	}
	
	
	
	// §§§§§§§§§§§§§§§§§§§§§§§§§ SHOW_VERSION §§§§§§§§§§§§§§§§§§§§§§§§§§§§§§
	
	function show_version($index) {

		console.log("==========Show version : "+$index+" ==========");
		if ($index >= 0) {
			
			// On affiche le versionBlock avec les valeurs actualisées
			$("#versionContent #genreInput").selectpicker('val',$versions_data[$index].genre);
			$("#versionContent #tonaInput").selectpicker('val',$versions_data[$index].tona);
			$("#versionContent #modeInput").selectpicker('val',$versions_data[$index].mode);
			$("#versionContent #tempoInput").val($versions_data[$index].tempo);
			$("#versionContent #langInput").selectpicker('val',$versions_data[$index].langue);
			$("#versionContent #soufflantsCbInput").prop("checked", $versions_data[$index].soufflants == "1");
			$("#versionContent #choeursCbInput").prop("checked", $versions_data[$index].choeurs == "1");
			$("#versionContent #mp3URLInput").val($versions_data[$index].mp3URL);

			// On update l'UI
			$("#versionBlock #adminBtn #updateBtn").prop('disabled', false);
			$("#versionBlock #adminBtn #deleteBtn").prop('disabled', false);
			
			
			// UI mp3
			// Si on a un fichier en mémoire
			if ($("#mp3Block #mp3URLInput").val() != '') {
				$("#mp3Block #updateBtn").prop('disabled', false);
				$("#mp3Block #deleteBtn").prop('disabled', false);
			}
			else {
				// On clean l'input file
				$("#mp3Block #updateBtn").prop('disabled', true);
				$("#mp3Block #deleteBtn").prop('disabled', true);
			}
			$("#mp3Block #mp3URLInput").prop('readonly', true);
			
			// On show le version content
			$("#versionContent").css("display","block");

		}
		// Index = -1   => on ajoute une version
		else {
			// On hide le version content
			$("#versionContent").css("display","none");
			
			// On reset le versionBlock
			$("#versionBlock input[type='text']").val("");
			$("#versionBlock input[type='checkbox']").prop("checked",false);
			$("#versionBlock select").val(-1);

			// On update l'UI
			//$("#update_version").css("display","none");	$("#version_toolbar span.separator").css("display","none");
			//$("#add_version").css("display","block");
			$("#mp3URLX").css("display","none");
			
			// On desactive les btn d'admin
			$("#versionBlock #adminBtn #updateBtn").prop('disabled', true);
			$("#versionBlock #adminBtn #deleteBtn").prop('disabled', true);
		}
		
		// On affiche le block avec la toolbar
		$("#versionBlock").css("display","block");
	}
	
	
	/**********************************************************************/
	/*************************    MEDIAS     ******************************/
	/**********************************************************************/	
	
	
	// On récupère les medias en fonction de la version selectionnée
	function get_medias() {
		
		console.log("get_medias");
		
		// On change le curseur
		document.body.style.cursor = 'progress';

		// On reset la création de media
		$("#display_mediaBlock a").removeClass("active");
		$("#divMediaModel").css("display","none");
		$("#divMediaModel").find("#URLX").css("display","none");
		
		
		// On vide la liste des medias
		$("#mediaContent").children(":not(#divMediaModel):not(hr):not(#adminBtn)").remove();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_version/get_medias",
		
			{
			'versionId':$version_selected
			},
	
			function (return_data) {

				// On change le curseur
				document.body.style.cursor = 'default';

				console.log(return_data);
				
				// PAS DE VERSION, on ouvre le add_version et on select la tr morceau
				if (return_data == "medias_not_found") {
					$("#mediaBlock").css("display","none");
					return;
				}
				
				// VERSION RECUES
				$media_data = JSON.parse(return_data);
				
				// On display le media block si besoin
				if ($media_data.length > 0 && $("#mediaBlock").css("display") == "none") $("#mediaBlock").css("display","block");
				
				// On remplit le media block
				$.each($media_data, function($index, $value) {
					// On duplique le div du media model et on le populate
					$media = $("#divMediaModel").clone();
					$media.appendTo("#mediaContent");
					$media.attr("id","divMedia-"+$value.mediaId);
					$media.find("#URLInput").val($media_data[$index].URL);
					$media.find("#transpoInput").val($media_data[$index].transpo);
					$media.find("#catInput").val($media_data[$index].catId);
					$media.find("#catInput").prop("disabled",true);
					$media.find("#instruInput").val($media_data[$index].instruId);
					$media.find("#instruInput").prop("disabled",true);
					
					// Action sur le update
					$media.find("#updateBtn").on("click", function() {
						$(this).parent().parent().find("#URLInput").prop("readonly", null);
					});
					
					// Fichier renommé
					$media.find("#URLInput").on("change", function() {
						$media_selected = $value.mediaId;
						update_media($value.mediaId);
					});
					
					// Bouton supprimer
					$media.find("#deleteBtn").on("click", function() {
						$media_selected = $value.mediaId;
						popup_delete_media($media_selected);
					});
	
					$media.css("display","block");
					console.log("DIVMEDIA : "+$media.find("#URLInput").val());
					
					// On reset le media model
					$("#divMediaModel input[type='text']").val('');
				});
			}
		);	
	}
	
	
	
	
	function update_media($mediaId) {
		
		console.log("****** update_media mediaId : "+$mediaId);
		
		// On récupère la div
		$divMedia = $("#mediaBlock").find("[id='divMedia-"+$mediaId+"']");


		// On récupère les infos du media
		var formData = new FormData();
		formData.append("versionId",$version_selected);	// pour le realPath
		formData.append("mediaId",$mediaId);
		
		$divMedia.find('input[type=text]').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).val());
		});

		/*$divMedia.find('select').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).children(":selected").val());
		});*/
		
		
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		$.ajax({
		
			type: 'POST',
			url: "<?php echo site_url(); ?>/ajax_version/update_media",
			data: formData,
			contentType: false,
			processData: false, 
			cache: false,
			success: function(return_data) {
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// On récupère la version insérée (objet complet + insert_id)
				console.log("============MSG============");
				console.log(return_data);
				$media_data = JSON.parse(return_data);
				
				// On actualise l'IHM
				$divMedia.find("#URLInput").prop("readonly",true);
				/*$media.find("#transpoInput").val($media_data.transpo);
				$media.find("#catInput").val($media_data.catId);
				$media.find("#instruInput").val($media_data.instruId);*/
				
				console.log("DIVMEDIA : "+$media.find("#URLInput").val());
				
			},

			xhr: function() {
				var xhr = new window.XMLHttpRequest();
				//Upload progress
				xhr.upload.addEventListener("progress", function(evt){
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						//Do something with upload progress
						//console.log(percentComplete);
					}
				}, false);
				//Download progress
				xhr.addEventListener("progress", function(evt){
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						//Do something with download progress
						//console.log(percentComplete);
					}
				}, false);
				return xhr;
			}
			
		});
	}
	
	
	function popup_delete_media($mediaId) {
		
		// On récupère les infos du morceau et de la version	
		$titreObject = JSON.parse($("#morceau #titreInput").val());
		$fileName = $("#mediaContent div[id^='divMedia'][id$='"+$mediaId+"'] #URLInput").val();
		
		$text = "Etes-vous sûr de vouloir supprimer le fichier \"<b>"+$fileName+"</b>\" associé à cette version de \"<b>"+$titreObject['titre']+"</b>\" ?";
		$confirm = "<div class='modal-footer'>";
			$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
			$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_file(\""+$fileName+"\",\"URL\")'>Supprimer fichier</button>";
		$confirm += "</div>";
		
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-dialog").addClass("backdrop","static");
		$("#modal_msg .modal-header").html("Supprimer le fichier");
		$("#modal_msg .modal-body").html($text);
		$("#modal_msg .modal-footer").html($confirm);
		$("#modal_msg").modal('show');
	}
	

	/**********************************************************************/
	/*************************    GLOBAL     ******************************/
	/**********************************************************************/	
	
	function browse(target) {
		$("#modal_msg").modal('hide');
		$(target).trigger('click');
	}
	
	
	function delete_file($filename, $target) {

		console.log("****** DELETE_FILE : "+$filename+" => "+$target);
		
		// On récupère les infos du morceau
		$morceauObject = JSON.parse($("#titreInput").val());

		// On change le curseur
		document.body.style.cursor = 'progress';

		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_version/delete_version_file",
		
			{
			'morceauId': $morceauObject['id'],
			'versionId': $version_selected,
			'filename': $filename
			},
	
			function (return_data) {
				
				console.log(return_data)
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// On hide la modal
				$("#modal_msg").modal('hide');
				
				// Modal
				if ($obj['state'] == 1) {
					
					// MP3 REMOVE
					if ($target == "mp3URL") {
						// On reset le inputfile
						$("#mp3URLInput").val('');
						clearInputFile($("#mp3Upload")[0]);
						// On update l'UI
						$("#mp3Group #deleteBtn").prop('disabled', true);
						$("#mp3Group #updateBtn").prop('disabled', true);
						// On update versions_data
						$versions_data[$("#versionBar .active").index()].mp3URL = '';
					}
					// MEDIA REMOVE
					else {
						console.log("MEDIA REMOVE : "+$media_selected);
						// On remove la div du media
						$("#mediaContent").find("div[id^='divMedia'][id$='"+$media_selected+"']").fadeOut("fast");
					}
				}
				// On veut supprimer un fichier qui n'existe pas => on remet juste un reset au input
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html("<button type='submit' class='btn btn-default' data-dismiss='modal'>Fermer</button>");
					$("#modal_msg").modal('show');
				}
			}
		);
	}


	function clearInputFile(f) {
		console.log("======== CLEARINPUTFILE  ========");
		console.log(f.value);
		if(f.value){
			try {
				f.value = ''; //for IE11, latest Chrome/Firefox/Opera...
			}catch(err){ }
			if(f.value){ //for IE5 ~ IE10
				var form = document.createElement('form'),
					parentNode = f.parentNode, ref = f.nextSibling;
				form.appendChild(f);
				form.reset();
				parentNode.insertBefore(f,ref);
			}
		}
	}	
	
	
 </script>


<!-------- SECTION ADMIN --------->
<div id="mediathequeBlock" class="panel panel-default row">
	
	
	<!-- Header !-->
	<div class="row panel-heading panel-bright title_box">
		<h4>
			<?php echo $page_title; ?>
			<!-- AJOUTER MORCEAU -->
			<button class="pull-right btn btn-default btn-xs" href="<?php echo site_url("morceau/create"); ?>" data-remote="false" data-toggle="modal" data-target="#createMorceauModal" title="Ajouter morceau"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;Ajouter morceau</button>
		</h4>
	</div>


	<!--*********************** PANEL GLOBAL ********************-->	
	<div id="morceau" class="row">
		<div class="panel-body col-lg-12">
		
			<!--*********************** MORCEAU BLOCK ********************-->	
			<div id="morceauBlock" class="row">
					
				<!-------- TITRE --------->
				<label for="titreInput" class="control-label"></label>
				<div class="col-sm-6">
					<input id="titreInput" class="form-control" type="text" name="titre" />
				</div>
				
				
				<!-- BOUTTONS UPDATE ET SUPPR -->
				<div id="adminBtn" class="col-sm-1 btn-group btn-group-xs" style="display:flex">
					<!-- MODIFIER -->
					<button id="updateBtn" class="form-control btn btn-default" data-remote="false" data-toggle="modal" data-target="#updateMorceauModal" title="Modifier morceau" disabled><i class="glyphicon glyphicon-pencil"></i></button>
					<!-- SUPPRIMER -->
					<button class="form-control btn btn-default" onclick="javascript:popup_delete_morceau()" title="Supprimer morceau" disabled><i class="glyphicon glyphicon-trash"></i></button>
				</div>
				
					
				<div id="morceauInfos" class="col-sm-8" style="display:none">	
					<h5><span id="morceauInfosCompo"></span>&nbsp;&nbsp;&nbsp;<small id="morceauInfosDate"></small></h5>
				</div>
			</div>
			
			
			<!-- ******************  VERSION BLOCK  ******************* !-->
			<div id="versionBlock" class="col-sm-12" style="display:none">
			
				<hr class="dark nomargin">

				<!-- BOUTTONS ADMIN -->
				<div id="adminBtn" class="pull-right btn-group btn-group-xs">
					<!-- Modifier -->
					<button id="updateBtn" class="btn btn-default" data-remote="false" data-toggle="modal" data-target="#updateVersionModal" title="Modifier version"><i class="glyphicon glyphicon-pencil"></i></button>
					<!-- Supprimer -->
					<button id="deleteBtn" class="btn btn-default" onclick="javascript:popup_delete_version()" title="Supprimer version" disabled><i class="glyphicon glyphicon-trash"></i></button>
					<!-- Ajouter -->
					<button class="btn btn-default" href="<?php echo site_url("morceau/create_version"); ?>" data-remote="false" data-toggle="modal" data-target="#createVersionModal" title="Ajouter version"><i class="glyphicon glyphicon-plus"></i></button>
				</div>
				
				
			
				<!------ VERSION BAR ------>
				<ul id="versionBar" class="nav nav-pills">
				</ul>
			
				<!------ VERSION CONTENT ------>
				<div id="versionContent" class="row col-sm-12 panel-default">
				<div class="panel-body">
	
					<div class="form-horizontal">
					
					
						<!-- ROW 1 !-->
						<div class="form-group">
						
							<!-- Genre -->
							<label for="genreInput" class="control-label col-sm-2 soften">Genre</label>
							<div class="col-sm-3">
								<select id="genreInput" class="form-control selectpicker" name="genreInput" data-style="btn-default">
									<?php foreach ($list_style as $style): ?>
										<option value="<?php echo ucfirst($style->id); ?>"><?php echo ucfirst(htmlentities($style->label)); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						
							<!-- Tona -->
							<label for="tonaInput" class="control-label col-sm-2 soften">Tonalité</label>
							<div class="col-sm-1">
								<select id="tonaInput" class="form-control selectpicker" name="genreInput" data-style="btn-default">
									<?php foreach ($list_tona as $tona): ?>
										<option value="<?php echo ucfirst($tona->id); ?>"><?php echo ucfirst(htmlentities($tona->label)); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						
						
							<!-- Mode -->
							<label for="modeInput" class="sr-only">Mode</label>
							<div class="col-sm-2">
								<select id="modeInput" class="form-control selectpicker" name="genreInput" data-style="btn-default">
									<?php foreach ($list_mode as $mode): ?>
										<option value="<?php echo $mode->id; ?>"><?php echo htmlentities($mode->label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						
						</div>
						
						<!-- ROW 2 !-->
						<div class="form-group">
						
							<!-- Tempo -->
							<label for="tempoInput" class="control-label col-sm-2 soften">Tempo</label>
							<div class="col-sm-2 col-sm-offset-1">
								<input id="tempoInput" class="form-control" type="text" name="tempoInput">
							</div>
							

							<!-- Langue -->
							<label for="langInput" class="control-label col-sm-2 soften">Langue</label>
							<div class="col-sm-3">
								<select id="langInput" class="form-control selectpicker" name="langInput" data-style="btn-default">
									<?php foreach ($list_lang as $lang): ?>
										<option value="<?php echo $lang->id; ?>"><?php echo htmlentities($lang->label); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							
						</div>
						
						<!-- ROW 3 !-->
						<div class="form-group">
						
							<!-- Choeurs -->
							<label for="choeursCbInput" class="control-label col-sm-1 col-sm-offset-6"><img style="vertical-align: text-bottom;" width="16px" src="/images/icons/heart.png" alt="choeurs" title="choeurs"></label>
							<div class="checkbox col-sm-1">
								<label>
									<input id="choeursCbInput" type="checkbox" value="">
									<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
								</label>
							</div>
							
							<!-- Soufflants -->
							<label for="soufflantsCbInput" class="control-label col-sm-1"><img style="vertical-align: text-bottom;" width="16px" src="/images/icons/tp.png" alt="soufflants" title="soufflants"></label>
							<div class="checkbox col-sm-1">
								<label>
									<input id="soufflantsCbInput" type="checkbox" value="">
									<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
								</label>
							</div>
							
						</div>
						
						<hr class="soft">
					
						<!-- ROW 4 !-->
						<div id="mp3Block" class="form-group">
						
							<label for="mp3URLInput" class="control-label col-sm-1 col-sm-offset-2 soften">MP3</label>
							<div class="input-group col-sm-5">
							
								<!-- MP3 URL -->
								<input id="mp3URLInput" class="form-control" type="text" name="mp3URLInput" readonly>
							
								<!-- Admin Btn -->
								<div id="mp3Group" class="input-group-btn">									
									<!-- Modifier -->
									<button id="updateBtn" class="btn btn-default" title="Modifier MP3"><i class="glyphicon glyphicon-pencil"></i></button>
									<!-- Supprimer -->
									<button id="deleteBtn" class="btn btn-default" onclick="javascript:popup_delete_mp3()" title="Supprimer mp3" disabled><i class="glyphicon glyphicon-trash"></i></button>
									<!-- Upload -->
									<button class="fileUpload btn btn-default btn-primary" onclick="javascript:popup_upload_mp3()" title="Envoyer MP3"><i class="glyphicon glyphicon-upload"></i></button>

								</div>
								
							</div>
							<input id="mp3Upload" type="file" name="file" accept=".mp3" style="display:none" />
							
						</div>
						
					</div>

				</div>
				</div>				
				
			</div>

			
			<!-- ******************  MEDIA DIV  ******************* !-->
			<div id="mediaBlock" class="col-sm-12" style="display:none">
			
				<hr class="dark nomargin">
				
				<!------ MEDIA CONTENT ------>
				<div id="mediaContent">
				
					<div id="divMediaModel" class="row panel-default" style="display:none;">
					<div class="panel-body">
					
						<div class="form-horizontal">
						
							<div class="form-group">								
								
								<!-- MEDIA Upload -->
								<label for="URLInput" class="control-label col-sm-1 soften"><i class="glyphicon glyphicon-file" style="font-size:20px;"></i></label>
								<div class="col-sm-4">
									<div class="input-group">
									
										<!-- FILE URL -->
										<input id="URLInput" class="form-control" type="text" name="URLInput" readonly>
									
										<!-- Admin Btn -->
										<div id="fileGroup" class="input-group-btn">									
											<!-- Modifier -->
											<button id="updateBtn" class="btn btn-default" data-remote="false" data-toggle="modal" data-target="#updateFileModal" title="Modifier fichier"><i class="glyphicon glyphicon-pencil"></i></button>
											<!-- Supprimer -->
											<button id="deleteBtn" class="btn btn-default" title="Supprimer fichier"><i class="glyphicon glyphicon-trash"></i></button>
										</div>
									</div>	
								</div>
								<input id="fileUploadInput" type="file" name="file" style="display:none" />
								
								
								<!-- Transpo -->
								<label for="transpoInput" class="control-label col-sm-1 soften"><small>Type</small></label>
								<div class="col-sm-1">
									<select id="transpoInput" class="form-control selectpicker">
										<?php foreach ($list_transpo as $transpo): ?>
											<option value="<?php echo $transpo->id; ?>"><?php echo ucfirst(htmlentities($transpo->label)); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								
								<!-- Cat -->
								<label for="catInput" class="control-label col-sm-1 soften"><small>Cat.</small></label>
								<div class="col-sm-1">
									<select id="catInput" class="form-control selectpicker">
										<?php foreach ($list_cat as $cat): ?>
											<option value="<?php echo $cat['id']; ?>"  <?php if ($cat['id'] == "-1") echo "selected"; ?>><?php echo htmlentities($cat['name']); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								
								<!-- Instru -->
								<label for="instruInput" class="control-label col-sm-1 soften"><small>Instru.</small></label>
								<div class="col-sm-2">
									<select id="instruInput" class="form-control selectpicker">
										<?php foreach ($list_instru as $instru): ?>
											<option value="<?php echo $instru['id']; ?>"><?php echo htmlentities($instru['name']); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								
							</div>
							
						</div>
						
					</div>
					</div>
					
				</div>
				
			</div>
			
			<!------ MEDIA ADMIN ------>
			<div id="mediaAdmin" class="col-sm-12" >
			
				<hr class="dark nomargin">
				<!-- BOUTTONS ADMIN -->
				<div id="adminBtn" class="pull-right btn-group btn-group-xs">
					<!-- Upload -->
					<button class="fileUpload btn btn-default btn-primary" onclick="javascript:browse('#divMediaModel #fileUploadInput')" title="Ajouter media"><i class="glyphicon glyphicon-upload"></i></button>
				</div>
			
			</div>
			
		</div>
	</div>	
	
</div>


<!-- ******** MODAL CREATE MORCEAU ******* !-->
<div id="createMorceauModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		<div class="modal-header lead">Ajouter un morceau</div>
		<div class="modal-body">
		...
		</div>
	</div>
	</div>
</div>

<!-- ******** MODAL UPDATE MORCEAU ******* !-->
<div id="updateMorceauModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		<div class="modal-header lead">Modifier un morceau</div>
		<div class="modal-body">
		...
		</div>
	</div>
	</div>
</div>

<!-- ******** MODAL CREATE VERSION ******* !-->
<div id="createVersionModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		<div class="modal-header lead">Ajouter une version</div>
		<div class="modal-body">
		...
		</div>
	</div>
	</div>
</div>