<!-- Editeur html -->
<!--<script src="<?php echo base_url();?>ressources/ckeditor/ckeditor.js"></script>!-->

<!-- Sortable JS !-->
<!--<script type="text/javascript" src="<?php echo base_url();?>ressources/script/sortable.min.js"></script>!-->


<script type="text/javascript">

	$(function() {
		
		// On remplit la choice_list en fonction de la hidden_select list (remplit par le php en cas d'update)
		$("#hidden_select option").each(function() {
			add_list_elem($(this).attr("versionId"), get_songTitle($(this).attr("versionId")), $(this).attr("idInstru"), get_instruName($(this).attr("idInstru")), false);
		});

	
		// On fixe le comportement des checkboxes
		$("input:checkbox").each(function() {	
		
			$(this).click(function() {
				
				// On récupère les infos du checkbox cliqué
				versionId = $(this).closest("tr").attr("versionId");
				nameSong = get_songTitle(versionId);
				idInstru = $(this).attr("idInstru");
				labelInstru = get_instruName(idInstru);
				
				
				// Comportement d'un checkbox
				if (this.checked) add_list_elem(versionId, nameSong, idInstru, labelInstru, true);	
				
				// On retire la song de la choice_list
				else {
					elemHTML = $("#choice_list div");
					//alert($("#choice_list div").html());
					elemHTML.each(function(index) {
						if ($(this).attr('versionId') == versionId && $(this).attr('idInstru') == idInstru) $(this).remove();
					});
					// On retire l'élément du formulaire caché
					$("#hidden_select [versionId='"+versionId+"'][idInstru='"+idInstru+"']").remove();

				}
			});		
		});
		
		
		// On définit les Hover Btn
		$(".clickableBtn").each(function() {
			$(this).css("filter", "grayscale(100%)");
			$(this).hover(function(){
				$(this).css("filter", "grayscale(0%)");
				}, function(){
				$(this).css("filter", "grayscale(100%)");
			});
		});
		
		
		// On fixe le comportement de l'upload
		$("#uploadInput").on("change", function() {
			// On récupère le nom de fichier
			$new_val = $(this).val().substring($(this).val().lastIndexOf("\\")+1,$(this).val().length);
			popup_upload($new_val);
		});
		
		
		// On fixe le comportement des select créés dynamiquement par le popup_upload/popup_generate
		$("body").on("change", "select", function() {
			
			// On gère le accessType et le pdfType
			if ($(this).attr("id") == "accessType" || $(this).attr("id") == "pdfType") {

				// On rend la fonction dynamique
				$pre = $(this).attr("id").substr(0,$(this).attr("id").length-4)

				// On remove la select du accessId
				$(this).parent().find("#"+$pre+"Id").remove();
				
				// On doit récupérer la list des access possibles					
				// On change le curseur
				document.body.style.cursor = 'progress';
				
				// Requète ajax au serveur
				$.post("<?php echo site_url(); ?>/ajax/get_access_elem",
				
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
							$new_select = '<select id="'+$pre+'Id">';
							$new_select += $option;
							$new_select += '</select>';
							$("#"+$pre+"Type").parent().append($new_select);
						}
					}
				);
			}
			
			
			// On gère le fileType du popup_generate
			else if ($(this).attr("id") == "fileType") {
				
				// On retire le pdf_form si besoin
				if ($("#pdf_form") && $(this).val() != "pdf") $("#pdf_form").remove();

				// Si rien n'est selectionné, le boutton générer est désactivé
				if ($(this).val() == '') $("#upload_block").find("[type=button]").prop('disabled', true);
				else {
					// On active le boutton générer
					$("#upload_block [type=button]").prop('disabled', false);
										
					// On actualise le textFile
					$("#upload_block #textFile").empty();
					if ($(this).val() == 'zip')
						$("#upload_block #textFile").append("Zip rassemblant tous les morceaux de la jam au format mp3");

					// On traite le cas pdf
					else if ($(this).val() == 'pdf') {
						$("#upload_block #textFile").append("Pdf rassemblant toutes les partitions de la jam au format pdf avec un sommaire");
						
						// On créé le select pour les media
						$new_select = "<p id='pdf_form'><label for='pdfType'><small>Sélection de pdf pour chaque morceau</small></label><br>";
						$new_select += '<select id="pdfType" style="margin-right:18px">';
							$new_select += "<option value='all'>Tous les documents</option>";
							$new_select += "<option value='cat'>Catégorie d'instrument</option>";
							$new_select += "<option value='instru'>Instrument</option>";
						$new_select += '</select>';
						$new_select += '</p>';
						
						// On créé le select pour le classement des titres du pdf
						$new_select += "<p id='alpha'><label for='alphaType'><small>Ordre des pdf</small></label><br>";
						$new_select += '<select id="alphaType" style="margin-right:18px">';
							$new_select += "<option value='none'>Ordre de la playlist</option>";
							$new_select += "<option value='asc'>Ordre alphabétique</option>";
						$new_select += '</select>';
						$new_select += '</p>';

						$("#upload_form").append($new_select);
					}
				}
				
				// On actualise la height de la popup
				$("#confirm").css("height",$("#confirm .tcontent").innerHeight());
				$("#confirm").resize();
			}
			
		});


		// On actualie l'affichage du block d'admin doc
		update_doc_section();
		
		
		////////////////// Aspect du tableau   ///////////////////////:
		
		$("#inscrTab").children().addClass( "dark_bg" );
		$("#inscrTab tbody :first-child").addClass( "dark_bg" );

		
		// On ferme les catégories qui ne concernent pas l'utilisateur en parcourant tous les header
		$(".cat_header").each(function(index) {
			if ($(this).text().trim() != $('#instru_cat1').text() && $(this).text().trim() != $('#instru_cat2').text()) {
				display_cat($(this).text().trim());
			}
		});
		
		// On vide les champs de saisie sur un reload
		resetForms();
		$('#select_repet').val("-1");
		
		// On remplit le CKEditor
		CKEDITOR.instances.editor1.setData($("#text_tab").html());
		
		// On active les handlers pour le player
		song_update();
		
	});
	
	
	// Permet de fixer le comportement des titre de morceaux
	function song_update() {
		$(".is_playable tbody tr[versionId!='-1']").on("click", function() {
			// On déselectionne la tr précédente
			$(this).closest("tbody").find(".selected").removeClass("selected");
			// La tr devient selected
			$(this).addClass("selected");
			update_player($(this).attr("morceauId"), $(this).attr("versionId"));
		});
		
		// On surcharge le css pour les pause
		$(".is_playable tbody tr[versionId='-1'] > td").css("background-color","#dddddd");		
	}
	
	
	function resetForms() {
		for (i = 0; i < document.forms.length; i++) {
			document.forms[i].reset();
		}
	}
	
		
	/* *************** Gestion des ajouts/suppressions de list_elem *************/
	/* **************************************************************************/
	function add_list_elem(versionId, nameSong, idInstru, labelInstru, to_hidden) {
		// On créé la div insérée dans la choice_list
		/*elemHTML = "<div versionId='"+versionId+"' idInstru='"+idInstru+"'>";
		//elemHTML += "<span class='song_title'>"+nameSong+"</span>";
		elemHTML += nameSong;
		elemHTML += "<span class='note'> - "+labelInstru+"</span>";
		elemHTML += "<br></div>";
		
		// On insère la div dans la choice list
		$("#choice_list").append(elemHTML);
		
		// On récupère l'objet jQuery pour lui ajouter la gestion des mouseEvents
		obj = $("#choice_list div:last")
		// Gestion des event sur la nouvelle tr
		obj.on("click", function() {
			// On déselectionne la tr précédente
			$("#choice_list").find(".selected").removeClass("selected");
			// La tr devient selected
			$(this).addClass("selected");
		});	*/
		
		// On créé la tr insérée dans la choice_list		
		elemHTML = "<tr><td versionId='"+versionId+"' idInstru='"+idInstru+"'>";
		//elemHTML += "<span class='song_title'>"+nameSong+"</span>";
		elemHTML += nameSong;
		elemHTML += "<span class='note'> - "+labelInstru+"</span>";
		elemHTML += "</td></tr>";
		
		// On insère la div dans la choice list
		$("#choice_list").append(elemHTML);
		
		// On ajoute l'élément au formulaire caché
		if (to_hidden) $("#hidden_select").append("<option versionId='"+versionId+"' idInstru='"+idInstru+"' selected>"+versionId+" - "+idInstru+"</option>");
	}

	
	
	/* ****************** Gestion des actions to top et bottom ******************/
	/* **************************************************************************/
	function move_elem(action) {
	
		if (action == "to_top") {
			selected_elem = $("#choice_list > .selected");
			var selected_index = 0;
			
			// S'il n'y a pas d'élément selectionné, on sort
			if (! selected_elem.length > 0) return;
			
			// On traite la liste affichée
			$list = $("#choice_list").children();
			$list.each(function(index) {
				if ($(this).hasClass('selected')) {
					// Si l'élément selectionné est le premier, on sort de la boucle
					if (index == 0) return;
					selected_elem.insertBefore($("#choice_list > div:eq("+(index-1)+")"));
					selected_index = index;
					return;
				}
			});
	
			// Si l'élément selectionné est le premier, on sort de la fonction
			if (selected_index == 0) return;
			
			// On traite la liste cachée
			hidden_elem = $("#hidden_select > option:eq("+selected_index+")");
			hidden_elem.insertBefore($("#hidden_select > option:eq("+(selected_index-1)+")"));
		}
		
		
		else if (action == "to_bottom") {
			selected_elem = $("#choice_list > .selected");

			// S'il n'y a pas d'élément selectionné à gauche, on sort
			if (! selected_elem.length > 0) return;
			
			// On traite la liste affichée
			$list = $("#choice_list").children();
			$list.each(function(index) {
				if ($(this).hasClass('selected')) {
					selected_elem.insertAfter($("#choice_list > div:eq("+(index+1)+")"));
					selected_index = index;
					return;
				}
			});
			
			// On traite la liste cachée
			hidden_elem = $("#hidden_select > option:eq("+selected_index+")");
			hidden_elem.insertAfter($("#hidden_select > option:eq("+(selected_index+1)+")"));
		}
		
	}	
	
	/* ****************** Gestion des actions du tableau ************************/
	/* **************************************************************************/
	
	// Permet d'afficher et de masquer une catégorie d'instrument (et ses colonnes)
	function display_cat(name) {
	
		is_visible = $("#inscrTab .catelem_"+name).css('display');
		if (is_visible == "table-cell") {
			$("#inscrTab #cat_"+name).addClass("hidden_cell");
			$("#inscrTab .catelem_"+name).css('display','none');
			$("#inscrTab .hidden_"+name).css("display",'table-cell');
			$("#inscrTab tr:nth-child(1) > th[id='cat_"+name+"']").attr("colspan",'1');
		}
		else {
			$("#inscrTab #cat_"+name).removeClass("hidden_cell");
			$("#inscrTab .catelem_"+name).css('display',"table-cell");
			$("#inscrTab .hidden_"+name).css("display",'none');
			nb_cat = 0;
			$("#inscrTab tr:nth-child(2) > th").each(function() {
				//alert($(this).hasClass("catelem_"+name));
				if ($(this).hasClass("catelem_"+name)) nb_cat++;
			});
			//alert(nb_cat);
			//alert($("#inscrTab tr:nth-child(2) > th.catelem_"+name).html());
			//var colCount = $("#inscrTab tr:nth-child(1) > th[id='cat_"+name+"']").length();
			$("#inscrTab tr:nth-child(1) > th[id='cat_"+name+"']").attr("colspan",nb_cat);
		}
    }
	
	// Permet de retrouver un titre de morceau à partir de l'versionId
	function get_songTitle(versionId) {
		return $("#inscrTab tbody tr[versionId='"+versionId+"'] .song").html();
	}
	
	// Permet de retrouver un nom d'instrument à partir de l'idInstru
	function get_instruName(idInstru) {
		$instruName = "";
		$thInstru = $("#inscrTab tr:nth-child(2)");//.children(":nth-child(2)");
		$thInstru.children().each(function(index) {
			if (index > 1 && $(this).attr("idInstru") == idInstru)
				$instruName = $(this).html();
		});
		return $instruName;
	}
	
	
	/* ******************  Gestion des sections admin    ************************/
	/* **************************************************************************/
	
	// On gère l'ouverture et fermeture de section
	function trig_admin_section($target) {
		$("#admin_"+$target+"_item").hasClass("active") ? $("#admin_"+$target+"_item").removeClass("active") : $("#admin_"+$target+"_item").addClass("active");
		$("#admin_"+$target+"_section").css("display") == "block" ?	$("#admin_"+$target+"_section").css("display", "none") : $("#admin_"+$target+"_section").css("display", "block");
	}
	
	
	// Update du texte d'intro
	function update_text_tab() {
		//alert(CKEDITOR.instances.editor1.getData());
		
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/update_text_tab",
		
			{
			'slugJam':'<?php echo $jam_item['slug'] ?>',
			'text_tab':CKEDITOR.instances.editor1.getData(),
			},
	
			function (msg) {
				// Le lieu spécifié n'est pas présent dans la base
				if (msg == "success") {
					TINY.box.show({html:"Le texte d'information a été mis à jour.",boxid:'success',animate:false,width:650, closejs:function(){location.reload();}});
				}
			}
		);
		
	}
	
	
	// ************** DOCUMENTS *********************/
	
	// update la doc_list
	function update_doc_section() {

		$("#doc_list").empty();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/get_parent_files",
		
			{
			'parentType':'event',
			'parentId':'<?php echo $jam_item['id']; ?>'
			},
		
			function (msg) {
				if (msg != 'empty') {
					$file_list = JSON.parse(msg);
					$.each($file_list, function(index) {
						add_file($file_list[index]);
					});
				}
			}
		);	
	}
	
	
	// Ajoute un div de fichier à la section doc_list
	function add_file($file_item) {

	$new_div = "<div file_id='"+$file_item.id+"' class='small_message'>";
			$new_div += "<h4>"+$file_item.label+"</h4>";
			if (<?php echo $is_admin == true ? 'true' : 'false' ?>) {
				$new_div += "<h5 class='numbers'>"+$file_item.memberName+"</h5>";
			}
			$new_div += "<div>";
				if ($file_item.text.length > 0) $new_div += "<p>"+$file_item.text+" : </p>";
				$new_div += "<p><b><a class='numbers' href='<?php echo $event_path; ?>/"+$file_item.fileName+"' target='_blanck'>"+$file_item.fileName+"</a></b>";
				if (<?php echo $is_admin == true ? 'true' : 'false' ?>) {
					$new_div += "&nbsp;<img id='fileRemoveBtn' class='clickableBtn' style=\"height: 8px; display:inline; filter:opacity(50%)\" src=\"/images/icons/x.png\" title=\"effacer fichier\">";
				}
				$new_div += "</p>";
			$new_div += "</div>";
		$new_div += "</div>";
		
		$("#doc_list").append($new_div);
		
		
		$new_elem = $("#doc_list").children("div [file_id="+$file_item.id+"]").first();
		//console.log("$new_elem : "+$new_elem);
		
		// On définit les Hover Btn
		$new_elem.find(".clickableBtn").on({
			click: function() {
				$fileName = $(this).parent().find("a").html();
				$fileId = $(this).parent().parent().parent().attr("file_id");
				remove_file_request($fileId, $fileName);
				//console.log("COUCOU "+$(this).parent().parent().parent().attr("file_id"));
				//console.log("COUCOU "+$(this).parent().find("a").html());
			},
			mouseover: function() {
				$(this).css("filter", "opacity(100%)");
				$(this).css("cursor","pointer");
			},
			mouseout: function() {
				$(this).css("filter", "opacity(50%)");
			}
		});
		
	}


	
	/***************  GENERATE ******************/
	
	// Popup pour générer un fichier
	function popup_generate() {

		//$filePath = $("#upload input[type=file]").first().val();
		//$fileName = $filePath.substring($filePath.lastIndexOf('\\')+1,$filePath.length);

		$form = "<div id='upload_block'>";
			$form += "<p>Veuillez définir les paramètres associés au fichier qui sera généré.</p>";
			$form += "<div>";
				$form += "<div id='upload_form'>";
					$form += "<p><label for='fileName'><small>Nom du fichier</small></label><br>";
						$form += "<input type='input' name='fileName' size='40' value='<?php echo date("Y-m-d")."_".$playlist_item['infos']['slug']; ?>' autofocus required/>&nbsp;.&nbsp;";
						$form += '<select id="fileType" style="margin-right:18px">';
							$form += "<option value=''></option>";
							$form += "<option value='zip'>zip</option>";
							$form += "<option value='pdf'>pdf</option>";
						$form += '</select>';
					$form += "</p>";
					$form += "</p>";
					$form += "<p><label for='accessType'><small>Accés/Visiblité</small></label><br>";
						$form += '<select id="accessType" style="margin-right:18px">';
							$form += "<option value='public'>Public</option>";
							$form += "<option value='cat'>Catégorie d'instrument</option>";
							$form += "<option value='instru'>Instrument</option>";
							$form += "<option value='tache'>Tâche</option>";
							$form += "<option value='admin'>Administrateurs</option>";
						$form += '</select>';
					$form += "</p>";
					$form += "<p><label for='textFile'><small>Texte de description</small></label><br>";
						$form += "<textarea id='textFile' style='width:350px; height:90px; padding:5px; resize:none'></textarea>";
					$form += "</p>";
				$form += "</div>";
				$form += "<div style='float:right'><input style='padding:6' type='button' value='Générer' onclick='generate_file()' disabled></div>";
			$form += "</div>";
		$form += "</div>";
		TINY.box.show({html:$form,boxid:'confirm',animate:false,width:650});
    }
	
	
	// Génère un fichier lié à la playlist
	function generate_file() {
				
		// On récupère l'id de la playlist
		$playlistId = $("#inscrTab").attr("data-playlistId");

		// On change le curseur
		$("body").addClass("wait");
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/generate_playlist_file",
		
			{
			'playlistId':$playlistId,
			'file_type':$("#upload_block #fileType").val(),
			'sort':$("#upload_block #alphaType").val() ? $("#upload_block #alphaType").val() : 0,
			'fileName': $("#upload_block [name=fileName]").val(),
			'parentId':'<?php echo $jam_item['id']; ?>',
			'accessType': $("#upload_block #accessType").val(),
			'accessId': $("#upload_block #accessId").val() ? $("#upload_block #accessId").val() : 0,
			'pdfType': $("#upload_block #pdfType").val() ? $("#upload_block #pdfType").val() : 0,    // pour les selection de media
			'pdfId': $("#upload_block #pdfId").val() ? $("#upload_block #pdfId").val() : 0,
			'memberId': '<?php echo $member->id; ?>',
			'text': $("#upload_block #textFile").val()
			},
		
			function (msg) {
								
				// On rétablit le pointeur
				$("body").removeClass("wait");

				if (msg.startsWith('Error') || msg.startsWith('<B>mPDF error')) TINY.box.show({html:"Le fichier n'a pas pu être généré !<br>"+msg,boxid:'error',animate:false,width:650});
				else {
					update_doc_section();
					$file = JSON.parse(msg);
					// Message de succés
					TINY.box.show({html:"Le fichier "+$file.name+" a été généré avec succès !",boxid:'success',animate:false,width:650, closejs:function(){}});
				}
			}
		);
		
		
	}
	

	/***************  UPLOAD ******************/
	// Popup pour uploader un fichier
	function popup_upload() {

		$filePath = $("#uploadInput").val();
		$fileName = $filePath.substring($filePath.lastIndexOf('\\')+1,$filePath.length);
		
		$form = "<div id='upload_block'>";
			$form += "<p>Veuillez définir les paramètres associés au fichier <b>"+$fileName+"</b></p>";
			$form += "<div>";
				$form += "<div>";
					$form += "<p><label for='accessType'><small>Accés/Visiblité</small></label><br>";
					$form += '<select id="accessType" style="margin-right:18px">';
						$form += "<option value='public'>Public</option>";
						$form += "<option value='cat'>Catégorie d'instrument</option>";
						$form += "<option value='instru'>Instrument</option>";
						$form += "<option value='tache'>Tâche</option>";
						$form += "<option value='admin'>Administrateurs</option>";
					$form += '</select>';
					$form += "</p>";
					$form += "<p><label for='textFile'><small>Texte de description</small></label><br>";
						$form += "<textarea id='textFile' style='width:350px; height:90px; padding:5px; resize:none'></textarea>";
					$form += "</p>";
				$form += "</div>";
				$form += "<div style='float:right'><input style='padding:6' type='button' value='Envoyer' onclick='upload_file()'></div>";
			$form += "</div>";
		$form += "</div>";
		TINY.box.show({html:$form,boxid:'confirm',animate:false,width:650});
    }
	
	
	
	// On upload un fichier
	function upload_file() {

		// On change le curseur
		document.body.style.cursor = 'progress';
	
		// On récupère les données du fichier
		var formData = new FormData();
		formData.append('file', $("#uploadInput")[0].files[0]);
		$filePath = $("#uploadInput").first().val();
		$fileName = $filePath.substring($filePath.lastIndexOf('\\')+1,$filePath.length);

		formData.append('fileName', $fileName);
		formData.append('parentType', 'event');
		formData.append('parentId', '<?php echo $jam_item['id']; ?>');
		formData.append('accessType', $("#upload_block #accessType").val());
		formData.append('accessId', $("#upload_block #accessId").val());
		formData.append('memberId', '<?php echo $member->id; ?>');
		formData.append('text', $("#upload_block #textFile").val());

		//console.log($("#upload_block #accessType").val());
	
		$.ajax({
		
			type: 'POST',
			url: "<?php echo site_url(); ?>/ajax/upload_file",
			data: formData,
			contentType: false,
			processData: false, 
			cache: false,
			success: function(msg) {
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// On récupère la version insérée (objet complet + insert_id)
				console.log("====MSG============");
				console.log(msg);
				
				if (msg == 'success') {
					TINY.box.show({html:"Le fichier a été envoyé avec succés !",boxid:'success',animate:false,width:650, closejs:function(){ }});
					update_doc_section();
				}
				else TINY.box.show({html:msg,boxid:'error',animate:false,width:650, closejs:function() {}});

			}

			/*,
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
			}*/
			
		});
    }
	
	/***************  REMOVE FILE ******************/
	// Popup pour supprimer un fichier
	function remove_file_request($fileId, $fileName) {
		$confirm = "<p>Etes-vous sûr de voulour supprimer le fichier '"+$fileName+"' ?</p>";
		$confirm += "<p style='text-align:center'><input type='button' value='supprimer' onclick='javascript:remove_file("+$fileId+",\""+$fileName+"\")'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()'></p>";
		TINY.box.show({html:$confirm,boxid:'confirm',animate:false,width:650});
    }
	
	
	// On supprimer un fichier
	function remove_file($fileId, $fileName) {

		// On change le curseur
		document.body.style.cursor = 'progress';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/remove_file",
		
			{'fileId':$fileId},
		
			function (msg) {

				// On rétablit le pointeur
				document.body.style.cursor = 'default';
			
				if (msg == "file_not_found") TINY.box.show({html:"Le fichier "+$fileName+" n'a pas pu être effacé !",boxid:'error',animate:false,width:650});
				else {				
					// Message de succés et on remove la div
					TINY.box.show({html:"Le fichier "+$fileName+" a été supprimé avec succès !",boxid:'success',animate:false,width:650, closejs:function(){ 
						$("#doc_list").children("div [file_id="+$fileId+"]").first().remove();
					}});
				}
			}
		);
    }
	

	
	// Actualise target avec le file size de path
	function set_file_size($path,$target) {
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/get_file_infos",
			{'path':$path},
			function (msg) {
				$file_infos = JSON.parse(msg);
				$($target+" #file_size").append($file_infos.sizeMo);
				$($target+" #file_size").css("display","inline");
			}
		);
	}

	
 </script>

 
<!-- Span caché pour obtenir les infos du membres -->
<span id="instru_cat1" style="display:none"><?php echo $instru_cat1; ?></span>
<span id="instru_cat2" style="display:none"><?php echo $instru_cat2; ?></span>


<?php
	// Retourne un tableau keys où $array[key]->$param == $id
	function searchForId($id, $array, $param) {
		$keys = array();
		foreach ($array as $key => $val) {
		   if ($val[$param] === $id) {
			   array_push($keys,$key);
		   }
		}
		return $keys;
	}
?>	


<!-- Block infos -->
<div class="main_block">
<h3 class="block_title"><?php echo $page_title ?> : Tableau d'inscription</h3><br>
<?php if ($jam_item["text_tab"] != "") : ?>
	<div class="small_message">
		<h4>infos</h4>
		<div id="text_tab"><?php echo $jam_item["text_tab"] ?>
		</div>
	</div>
<?php endif; ?>
</div>
 
<!--------- Section d'admin pour le texte d'intro !----------->
<?php if ($this->session->userdata('admin') == 1) : ?>
<!-- Barre pour ouvrir le cadre d'admin -->
	<div class="block_footer" style="margin-top:-10">
		<hr style="width:100%; margin:0;">
		<div class="soften">
			<a id="admin_info_item" href="javascript:trig_admin_section('info')" class="ui_elem soften"><img width="16" style="vertical-align: middle;" src="/images/icons/edit.png" alt="edit">  modifier</a>
		</div>
		<hr style="width:100%; margin:0; padding: 0 0 2 0;">
	</div>
<?php endif; ?>


<div id="admin_info_section" class="block" style="display:none; padding-bottom:0">
	<form action="javascript:update_text_tab()">
		<div>
			<textarea name="editor1" id="editor1">
			</textarea>
			<script>
				CKEDITOR.replace( 'editor1', { customConfig : 'config_medium.js' } );
				CKEDITOR.width="500";
			</script>
			<!-- Bouton !-->
			<div style="text-align:right;padding-bottom:0"><small><input id="admin_submit" class="button" type="submit" name="submit" value="Mettre à jour" /></small></div>
		</div>
	</form>
</div>
 
 <br>
 
<!--========================= PLAYLIST  =========================-->
<?php if ($playlist_item['list'] != 0) : ?>

	<div style="overflow:auto">
	<table id="inscrTab" class="is_playable" style="width:100%;" 
					data-playlistId="<?php echo $playlist_item['infos']['id'] ?>">

		<!-- Headers de colonne catégories d'instruments !-->
		<thead>
		<tr>
			<th style="width:80;">&nbsp </th>
			<?php foreach ($cat_instru_list as $cat): ?>
				<th class="cat_header"
					id="cat_<?php echo $cat['name']?>"
					colspan="<?php echo sizeof($cat['list']); ?>"
					onclick="display_cat('<?php echo $cat['name']?>')"				
					>
					
					<?php echo ($cat['name']=="hors catégorie" ? $cat['name'] : $cat['name']);?> <!--<span onclick="display_cat('<?php echo $cat['name']?>')">+</span>-->
				</th>
			<?php endforeach; ?>
		</tr>
		
		
		<!-- Headers de colonne instruments !-->
		<tr>
			<th>&nbsp </th>
			<?php 
				$nbcol = 1;
				foreach ($cat_instru_list as $cat) {
					echo "<th class='hidden_".$cat['name']." hidden_cell' style='display:none'>&nbsp;</th>";
					foreach ($cat['list'] as $instru) {
						if($instru) echo '<th class="catelem_'.$cat['name'].'" idInstru="'.$instru.'">'.$this->instruments_model->get_instrument($instru).'</th>';
						// On enregistre le nombre de colonne du tableau
						$nbcol++;
					}
				}?>
		</tr>
		
		</thead>
		
		
		<tbody>
		<!-- Ligne des morceaux !-->
		<?php foreach ($playlist_item['list'] as $key=>$ref): ?>
			<tr class="<?php if ($ref->reserve_stage) echo "stage_elem";?>" versionId="<?php echo $ref->versionId; ?>">
			
				<!-- On gère les pauses !-->
				<?php if ($ref->versionId == -1) :?>
					<td colspan="<?php echo $nbcol; ?>"></td>
				<!-- Titre du morceau !-->
				<?php else : ?>
					<td class="song">
						<?php echo $ref->titre;
							$titreSong = $ref->titre; 
						?>
					</td>
					
					
					<?php foreach ($cat_instru_list as $cat) {
						echo "<td class='hidden_".$cat['name']." hidden_cell' style='display:none'></td>";
						foreach ($cat['list'] as $idInstru) {
							if($idInstru) {
								echo '<td class="catelem_'.$cat['name'].'">';
								
								// On affiche le pseudo du membre affecté si besoin
								if ($jam_item["affectations_visibles"]) {
									// On recherche l'id des affectés par rapport au titre de la ligne $titresong
									$keys = searchForId($titreSong,$affectations,"titre");
									if (isset($keys)) {
										$find = false;
										// Pour chaque référence, on affiche le pseudo
										foreach ($keys as $key) {
											if($idInstru == $affectations[$key]['instruId']) {
												$find = true;
												echo "<p class='affected'>".$affectations[$key]['pseudo']."</p>";
											}
										}
									}
								}
								else $find = false;
								
								// Si l'internaute est concerné	la checkbox doit être affichée
								$set_checkbox = false;
								if ($idInstru == $member->idInstru1 || $idInstru == $member->idInstru2 )
									$set_checkbox = true;

								////// On affiche la liste des inscrits sur ce morceaux
								// On recherche l'id des inscrits par rapport au titre de la ligne $titresong
								$keys = searchForId($titreSong,$inscriptions,"titre");
								if (isset($keys)) {
									$is_set = false;
									// Pour chaque référence, on affiche le pseudo
									foreach ($keys as $key) {
										if($idInstru == $inscriptions[$key]['instruId']) {
											// On affiche une hr si besoin
											if ($find) { echo '<hr style="margin:inherit">'; $find = false; }
											echo '<p style="white-space:nowrap; background-color:inherit">';
											// Si l'inscription est effective, on coche la checkbox
											if ($set_checkbox && $inscriptions[$key]['pseudo'] == $member->pseudo) {
												echo '<input type="checkbox" name="" value="" idInstru="'.$idInstru.'"'.set_checkbox("top", "set_top",true).' />&nbsp';
												$is_set = true;
											}
											//echo getChoicePos($inscriptions[$key]['jam_membresId'],$key,$inscriptions).".".$inscriptions[$key]['pseudo']."</p>";
											echo $inscriptions[$key]['choicePos'].".".$inscriptions[$key]['pseudo'];
											echo "</p>";
										}
									}
									// Si il n'y a pas d'inscription, on affiche la checkbox décochée.
									if ($set_checkbox && !$is_set) echo '<p style="background-color:inherit"><input type="checkbox" name="" value="" idInstru="'.$idInstru.'"'.set_checkbox("top", "set_top").' /></p>';
								}

							}
							else echo '&nbsp';
							
							echo '</td>';
						}
					}?>
				<?php endif; ?>				
			</tr>
		<?php endforeach; ?>
		<tbody>
		
		
	</table>
	</div>


</div> <!-- On ferme le spécial content !-->


<br>

<!--========================= Formulaire =========================-->
<div class="content">


	<div id="sortlist">
		<div>
			<lessons></lessons>
		</div>
		
		<template id="lessons-template">
			<div>
				<ul v-el:sortable>
					<li v-for="item in list" id="{{item}}">{{ item }}</li>
				</ul>

				{{list | json}}
			</div>
		</template>
	</div>
	
	<div id="list">
		<li v-for="item in items">
			{{ item.message }}
		</li>
	</div>
	
	<br>
	<div id="testcompo">
		<my-list></my-list>
	</div>
	
	
	
	
	<hr>

	<div style="display:flex; align-items: flex-start;">

		<!----------- ORDRE DES CHOIX  ------------>
		<div class="block_left">

			<h3 class="block_title">Ordre des choix</h3>

			<?php echo form_open('jam/inscriptions/'.$jam_item['slug']) ?>
				
				<table class="listTab" style="width:550;">
					<tbody id="choice_list">
					</tbody>
				</table>
				
				
				<!-- Obligé pour forcer le push de la multipl select !-->
				<div style="display:none">
					<input id="titre" type="input" name="title" value="bidon"/>
					<?php echo form_error('title'); ?>
				</div>	
				
				<select id="hidden_select" name="choice_list[]" multiple style="display:block">
					<?php
						////// On insère dans la hidden_select list la liste des morceaux sur lequel le membre est inscrit
						$keys = searchForId($member->pseudo,$inscriptions,"pseudo");
						if (isset($keys)) {
							// Pour chaque référence, "versionId - idInstru"
							foreach ($keys as $key) {
								echo "<option versionId='".$inscriptions[$key]['versionId']."'idInstru='".$inscriptions[$key]['instruId']."' selected>".$inscriptions[$key]['versionId']." - ".$inscriptions[$key]['instruId']."</option>";
							}
						}
					?>
				</select>
				<?php echo form_error('hidden_select'); ?>
				<br />
				<input class="button" type="submit" name="submit" value="  Inscription  " />
			</form>	

		</div>

		<!-- Pour l'espacement entre les colonnes !-->
		<div style="width:18px">&nbsp;</div>

		
		<!----------- DOCUMENTS ------------>
		<div style="width:50%">

			<?php if (true) : ?>
				<div id="doc_block" class="block">
					<h3 class="block_title">Documents</h3><br>
					<div id="doc_list"></div>
				</div>
				
			<?php endif; ?>		
			
			<!----------- DOCUMENT ADMIN ------------>
			<?php if ($this->session->userdata('admin') || $is_admin) : ?>
				<!-- Barre pour ouvrir le cadre d'admin -->
				<div id="doc_admin_block" class="block_footer">
					<hr style="width:100%; margin:0;">
					<div class="soften">
						<a id="admin_doc_item" href="javascript:trig_admin_section('doc')" class="ui_elem soften"><img style="vertical-align: middle;" src="/images/icons/add.png" alt="add">  admin</a>
					</div>
					<hr style="width:100%; margin:0; padding: 0 0 2 0;">
				</div>

				<div id="admin_doc_section" class="block" style="display:none; flex-direction:row-reverse">
				
					<!----- FILE BLOCK ----->
					<div>
						<div class="small_block_list_title soften">Ajouter un fichier</div>
						<div class="small_block_info file_list" style="text-align:left; margin:inherit; width:inherit">
							<ul class="soften">
								<li><!-- Générate -->
									<a href="javascript:popup_generate()" class="action_icon clickableBtn soften">
										<small><span>Générer un fichier à partir de la playlist.</span></small>
										<img style="height: 14; vertical-align:text-bottom;" src="/images/icons/gear.png" alt="Générer" title="générer zip">
									</a>
								</li>
								<li><!-- Nom de fichier ou texte -->
									<div class="fileUpload clickableBtn action_icon">
										<input id="uploadInput" type="file" class="upload" style="width:286px; height: 24px">
										<span><small>Uploader un fichier.</small></span>
										<img style="height: 14; vertical-align:text-bottom;" src="/images/icons/upload.png" alt="Envoyer" title="envoyer fichier">
									</div>
								</li>
							</ul>
						</div>
					</div>
					
				</div>
			<?php endif; ?>	
		</div>

	</div>
	
	<br>


	<?php else:?>
	<div class="main_block small_block_alert"><p>Pas de playlist sélectionnée.</p>
	</div>
	<br>
	<?php endif; ?>





<script>

	var el = $('#choice_list').get(0);
	var sortable = Sortable.create(el, {
		ghostClass: "ghost"
	});

	
</script>

