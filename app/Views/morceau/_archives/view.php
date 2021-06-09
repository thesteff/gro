<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/jquery.tablesorter.js"></script>

<!-- Tablesorter: filter -->
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-storage.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-filter.js"></script>

<!-- Tablesorter: pager -->
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-pager.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-columnSelector.js"></script>

<!-- flexdatalist pour les input !-->
<script type="text/javascript" src="<?php echo base_url();?>ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.js"></script>
<link href="<?php echo base_url();?>ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.css" rel="stylesheet" type="text/css" />



<script type="text/javascript">

	$(function() {
		
		$table1 = $( 'table' )
			.tablesorter({
				theme : 'sand',
				
				// initialize zebra and filter widgets
				widgets : [ "zebra", "filter", "pager" ],

				widgetOptions: {
					// output default: '{page}/{totalPages}'
					// possible variables: {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
					pager_output: '{page}/{totalPages}',

					pager_removeRows: false,

					// include child row content while filtering, if true
					filter_childRows  : true,
					// class name applied to filter row and each input
					filter_cssFilter  : 'tablesorter-filter',
					// search from beginning
					filter_startsWith : false,
					// Set this option to false to make the searches case sensitive
					filter_ignoreCase : true
				}

			});
		
		// On stylise les colonnes
		$("#songlist .centerTD").each(function() {
			$(this).css("text-align","center");
			$(this).parents("table").find("tbody tr td:nth-child("+($(this).index()+1)+")").css("text-align","center");
		});
				
		
		// **************** Titre
		// On rempli les flexdatalist
		$('.flexDL_Titre').flexdatalist({
			 minLength: 0,
			 selectionRequired: true,
			 data: [{ 'id':'-1', 'name':'morceau non défini'},
					<?php foreach ($list_song as $song): ?>
						{ 'id':'<?php echo $song->id ?>', 'name':'<?php echo addslashes(htmlspecialchars($song->titre)) ?>'},
					<?php endforeach ?>
					],
			 searchIn: 'name',
			 searchByWord: true,
			 valueProperty: 'id'	// on envoie l'attribut 'id' quand on appelle la méthode val()
			});
		
		
		// **************** Titre
		// On fait un input
		$("#titreInput").on("input", function( event ) {
			if ($("#titreInput").val() == "") {
				$("#add_morceau").css("display","none");
				$("#compoInput").val("");	$("#compoInput").attr("old_value","");
				$("#anneeInput").val("");	$("#anneeInput").attr("old_value","");
				$("#update_morceau").css("display","none");		$("#update_separator").css("display","none");
				$("#delete_morceau").css("display","none");
				// On reset les blocks version
				$("#version_menubar").css("display","none");
				$("#version_menubar_content").empty();
				$("#version_block").css("display","none");
				$("#version_toolbar").css("display","none");
				if ($("#display_version_block a").hasClass("active")) $("#display_version_block a").removeClass("active");
				// On deselect la tr de la table
				$("#songlist tbody tr.selected").removeClass("selected");

			}
			else titre_change("input");
		});
		
		// On sort de l'input, on test si c'est bien un nouveau morceau ou pas
		$("#titreInput").on("blur", function( event ) {
			if ($("#titreInput").val() == "" || $("#titreInput").val() == $("#titreInput").attr("old_value")) return;
			else titre_change("blur");
		});
		
		
		
		// **************** Compositeur
		// On fait un input
		$("#compoInput").on("input", function( event ) {
			// Si y'a un input alors que le morceau a été select on active l'update
			if (parseInt($("#morceau").attr("morceauId")) > 0) {
				if ($("#compoInput").val() != $("#compoInput").attr("old_value")) { $("#update_morceau").css("display","block");	$("#update_separator").css("display","block"); }
				else if ($("#anneeInput").val() == $("#anneeInput").attr("old_value")) { $("#update_morceau").css("display","none");	$("#update_separator").css("display","none"); }
			}
			compo_change("input");
		});
		
		// On sort de l'input
		$("#compoInput").on("blur", function( event ) {
			if ($("#compoInput").val() == "") return;
			compo_change("blur");
		});
		
		
		// **************** Année
		$("#anneeInput").on("input", function( event ) {
			// Si y'a un input alors que le morceau a été select, on active l'update
			if (parseInt($("#morceau").attr("morceauId")) > 0) {
				if ($("#anneeInput").val() != $("#anneeInput").attr("old_value")) { $("#update_morceau").css("display","block");	$("#update_separator").css("display","block"); }
				else if ($("#compoInput").val() == $("#compoInput").attr("old_value")) { $("#update_morceau").css("display","none");	$("#update_separator").css("display","none"); }
			}
		});
		
		// On sort de l'input
		$("#anneeInput").on("blur", function( event ) {
			$year = new Date().getFullYear();
			if ($("#anneeInput").val() == "") return;
			else if (! $("#anneeInput").val().match(/^\d+$/)) 
				TINY.box.show({html:"Votre saisie n'est pas valide (entier positif attendu).",boxid:'error',animate:false,width:650, closejs:function(){ $("#anneeInput").val($("#anneeInput").attr("old_value")) }});
			else if ( $("#anneeInput").val() > $year || $("#anneeInput").val() < 0) 
				TINY.box.show({html:"Votre saisie n'est pas valide (entier compris entre '0' et '"+$year+"').",boxid:'error',animate:false,width:650, closejs:function(){ $("#anneeInput").val($("#anneeInput").attr("old_value")) }});
		});
		


		// **************** Upload MP3
		$("#mp3UploadInput").on("change", function() {

			// On récupère le nom de fichier
			$new_val = $(this).val().substring($(this).val().lastIndexOf("\\")+1,$(this).val().length);
			
			// Si le champ était rempli, on supprime l'ancien fichier
			if ($("#mp3URLInput").val() != '') delete_file($("#mp3URLInput").val(), "mp3URL");
			
			$("#mp3URLInput").val($new_val);
			if ($("#mp3URLInput").val() == '') $("#mp3URLX").css("display","none");
			else $("#mp3URLX").css("display","inline-flex");
		});
		
		$("#mp3URLX").on("click", function() {
			$filename = $(this).prev().children("[type='text']").val();
			$("#mp3UploadInput").val("");
			delete_file($filename, "mp3URL");
			
		});
		
		
		// **************** Upload Media model
		$("#uploadInput").on("change", function() {
			
			// On récupère le nom de fichier
			$new_val = $(this).val().substring($(this).val().lastIndexOf("\\")+1,$(this).val().length);
			
			// Si le champ était rempli, on supprime l'ancien fichier
			if ($("#URLInput").val() != '') delete_file($("#URLInput").val(), "URL");
			
			$("#URLInput").val($new_val);
			if ($("#URLInput").val() == '') $("#URLX").css("display","none");
			else $("#URLX").css("display","inline-flex");
		});
		
		$("#divMediaModel #URLX").on("click", function() {
			$media_selected = '';
			$(this).prev().children("input").val("");
			$(this).css("display","none");
		});
		

		
		
		// **************** Songlist // Pour updater la section morceau en cas de clic dans le tableau
		$("#songlist tbody").on("click", function(event) {
			console.log("click");
			// On récupère la tr cliquée
			$tr = $(event.target).parent();
			$("#titreInput").val($tr.children(":first-child").html());
			$version_selected = $tr.attr("versionId");
			titre_change("blur");
		});
		
		
		// Data des versions
		$versions_data = [];
		$version_selected = "";
		$media_selected = "";
		
	});

	
	/**********************************************************************/
	/**********************************************************************/
	
	function titre_change($action) {
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_morceau_by_titre",
		
			{
			'titre':$("#titreInput").val()
			},
	
			function (msg) {
				//alert(msg);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Le morceau n'est pas présent mais on est encore en input
				if (msg == "morceau_not_found") {
					// On passe de trouvé à non trouvé
					if (parseInt($("#morceau").attr("morceauId")) > 0) {
						$("#morceau").attr("morceauId","-1");
						$("#add_morceau").css("display","block");
						$("#update_morceau").css("display","none");		$("#update_separator").css("display","none");
						$("#delete_morceau").css("display","none");
						// On reset les blocks version
						$("#version_menubar").css("display","none");
						$("#version_block").css("display","none");
						$("#version_toolbar").css("display","none");
						if ($("#display_version_block a").hasClass("active")) $("#display_version_block a").removeClass("active");
						// On deselect la tr de la table
						$("#songlist tbody tr.selected").removeClass("selected");
						return;
					}
					else if ($("#add_morceau").css("display") == "none") $("#add_morceau").css("display","block");
				}
				
				// Le Morceau FOUND !
				else {
					console.log("morceau Found !");
					// On récupère les infos
					$data = JSON.parse(msg);
				
					// On le met set et on actualise le old_value
					$("#morceau").attr("morceauId",$data.morceau.id);
					$("#titreInput").attr("old_value",$("#titreInput").val());

					// On ne peut plus créer le morceau avec le lien ni l'update
					$("#add_morceau").css("display","none");
					$("#update_morceau").css("display","none");		$("#update_separator").css("display","none");
					$("#delete_morceau").css("display","block");
					
					
					// On actualise les champs input du morceau ainsi que les old_value
					$data.artiste ? $("#compoInput").val($data.artiste.label) : $("#compoInput").val("");
					$("#anneeInput").val($data.morceau.annee);
					
					$("#compoInput").attr("old_value",$("#compoInput").val());
					$("#anneeInput").attr("old_value",$("#anneeInput").val());

					// On reset les blocks version et on charge les versions
					if ($("#display_version_block a").hasClass("active")) $("#display_version_block a").removeClass("active");
					$("#version_menubar_content").empty();
					get_versions();
					$("#version_menubar").css("display","inline-flex");
				}

			}
		);
	}
	
	
	function compo_change($action) {

		// En cas de reset
		if ($action == "reset") {
			$("#compoInput").val($("#compoInput").attr("old_value"));
			return;
		}
		
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_artist",
		
			{
			'artist_name':$("#compoInput").val()
			},
	
			function (msg) {
			
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// L'artiste n'est pas présent mais on est encore en input
				if (msg == "artist_not_found" && $action == "input") return;
				
				// L'artiste spécifié n'est pas présent dans la base et on perd le focus => on propose de créer l'artiste
				else if (msg == "artist_not_found" && $action == "blur") {
					$txt = "<p>L'artiste spécifié n'est pas présent dans la base de données.<br> Voulez-vous le créer ?</p>"
					$txt += "<p style='text-align:center'><input type='button' value='valider' onclick='javascript:create_artist_box(\""+encodeURI($("#compoInput").val())+"\")'>"+"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide(); compo_change(\"reset\");' ></p>";
					TINY.box.show({html:$txt,boxid:'confirm',animate:true,width:650, closejs:function(){$("#compoInput").val("")}});
				}
				
				// Si on a trouvé l'artiste
				else {
					//$artist = JSON.parse(msg);
				}
			}
		);
		
	}

	
	// Formulaire de création de lieu
	function create_artist_box($artist_name) {
	
		$html = "<p><b><u>Ajouter un artiste</u></b></p>";
		$html += "<div class='formLayout'>";
		$html += "<label>Nom</label><input id='artist_pop_name' size='32' value='"+decodeURI($artist_name)+"'><br>";
		$html += "</div>";
		$html += "<p style='text-align:center'><input type='button' value='ajouter' onclick='javascript:create_artist()'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()' ></p>";
		TINY.box.show({html:$html,boxid:'confirm',animate:false,width:650});
	
	}
	
	// Création du lieu
	function create_artist() {
	
		$artist_name = $("#artist_pop_name").val()
		
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/add_artist",
		
			{
			'name': $artist_name,
			},
	
			function (msg) {
				// On change le curseur
				
				document.body.style.cursor = 'default';
				
				if (msg == "success") TINY.box.show({html:"L'artiste a été ajouté à la base de donnée.",boxid:'success',animate:false,width:650, closejs:function(){ $("#compoInput").append("<option value='"+$artist_name+"'>"+$artist_name+"</option>"); compo_change("blur"); } });
				else TINY.box.show({html:"L'artiste "+$artist_name+" est déjà présent dans la base de donnée !",boxid:'error',animate:false,width:650, closejs:function(){ $("#compoInput").val($artist_name); } });
			}
		);
	}
	

	/**********************************************************************/
	/*************************    MORCEAUX   ******************************/
	/**********************************************************************/	
	
	function add_morceau() {
		// On change le curseur
		document.body.style.cursor = 'progress';

		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/set_morceau",
		
			{
			'morceauId':"-1",		// tag pour faire un ajout plutôt qu'un update
			'titre':$("#titreInput").val(),
			'compo_label':$("#compoInput").val(),
			'annee':$("#anneeInput").val()
			},
	
			function (msg) {
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Le morceau a été ajouté dans la base
				if (msg.indexOf("success") > -1) {
					$new_id = msg.substr(msg.indexOf("=> ")+3);
					$("#morceau").attr("morceauId",$new_id);
					$("#add_morceau").css("display","none");
					$("#version_toolbar").css("display","inline-flex");
					
					
					// On ajoute le morceau dans la table
					$tr = "<tr morceauId='"+$new_id+"' versionId=''>";
					$("#songlist thead th span").each( function() {
						// On parcourt les thead avec titre / span obligatoire car tablesorter rajoute des div de partout
						switch($(this).html()) {
							case "Titre": 
								$tr += "<td class='song'>"+$('#titreInput').val()+"</td>";
								break;
							case "Compositeur":
								$tr += "<td>"+$("#compoInput").val()+"</td>";
								break;
							case "Année":
								$tr += "<td>"+$("#anneeInput").val()+"</td>";
								break;
							default:
								$tr += "<td></td>";	
						}						
					});
					$tr += "</tr>";
					$table1.trigger('addRows',[$tr, true]);
					
					// On actualise l'UI (songlist inclue)
					trig_add_version();
					$("#delete_morceau").css("display","block");
					$("#version_menubar_content").empty();
					$versions_data = [];
					
					// Pour activer la selection des tr		
					$new_tr = $("#songlist tr[morceauId='"+$new_id+"']");
					$new_tr.on("click", function() {
						console.log("NEW TR CLICK");
						// On déselectionne la tr précédente
						$(this).closest("tbody").find(".selected").removeClass("selected");
						// La tr devient selected
						$(this).addClass("selected");
						update_player($(this).attr("morceauId"), $(this).attr("versionId"));
					});
								
					TINY.box.show({html:"Le morceau a été ajouté à la base de donnée !",boxid:'success',animate:false,width:650, closejs:function(){}});
				}
				
				// Erreur à l'insertition de morceau
				else {
					TINY.box.show({html:"Le morceau n'a pas été ajouté à la base de donnée !<br>"+msg,boxid:'error',animate:false,width:650, closejs:function(){  } });
				}

			}
		);

	}
	
	
	function update_morceau() {
		// On change le curseur
		document.body.style.cursor = 'progress';


		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/set_morceau",
		
			{
			'morceauId':$("#morceau").attr("morceauId"),
			'titre':$("#titreInput").val(),
			'compo_label':$("#compoInput").val(),
			'annee':$("#anneeInput").val()
			},
	
			function (msg) {
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Le morceau a été modifié dans la base
				if (msg == "success") {
					$("#update_morceau").css("display","none");			$("#update_separator").css("display","none");
					$("#titreInput").attr("old_value",$("#titreInput").val());
					$("#compoInput").attr("old_value",$("#compoInput").val());
					$("#anneeInput").attr("old_value",$("#anneeInput").val());
					
					// On fait un update dans la table
					$tr = $("#songlist tbody tr[morceauId='"+$("#morceau").attr("morceauId")+"']");
					$indexCol = $("#songlist thead th[connect='compoInput']").index()+1;
					$tr.children(":nth-child("+$indexCol+")").html($("#compoInput").val());
					$indexCol = $("#songlist thead th[connect='anneeInput']").index()+1;
					$tr.children(":nth-child("+$indexCol+")").html($("#anneeInput").val());
					$table1.trigger("updateCache");
					
					TINY.box.show({html:"Le morceau a été modifié dans la base de donnée !",boxid:'success',animate:false,width:650, closejs:function(){}});
				}
				
				// Erreur à l'update du morceau
				else {
					TINY.box.show({html:"Le morceau n'a pas été modifié dans la base de donnée !<br>"+msg,boxid:'error',animate:false,width:650, closejs:function(){  } });
				}

			}
		);

	}
	
	
	function delete_morceau() {
		// On change le curseur
		document.body.style.cursor = 'progress';

		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/delete_morceau",
		
			{
			'morceauId':$("#morceau").attr("morceauId")
			},
	
			function (msg) {
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Le morceau a été supprimé de la base, on reset le formulaire
				if (msg == "success") {
					$("#add_morceau").css("display","none");
					$("#titreInput").val("");
					$("#compoInput").val("");	$("#compoInput").attr("old_value","");
					$("#anneeInput").val("");	$("#anneeInput").attr("old_value","");
					$("#delete_morceau").css("display","none");
					$("#version_menubar").css("display","none");
					$("#version_block").css("display","none");
					$("#version_toolbar").css("display","none");

					// On delete la tr dans la songlist
					$("#songlist tbody tr[morceauId='"+$("#morceau").attr("morceauId")+"']").remove();
					$table1.trigger('update');
					
					TINY.box.show({html:"Le morceau a été supprimé de la base de donnée !",boxid:'success',animate:false,width:650, closejs:function(){}});
				}
				
				// Erreur à l'insertition de morceau
				else {
					TINY.box.show({html:"Le morceau n'a pas été supprimé de la base de donnée !<br>"+msg,boxid:'error',animate:false,width:650, closejs:function(){  } });
				}
			}
		);
	}
	
	
	/**********************************************************************/
	/*************************    VERSIONS   ******************************/
	/**********************************************************************/	
	
	// Permet d'ouvrir la section d'ajout de version
	function trig_add_version() {
		
		// On affiche la menubar si besoin (après ajout de morceau)
		if ($("#version_menubar").css("display") == "none") $("#version_menubar").css("display","inline-flex");
		
		// On change la couleur du lien et on desectionne l'item de la menu bar
		$("#display_version_block a").addClass("active");
		$("#version_menubar_content [is_selected='true']").attr("is_selected","false");
		
		$version_selected = '';
		
		// On reset le version block
		show_version(-1);
		
		// On ne peut plus créer des medias
		$("#display_media_block a").removeClass("active");
		$("#media_menubar").css("display","none");
		$("#media_block").css("display","none");
	}
	
	
	// On récupère les versions en fonction du morceau selectionné
	function get_versions() {
		// On change le curseur
		document.body.style.cursor = 'progress';

		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_versions",
		
			{
			'morceauId':$("#morceau").attr("morceauId")
			},
	
			function (msg) {

				// On change le curseur
				document.body.style.cursor = 'default';

				// PAS DE VERSION, on ouvre le add_version et on select la tr morceau
				if (msg == "versions_not_found") {
					trig_add_version();
					// On deselect la tr de la table
					/*$("#songlist tbody tr[morceauId='"+$("#morceau").attr("morceauId")+"']").addClass("selected");
					console.log("update_player get_versions not found");
					update_player($("#morceau").attr("morceauId"),'');*/
					return;
				}
				
				// VERSION RECUES
				$versions_data = JSON.parse(msg);
				
				// On remplit la menu bar en mettant selected le premier
				$.each($versions_data, function($index, $value) {
					if ($index == 0 && $version_selected == "") {
						$selected = "true";
						$version_selected = $value.id;
					}
					else if ($value.id == $version_selected) $selected = "true";
					else $selected = "false";
					$("#version_menubar_content").append("<a id="+$value.id+" is_selected='"+$selected+"' target='_self'>"+$value.groupe+"</a>");
				});
				
				
				// On set le onclick sur les items de la version menubar
				$("#version_menubar_content a").on("click", function() {
					// On deselectionne tout
					$version_selected = "";
					$(this).parent().children("[is_selected='true']").attr("is_selected","false");
					if ($("#display_version_block a").hasClass("active")) $("#display_version_block a").removeClass("active");

					// On selectionne le this
					$(this).attr("is_selected","true");
					$version_selected = $(this).attr("id");
					
					// On populate le version block
					show_version($(this).index());
					
					// On remplit le liste de media en fonction de version selected
					get_medias();

				});
				
				// On populate le version block
				show_version($("#version_menubar_content a[id='"+$version_selected+"']").index());
				
				// On peut créer des medias
				$("#media_menubar").css("display","inline-flex");
				
				// On remplit le liste de media en fonction de version selected
				get_medias();
			}
		);	
	}
	
	
	// Envoie les données de la version + upload du fichier
	function add_version() {
		
		// Alert si aucun groupe n'a été identifié
		if ($("#groupInput").val() == "") {
			$alert = "<p>Pour créer une version, vous devez d'abord indiquer un groupe !</p>";
			TINY.box.show({html:$alert,boxid:'error',animate:false,width:650, closejs:function() {$("#groupInput").focus();}});
			return;
		}
		
		// Alert si le groupe saisis existe déjà
		$find = false;
		$("#version_menubar a").each(function() {
			if ($(this).html().toUpperCase() == $("#groupInput").val().toUpperCase()) {
				$alert = "<p>Le morceau a déjà une version liée au groupe "+$("#groupInput").val()+" !</p>";
				TINY.box.show({html:$alert,boxid:'error',animate:false,width:650, closejs:function() {$("#groupInput").select();}});
				$find = true;
			}
		});
		if ($find) return;
		
		// Alert si on essaie d'uploader autre chose qu'un mp3
		if ($("#mp3URLInput").val().length > 0 && $("#mp3URLInput").val().substr(-4) != ".mp3") {
			$alert = "<p>Vous n'avez pas sélectionné de fichier valide (mp3 attendu) !</p>";
			TINY.box.show({html:$alert,boxid:'error',animate:false,width:650, closejs:function() {
					$("#mp3UploadInput").val('');
					$("#mp3URLInput").val('');
					$("#mp3URLX").css("display","none");
				}
			});
			return;
		}
				
		// On récupère les infos de la version		
		var formData = new FormData();
		formData.append('file', $('input[type=file]')[0].files[0]);
		$('#version_block input[type=text]').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).val());
		});
		$('#version_block select').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).children(":selected").val());
		});
		$('#version_block input[type=checkbox]').each(function() {
			formData.append($(this).attr("id").replace("Cb",""), $(this).prop("checked"));
		});
		
		console.log("formData : "+$("#morceau").attr("morceauId"));
		
		// On ajoute les infos du morceau id (bd) + label
		formData.append("morceauId",$("#morceau").attr("morceauId"));
		formData.append("morceauLabel",$("#titreInput").val());
		
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		$.ajax({
		
			type: 'POST',
			url: "<?php echo site_url(); ?>/ajax/add_version",
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
				$version = JSON.parse(msg);
				
				// On désélectionne l'item du menu version et on séléctionne la version créee et on la créer dans la version menubar
				$("#version_menubar_content").children("[is_selected='true']").attr("is_selected","false");
				$new_version_id = $version.id;
				console.log("new_version_id : "+$version.id);
				$("#version_menubar_content").append("<a id='"+$new_version_id+"' is_selected='true' target='_self'>"+$("#groupInput").val()+"</a>");

				// On set le onclick event sur un titre de version (groupe)
				$("#version_menubar_content a[is_selected='true']").on("click", function() {
					console.log("CLICK MENUBAR");
					// On actualise la version select et on deselectionne le reste
					$new_version_id = $versions_data[$(this).index()].id;
					$(this).parent().children("[is_selected='true']").attr("is_selected","false");
					if ($("#display_version_block a").hasClass("active")) $("#display_version_block a").removeClass("active");

					// On selectionne le this
					$(this).attr("is_selected","true");
					$version_selected = $versions_data[$(this).index()].id;
					show_version($(this).index());
					// On remplit le liste de media en fonction de version selected
					get_medias();
				});

				
				// On update la table
				update_row($new_version_id);
				
				// On update l'UI
				$("#display_version_block a").removeClass("active");
				$("#update_version").css("display","block");	$("#version_toolbar span.separator").css("display","block");
				$("#delete_version").css("display","block");
				$("#add_version").css("display","none");
				
				// On peut créer des medias (on reset d'abord)
				get_medias();
				$("#media_menubar").css("display","inline-flex");

				//$versions_data = JSON.parse(msg);
				console.log("version_data1 : "+JSON.stringify($versions_data));
				// On actualise le versions_data
				$versions_data.push($version);
				console.log("version_data2 : "+JSON.stringify($versions_data));
				
				TINY.box.show({html:"La version a été ajoutée à la base de donnée !",boxid:'success',animate:false,width:650, closejs:function(){ }});

				
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

	
	
	function update_version() {
		
		// Alert si aucun groupe n'a été identifié
		if ($("#groupInput").val() == "") {
			$alert = "<p>Pour créer une version, vous devez d'abord indiquer un groupe !</p>";
			TINY.box.show({html:$alert,boxid:'error',animate:false,width:650, closejs:function() {$("#groupInput").focus();}});
			return;
		}
		
		// Alert si le groupe saisis existe déjà et n'est pas celui selectionné
		$find = false;
		$("#version_menubar a[is_selected!='true']").each(function() {
			if ($(this).html().toUpperCase() == $("#groupInput").val().toUpperCase()) {
				$alert = "<p>Le morceau a déjà une version liée au groupe "+$("#groupInput").val()+" !</p>";
				TINY.box.show({html:$alert,boxid:'error',animate:false,width:650, closejs:function() {$("#groupInput").select();}});
				$find = true;
			}
		});
		if ($find) return;
		
		// Alert si on essaie d'uploader autre chose qu'un mp3
		if ($("#mp3URLInput").val().length > 0 && $("#mp3URLInput").val().substr(-4) != ".mp3") {
			$alert = "<p>Vous n'avez pas sélectionné de fichier valide (mp3 attendu) !</p>";
			TINY.box.show({html:$alert,boxid:'error',animate:false,width:650, closejs:function() {
					$("#mp3UploadInput").val('');
					$("#mp3URLInput").val('');
					$("#mp3URLX").css("display","none");
				}
			});
			return;
		}
		
		// On récupère les infos de la version		
		var formData = new FormData();
		
		formData.append('file', $('input[type=file]')[0].files[0]);
		$('#version_block input[type=text]').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).val());
		});
		$('#version_block select').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).children(":selected").val());
		});
		$('#version_block input[type=checkbox]').each(function() {
			formData.append($(this).attr("id").replace("Cb",""), $(this).prop("checked"));
		});
		
		// On ajoute les infos du morceau id (bd) + label (realpath) et de la version à updater
		formData.append("morceauId",$("#morceau").attr("morceauId"));
		formData.append("morceauLabel",$("#titreInput").val());
		formData.append("versionId",$version_selected);
		
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		$.ajax({
		
			type: 'POST',
			url: "<?php echo site_url(); ?>/ajax/update_version",
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
				$version = JSON.parse(msg);
				
				// On update l'UI (changement de nom de groupe)
				if ($version.groupe != $("#version_menubar a[is_selected='true']").html())
					$("#version_menubar a[is_selected='true']").html($version.groupe);
				
				// On update la table
				update_row($version_selected);
				
				console.log("version_data1 : "+JSON.stringify($versions_data));
				// On actualise le versions_data
				$index = $("#version_menubar [is_selected='true']").index();
				console.log("INDEX : "+$index);
				$versions_data.splice($index,1,$version);
				console.log("version_data2 : "+JSON.stringify($versions_data));
				
				TINY.box.show({html:"La version a été modifiée dans la base de donnée !",boxid:'success',animate:false,width:650, closejs:function(){ }});

				
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
	

	
	function delete_version() {
		// On change le curseur
		document.body.style.cursor = 'progress';

		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/delete_version",
		
			{
			'morceauId':$("#morceau").attr("morceauId"),
			'versionId':$version_selected
			},
	
			function (msg) {
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// La version a été supprimée de la base, on reset le formulaire
				if (msg == "success") {
					
					// On récup le <a> de la version
					$a = $("#version_menubar_content a[is_selected='true']");
					
					// On actualise versions_data
					$versions_data.splice($a.index(),1);
					
					// On actualise le version menubar
					$a.remove();
					
					// Si il reste des versions
					if ($("#version_menubar_content a").length > 0) {
						// On supprime la tr de la songlist
						$("#songlist tbody tr[versionId='"+$version_selected+"']").remove();
						// On actualise le block_version
						$version_selected = $("#version_menubar_content a:first-child").attr("id");
						$("#version_menubar_content a:first-child").attr("is_selected","true");
						show_version($("#version_menubar_content a:first-child").index());
						get_medias();
					}
					// Sinon on se cale sur une création de version
					else {
						// On reset le block version
						trig_add_version();
						// On update la table => On ne supprime pas la ligne il n'y a plus de version
						$version_selected = "";
						update_row();
					}
					
					// On update la liste de documents dans le player
					update_player($("#morceau").attr("morceauId"), $version_selected);
	
					TINY.box.show({html:"La version a été supprimée de la base de donnée !",boxid:'success',animate:false,width:650, closejs:function(){}});
				}
				
				// Erreur à l'insertition de morceau
				else {
					TINY.box.show({html:"La version n'a pas été supprimée de la base de donnée !<br>"+msg,boxid:'error',animate:false,width:650, closejs:function(){  } });
				}
			}
		);

	}
	
	
	/**********************************************************************/
	/*************************    MEDIAS     ******************************/
	/**********************************************************************/	
	
	// Permet d'ouvrir la section d'ajout de version
	function trig_add_media() {
		console.log("trig_add_media");
		
		if ($("#media_block").css("display") == "none") $("#media_block").css("display","block");

		// On change la couleur du lien et on affiche le model
		if ($("#display_media_block a").hasClass("active")) {
			$("#display_media_block a").removeClass("active");
			$("#media_block #divMediaModel").css("display","none");
			if ($("#media_block div[mediaId!='']").length == 1) $("#media_block").css("display","none");
		}
		else {
			$("#display_media_block a").addClass("active");
			$("#media_block #divMediaModel").css("display","inline-flex");
		}
	}
	
	
	// On récupère les medias en fonction de la version selectionnée
	function get_medias() {
		
		console.log("get_medias");
		
		// On change le curseur
		document.body.style.cursor = 'progress';

		// On reset la création de media
		$("#display_media_block a").removeClass("active");
		$("#divMediaModel").css("display","none");
		$("#divMediaModel").find("#URLX").css("display","none");
		
		
		// On vide la liste des medias
		$("#media_block").children(":not(#divMediaModel)").remove();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_medias",
		
			{
			'versionId':$version_selected
			},
	
			function (msg) {

				// On change le curseur
				document.body.style.cursor = 'default';

				console.log("MSG******  "+msg);
				
				// PAS DE VERSION, on ouvre le add_version et on select la tr morceau
				if (msg == "medias_not_found") {
					$("#media_block").css("display","none");
					return;
				}
				
				// VERSION RECUES
				$media_data = JSON.parse(msg);
				
				// On display le media block si besoin
				if ($media_data.length > 0 && $("#media_block").css("display") == "none") $("#media_block").css("display","block");
				
				// On remplit le media block
				$.each($media_data, function($index, $value) {
					// On duplique le div du media model et on le populate
					$media = $("#divMediaModel").clone();
					$media.appendTo("#media_block");
					$media.attr("id","divMedia-"+$value.id);
					$media.attr("mediaId",$media_data[$index].id);
					$media.find("#URLInput").val($media_data[$index].URL);
					$media.find("#URLInput").attr("old_value",$media_data[$index].URL);
					$media.find("#transpoInput").val($media_data[$index].transpo);
					$media.find("#transpoInput").attr("old_value",$media_data[$index].transpo);
					$media.find("#catInput").val($media_data[$index].catId);
					$media.find("#catInput").attr("old_value",$media_data[$index].catId);
					$media.find("#instruInput").val($media_data[$index].instruId);
					$media.find("#instruInput").attr("old_value",$media_data[$index].instruId);
					$media.find("#icon #add_media").css("display","none");
					$media.find("#icon #file_valid").css("display","block");
					// Action sur le update
					$media.find("#icon #file_update").attr("href","javascript:update_media("+$media_data[$index].id+")");
					// Action sur le input pour un changement de fichier
					$media.find("#uploadInput").on("change", function() {
						console.log("CHANGE uploadInput");
						$input = $(this).parent().children("input[type='text']");
						$X = $(this).parent().parent().find("#URLX");
						// Si le champ était rempli, on supprime l'ancien fichier
						if ($input.val() != '') {
							//delete_file($input.val(), "URL");
							$(this).parent().parent().find("#icon #file_valid").css("display","none");
							$(this).parent().parent().find("#icon #file_update").css("display","block");
						}
						
						$input.val($(this).val());
						if ($input.val() == '') $("#URLX").css("display","none");
						else $X.css("display","inline-flex");
					});
					// Action sur les select => changement d'icon pour notifier le update possible
					$media.find("select").on("change", function() {
						update_icon_state($(this).parents("[id^='divMedia']").attr("mediaId"));
					});
					$media.find("input[type='text']").on("change", function() {
						update_icon_state($(this).parents("[id^='divMedia']").attr("mediaId"));
					});
					// URLX
					$URLX = $media.find("#URLX");
					$URLX.on("click", function() {
						console.log("media_selected : "+$(this).parents("div").attr("mediaId"));
						$media_selected = $(this).parents("div").attr("mediaId");
						$filename = $(this).prev().children("[type='text']").val();
						delete_file($filename, "URL");
					});
					$URLX.css("display","block");
					$media.css("display","inline-flex");
					
					console.log("DIVMEDIA : "+$media.find("#URLInput").val());
					
					// On reset le media model
					$("#divMediaModel input[type='text']").val('');
				});
			}
		);	
	}
	
	
	
	// Envoie les données du media + upload du fichier
	function add_media() {
		
		// Alert si aucun groupe n'a été identifié
		if ($("#URLInput").val() == "") {
			$alert = "<p>Pour créer un média, vous devez d'abord sélectionner un fichier de votre disque dur.</p>";
			TINY.box.show({html:$alert,boxid:'error',animate:false,width:650});
			return;
		}
		
		// Alert si on essaie d'uploader autre chose qu'un pdf
		if ($("#URLInput").val().length > 4 && $("#URLInput").val().substr(-4) != ".pdf") {
			$alert = "<p>Vous n'avez pas sélectionné de fichier valide (pdf attendu) !</p>";
			TINY.box.show({html:$alert,boxid:'error',animate:false,width:650, closejs:function() {
					$("#UploadInput").val('');
					$("#URLInput").val('');
					$("#URLX").css("display","none");
				}
			});
			return;
		}
		
		// On récupère les infos du média	
		var formData = new FormData();
		formData.append('file', $('#uploadInput')[0].files[0]);
		$('#divMediaModel input[type=text]').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).val());
		});
		$('#divMediaModel select').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).children(":selected").val());
		});
		
		
		// On ajoute les infos du morceau id (bd) + label (realpath)
		formData.append("morceauId",$("#morceau").attr("morceauId"));
		formData.append("morceauLabel",$("#titreInput").val());
		// Et les infos de la version (realpath)
		formData.append("versionId",$version_selected);
		formData.append("groupe",$("#groupInput").val());
		
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		$.ajax({
		
			type: 'POST',
			url: "<?php echo site_url(); ?>/ajax/add_media",
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
				$media_data = JSON.parse(msg);
				
				// On créé la div media
				$media = $("#divMediaModel").clone();
				$media.appendTo("#media_block");
				$media.attr("id","divMedia-"+$media_data.id);
				$media.attr("mediaId",$media_data.id);
				$media.find("#URLInput").val($media_data.URL);
				$media.find("#URLInput").attr("old_value",$media_data.URL);
				$media.find("#transpoInput").val($media_data.transpo);
				$media.find("#transpoInput").attr("old_value",$media_data.transpo);
				$media.find("#catInput").val($media_data.catId);
				$media.find("#catInput").attr("old_value",$media_data.catId);
				$media.find("#instruInput").val($media_data.instruId);
				$media.find("#instruInput").attr("old_value",$media_data.instruId);
				$media.find("#icon #add_media").css("display","none");
				$media.find("#icon #file_valid").css("display","block");
				// Action sur le update
				$media.find("#icon #file_update").attr("href","javascript:update_media("+$media_data.id+")");				
				// Action sur le input pour un changement de fichier
				$media.find("#uploadInput").on("change", function() {
					console.log("CHANGE uploadInput");
					$input = $(this).parent().children("input[type='text']");
					$X = $(this).parent().parent().find("#URLX");
					// Si le champ était rempli, on supprime l'ancien fichier
					if ($input.val() != '') {
						//delete_file($input.val(), "URL");
						$(this).parent().parent().find("#icon #file_valid").css("display","none");
						$(this).parent().parent().find("#icon #file_update").css("display","block");
					}
					
					$input.val($(this).val());
					if ($input.val() == '') $("#URLX").css("display","none");
					else $X.css("display","inline-flex");
				});
				// Action sur les select
				$media.find("select").on("change", function() {
					update_icon_state($(this).parents("[id^='divMedia']").attr("mediaId"));
				});
				$media.find("input[type='text']").on("change", function() {
					update_icon_state($(this).parents("[id^='divMedia']").attr("mediaId"));
				});
				// URLX
				$URLX = $media.find("#URLX");
				$URLX.on("click", function() {
					console.log("media_selected : "+$(this).parents("div").attr("mediaId"));
					$media_selected = $(this).parents("div").attr("mediaId");
					$filename = $(this).prev().children("[type='text']").val();
					delete_file($filename, "URL");
				});
				$URLX.css("display","block");
				$media.css("display","inline-flex");
				
				// On update la liste de documents dans le player
				update_player($("#morceau").attr("morceauId"), $version_selected);
				
				console.log("DIVMEDIA : "+$media.find("#URLInput").val());
				
				// On reset le media model
				$("#divMediaModel input[type='text']").val('');
				$("#divMediaModel").find("#URLX").css("display","none");
				$("#divMediaModel select").each( function() {
					$(this).val($(this).children(":first").val());
				});
				
				TINY.box.show({html:"Le media a été ajoutée à la base de donnée !",boxid:'success',animate:false,width:650});

				
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
	
	
	function update_media($mediaId) {
		
		console.log("****** update_media mediaId : "+$mediaId);
		
		$div = $("#media_block").find("[id='divMedia-"+$mediaId+"']");
		$URLInput = $div.find("#URLInput");
		$URLX = $div.find("#URLX");
		
		// Alert si aucun groupe n'a été identifié
		if ($URLInput.val() == "") {
			$alert = "<p>Pour créer un media, vous devez d'abord sélectionner un fichier !</p>";
			TINY.box.show({html:$alert,boxid:'error',animate:false,width:650});
			return;
		}
		
		// Alert si on essaie d'uploader autre chose qu'un pdf
		if ($URLInput.val().length > 4 && $URLInput.val().substr(-4) != ".pdf") {
			$alert = "<p>Vous n'avez pas sélectionné de fichier valide (pdf attendu) !</p>";
			TINY.box.show({html:$alert,boxid:'error',animate:false,width:650, closejs:function() {
					$URLInput.val('');
					$URLInput.val('');
					$URLX.css("display","none");
				}
			});
			return;
		}

		var formData = new FormData();
		$div.find('input[type=text]').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).val());
		});
		if ($URLInput.val() != $URLInput.attr("old_value")) {
			// On récupère les infos du média si besoin
			formData.append('file', $div.find('#uploadInput')[0].files[0]);
		}
		
		$div.find('select').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).children(":selected").val());
		});
		
		// On ajoute les infos du morceau id (bd) + label (realpath)
		formData.append("morceauId",$("#morceau").attr("morceauId"));
		formData.append("morceauLabel",$("#titreInput").val());
		// Les infos de la version (realpath)
		formData.append("versionId",$version_selected);
		formData.append("groupe",$("#groupInput").val());
		// Et le mediaId
		formData.append("mediaId",$mediaId);
		
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		$.ajax({
		
			type: 'POST',
			url: "<?php echo site_url(); ?>/ajax/update_media",
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
				$media_data = JSON.parse(msg);
				
				// On créé la div media
				$media = $div;
				$media.find("#URLInput").val($media_data.URL);
				$media.find("#transpoInput").val($media_data.transpo);
				$media.find("#catInput").val($media_data.catId);
				$media.find("#instruInput").val($media_data.instruId);
				$media.find("#icon #file_update").css("display","none");
				$media.find("#icon #file_valid").css("display","block");
				$URLX.css("display","block");
				
				console.log("DIVMEDIA : "+$media.find("#URLInput").val());
				
				TINY.box.show({html:"Le media a été modifié dans la base de donnée !",boxid:'success',animate:false,width:650});

				
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
	
	
	// Permet le populate du version_block en fonction de data_version / $index = -1 => on reset le forulaire
	function show_version($index) {
		console.log("SHOW VERSION : "+$index);
		if ($index >= 0) {
			// On affiche le version_block avec les valeurs actualisées
			$("#version_block #groupInput").val($versions_data[$index].groupe);
			$("#version_block #genreInput").val($versions_data[$index].genre);
			$("#version_block #tonaInput").val($versions_data[$index].tona);
			$("#version_block #modeInput").val($versions_data[$index].mode);
			$("#version_block #tempoInput").val($versions_data[$index].tempo);
			$("#version_block #langInput").val($versions_data[$index].langue);
			$("#version_block #mp3URLInput").val($versions_data[$index].mp3URL);
			$("#version_block #soufflantsCb").prop("checked", $versions_data[$index].soufflants == "1");
			$("#version_block #choeursCb").prop("checked", $versions_data[$index].choeurs == "1");

			
			// On update l'UI
			$("#update_version").css("display","block");	$("#version_toolbar span.separator").css("display","block");
			$("#delete_version").css("display","block");
			$("#add_version").css("display","none");
			
			// UI mp3
			if ($("#version_block #mp3URLInput").val() != '') $("#mp3URLX").css("display","initial");
			else {
				clearInputFile($("#mp3UploadInput")[0]);  // on efface le cache du l'input file
				$("#mp3URLX").css("display","none");
			}
			
			
			// On select la tr correspondant dans la songlist si besoin (pas besoin de faire select la tr si elle l'est déjà via le click songlist)
			//if ($("#songlist tbody tr.selected").attr("morceauId") != $("#morceau").attr("morceauId") ||
			//		$("#songlist tbody tr.selected").attr("versionId") != $version_selected) {
				console.log("SELECT TR version_selected : "+$version_selected);
				$("#songlist tbody tr.selected").removeClass("selected");
				$tr = $("#songlist tbody tr[versionId='"+$version_selected+"']");
				$tr.addClass("selected");
				update_player($tr.attr("morceauId"), $tr.attr("versionId"));
				$table1.trigger("updateCache");
			//}
		}
		// Index = -1   => on ajoute une version
		else {
			// On reset le version_block
			$("#version_block input[type='text']").val("");
			$("#version_block input[type='checkbox']").prop("checked",false);
			$("#version_block select").val(-1);

			// On update l'UI
			$("#update_version").css("display","none");	$("#version_toolbar span.separator").css("display","none");
			$("#delete_version").css("display","none");
			$("#add_version").css("display","block");
			$("#mp3URLX").css("display","none");
			
			// On select un tr de morceau
			$("#songlist tbody tr.selected").removeClass("selected");
			$tr = $("#songlist tbody tr[morceauId='"+$("#morceau").attr("morceauId")+"']");
			if ($tr.length == 1 && $tr.attr("versionId") == '') {
				$tr.addClass("selected");
				update_player($tr.attr("morceauId"), $tr.attr("versionId"));
				$table1.trigger("updateCache");
			}
		}
		
		// On affiche le block avec la toolbar
		$("#version_block").css("display","block");
		$("#version_toolbar").css("display","inline-flex");
	}
	
	
	// Fait un update entre le version block et la table songlist // $version == undefined => plus de version reste morceau
	function update_row($version_id) {
		console.log("UPDATE_ROW=====");
		//console.log("debug","undefined : "+(typeof $version_id == "undefined"));			
		console.log("version_id : "+$version_id);
		
		// On récup le morceau qui existe "forcément"
		$tr = $("#songlist tbody tr[morceauId='"+$("#morceau").attr("morceauId")+"']");
		
		console.log("$tr.length : "+$tr.length);
		console.log("$tr.versionId : "+$tr.attr("versionId"));
		
		// Si on passe de tr morceau à tr version
		if ($tr.attr("versionId") == '') {
			$tr.attr("versionId",$version_id);
		}
		
		// Plus de version il ne reste qu'un morceau (delete)
		else if (typeof $version_id == "undefined") {
			console.log("UNDEFINED => Pas de version select");
			$("#songlist thead th span").each( function($index) {
				switch($(this).html()) {
					case "Titre": 
						break;
					case "Compositeur":
						break;
					case "Année":
						break;
					default:
						$tr.children("td:nth-child("+($index+1)+")").empty();
				}
			});
			
			// On actualise la tr du morceau sans version
			$tr.addClass("selected");
			$tr.attr("versionId","");
			
			return;
		}
		// Il y a au moins une version déjà existante et on n'est pas en train de faire un update version donc il faut créer une nouvelle tr
		else if ($tr.length >= 1 && $version_id != $version_selected) {
			console.log("ELSE IF");
			//$tr = $tr.first().clone();
			$html = "<tr morceauId=\""+$("#morceau").attr("morceauId")+"\" versionId=\""+$version_id+"\" >"+$tr.first().html()+"</tr>";
			//console.log("HTML : "+$html);
			$table1.trigger('addRows',[$html, true]);
			$("#songlist tbody tr.selected").removeClass("selected");
			
			// On set le comportement de la nouvelle tr
			$tr = $("#songlist tbody tr[versionId='"+$version_id+"']");
			$tr.addClass("selected");
			$tr.on("click", function() {
				//console.log("NEW TR CLICK");
				// On déselectionne la tr précédente
				$(this).closest("tbody").find(".selected").removeClass("selected");
				// La tr devient selected
				$(this).addClass("selected");
			});
		}
		// On selectionne la tr correspondant à version_select pour l'update
		else {
			console.log("ELSE");
			$tr = $("#songlist tbody tr[versionId='"+$version_selected+"']");
		}

		// On fait un update dans la table
		$indexCol = $("#songlist thead th[connect='groupInput']").index()+1;
		$tr.children(":nth-child("+$indexCol+")").html($("#groupInput").val());
		$indexCol = $("#songlist thead th[connect='genreInput']").index()+1;
		$tr.children(":nth-child("+$indexCol+")").html($("#genreInput option:selected").text());
		$indexCol = $("#songlist thead th[connect='tonaInput']").index()+1;
		$tr.children(":nth-child("+$indexCol+")").html($("#tonaInput option:selected").text());
		$indexCol = $("#songlist thead th[connect='modeInput']").index()+1;
		$tr.children(":nth-child("+$indexCol+")").html($("#modeInput option:selected").text());
		$indexCol = $("#songlist thead th[connect='tempoInput']").index()+1;
		$tr.children(":nth-child("+$indexCol+")").html($("#tempoInput").val());
		$indexCol = $("#songlist thead th[connect='langInput']").index()+1;
		$tr.children(":nth-child("+$indexCol+")").html($("#langInput option:selected").text());
		// MP3
		$indexCol = $("#songlist thead th[connect='mp3URLInput']").index()+1;
		if ($("#mp3URLInput").val() != '')	$td = "<span style='display:none'>1</span><img style='height: 12' src='/images/icons/ok.png'>"
		else $td = "<span style='display:none'>0</span>";
		$tr.children(":nth-child("+$indexCol+")").html($td);

		// Choeurs
		$indexCol = $("#songlist thead th[connect='choeursCb']").index()+1;
		if ($("#choeursCb").prop("checked"))	$td = "<span style='display:none'>1</span><img style='height: 12' src='/images/icons/ok.png'>"
		else $td = "<span style='display:none'>0</span>";
		$tr.children(":nth-child("+$indexCol+")").html($td);
		// Soufflants
		$indexCol = $("#songlist thead th[connect='soufflantsCb']").index()+1;
		if ($("#soufflantsCb").prop("checked"))	$td = "<span style='display:none'>1</span><img style='height: 12' src='/images/icons/ok.png'>"
		else $td = "<span style='display:none'>0</span>";
		$tr.children(":nth-child("+$indexCol+")").html($td);
		
		//console.log("FINAL TR : "+$tr.html());
				
		$table1.trigger("updateCache");
		console.log("update END ==============");
		
		// On update le player
		update_player($tr.attr("morceauId"), $tr.attr("versionId"));
		
		// On actualise le version selected
		$version_selected = $version_id;
	}
	
	
	function update_icon_state($mediaId) {
		$div = $("#media_block #divMedia-"+$mediaId);
		$need_update = false;
		// Changement de fichier
		$need_update = $div.find("#URLInput").val() != $div.find("#URLInput").attr("old_value");
		// Changement dans les selects
		if (!$need_update) {
			$div.find("select").each( function() {
				if ($(this).val() != $(this).attr("old_value")) {
					$need_update = true;
				}
			});
		}
		
		if ($need_update) {
			$div.find("#icon #file_valid").css("display","none");
			$div.find("#icon #file_update").css("display","block");
		}
		else {
			$div.find("#icon #file_valid").css("display","block");
			$div.find("#icon #file_update").css("display","none");
		}
	}
	
	
	
	function delete_file($filename, $target) {

		console.log("****** DELETE_FILE : "+$filename+" => "+$target);

	
		// Si la version n'existe pas, c'est que le fichier n'a pas encore été uploadé
		if ($version_selected == '') {
			console.log("DELETE_FILE : la version n'existe pas ou pas de media select !!");
			// On masque l'icon suppr
			$("#"+$target+"X").css("display","none");
			// On reset le inputfile
			$("#"+$target+"Input").next().val("");
			$("#"+$target+"Input").val("");
			return;
		}

		// On change le curseur
		document.body.style.cursor = 'progress';

		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/delete_version_file",
		
			{
			'morceauId': $("#morceau").attr("morceauId"),
			'versionId': $version_selected,
			'filename': $filename
			},
	
			function (msg) {
				// On change le curseur
				document.body.style.cursor = 'default';
				console.log("MSG : "+msg);
				console.log("targetInput : "+$("#"+$target+"Input").val());
				
				// On actualise l'UI dont la songlist
				if (msg == "success") {
					if ($target == "mp3URL") {
						// On reset le inputfile
						$("#mp3URLInput").val($("#mp3UploadInput").val());
						// On masque l'icon suppr
						if ($("#mp3URLInput").val() == '') $("#mp3URLX").css("display","none");
						// On update la tr
						update_row($version_selected);
						// On update versions_data
						$versions_data[$("#version_menubar_content [is_selected='true']").index()].mp3URL = '';
					}
					// MEDIA REMOVE
					else {
						console.log("MEDIA REMOVE : "+$media_selected);
						// On remove la div du media
						$("#media_block").find("div[mediaId='"+$media_selected+"']").remove();
						// On update la liste de documents dans le player    // Pourrait être optimisé
						update_player($("#morceau").attr("morceauId"), $version_selected);
					}
				}
				// On veut supprimer un fichier qui n'existe pas => on remet juste un reset au input
				else if (msg == "file_not_found") {
					console.log("file_not_found");
					if ($("#mp3URLInput").val() == $filename) {
						// On masque l'icon suppr
						$("#mp3URLX").css("display","none");
						// On reset le inputfile
						$("#mp3URLInput").next().val("");
						$("#mp3URLInput").val("");
					}
				}
			}
		);
	}


	function clearInputFile(f){
		console.log("CLEARINPUTFILE========");
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

<div class="panel panel-default row">

	<!-- TITRE -->
	<div class="row panel-heading panel-bright title_box">
		<h4>
			<?php echo $page_title; ?>
		</h4>
	</div>

	
	<div class="row panel-heading panel-default">
	
		<!--*********************** MORCEAU ********************-->	
		<div id="morceau" morceauId="-1">
		
			<div class="form-group>
				<!-- Titre !-->
				<!--<label for="titreInput"></label>
				<input id="titreInput" size="45" list="repertoire" placeholder="Titre du morceau" autocomplete="off" >
				<datalist id="repertoire">
					<?php foreach ($list_song as $song): ?>
						<option value="<?php echo htmlentities($song->titre); ?>"><?php echo htmlentities($song->titre); ?></option>
					<?php endforeach; ?>
				</datalist> !-->
				
					<!-------- TITRE --------->
					<label for="titreInput" class="control-label"></label>
					<div class="col-sm-5">
						<input id="titreInput" class="form-control flexDL_Titre" type="text" name="titre" />
					</div>
					
					
					<!-- Compositeur -->
					<label for="compoInput"></label>
					<input id="compoInput" size="25" list="artistes" placeholder="Compositeur" autocomplete="off" old_value="">
					<datalist id="artistes">
						<?php foreach ($list_artist as $artist): ?>
							<option value="<?php echo htmlentities($artist->label); ?>"><?php echo htmlentities($artist->label); ?></option>
						<?php endforeach; ?>
					</datalist>
					
					<!-- Année !-->
					<label for="anneeInput"></label>
					<input id="anneeInput" size="5" style="text-align:center" list="years" placeholder="Année" autocomplete="off"  old_value="">
					<datalist id="years">
						<?php 
							for ($i=1900; $i <= date("Y"); $i++) {
								echo "<option value='".$i."'>".$i."</option>";
							}
						?>
					</datalist>
				</div>
			</div>

		</div>

	</div>
	
	<!------ TOOLBAR MORCEAU ------>
	<div id="morceau_toolbar" class="block_footer" style="text-align:right">
		<hr style="width:100%; margin:0;">
		<div style="display:inline-flex; justify-content:flex-end; align-items:center; height:20; margin-right:15">

			<!-- Items de droite (archives)  // Lien vers la création du morceau  !-->
			<div id="add_morceau" style="display:none">
				<a class="ui_elem action_icon soften" href="javascript:add_morceau()"><img style="vertical-align: text-bottom;" src="/images/icons/add.png" alt="add">  ajouter le morceau</a>
			</div>
			<div id="update_morceau" style="display:none">
				<a class="ui_elem action_icon soften" href="javascript:update_morceau()"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/update.png" alt="update">  modifier le morceau</a>
				
			</div>
			<span id="update_separator" class="separator soften" style="display:none"><small>&nbsp;&nbsp;|</small></span>
			<div id="delete_morceau" style="display:none; margin-left:7">
				<a class="ui_elem action_icon soften" href="javascript:delete_morceau()"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/trash.png" alt="delete">  supprimer le morceau</a>
			</div>
		</div>
		<hr style="width:100%; margin:0; padding: 0 0 2 0;">
	</div>
	
	
	<!------ VERSION MENUBAR ------>
	<div id="version_menubar" class="block_footer" style="display:none; justify-content: space-between; width:100%">
		<div id="version_menubar_content">
		</div>
		
		<div id="display_version_block" class="soften" style="display:block; margin-right:15;">
			<a href="javascript:trig_add_version()" class="ui_elem soften" style="height:23px; line-height:23px;"><img style="vertical-align: middle;" src="/images/icons/add.png" alt="add">  ajouter une version</a>
		</div>
	</div>
	
	<!-- ******************  VERSION BLOCK  ******************* !-->
	<div id="version_block" class="block" style="display:none; padding-top:15">

		<!-- Groupe -->
		<label for="groupInput"></label>
		<input id="groupInput" size="18" list="groupes" placeholder="Groupe" autocomplete="off" value="GRO" type="text">
		<datalist id="groupes">
			<?php foreach ($list_group as $group): ?>
				<option value="<?php echo ucfirst(htmlentities($group->label)); ?>"><?php echo ucfirst(htmlentities($group->label)); ?></option>
			<?php endforeach; ?>
		</datalist>
		
		<br><br>
		
		<!-- Genre -->
		<label for="genreInput" class="soften"><small>Genre</small></label>
		<select id="genreInput">
			<?php foreach ($list_style as $style): ?>
				<option value="<?php echo ucfirst($style->id); ?>"><?php echo ucfirst(htmlentities($style->label)); ?></option>
			<?php endforeach; ?>
		</select>
		
		<span class="separator soften">|</span>
		
		<!-- Tona -->
		<label for="tonaInput" class="soften"><small>Tonalité</small></label>
		<select id="tonaInput">
			<?php foreach ($list_tona as $tona): ?>
				<option value="<?php echo ucfirst($tona->id); ?>"><?php echo ucfirst(htmlentities($tona->label)); ?></option>
			<?php endforeach; ?>
		</select>
		
		<!-- Mode -->
		<label for="modeInput"></label>
		<select id="modeInput">
			<?php foreach ($list_mode as $mode): ?>
				<option value="<?php echo $mode->id; ?>"><?php echo htmlentities($mode->label); ?></option>
			<?php endforeach; ?>
		</select>
		
		<span class="separator soften">|</span>
		
		<!-- Tempo -->
		<label for="tempoInput" class="soften"><small>Tempo</small></label>
		<input id="tempoInput" size="9" type="text">
		
		<span class="separator soften">|</span>
		
		<!-- Langue -->
		<label for="langInput" class="soften"><small>Langue</small></label>
		<select id="langInput">
			<?php foreach ($list_lang as $lang): ?>
				<option value="<?php echo $lang->id; ?>"><?php echo htmlentities($lang->label); ?></option>
			<?php endforeach; ?>
		</select>
		
		<br><br>
		
		<!-- MP3 Upload -->
		<span class="fileUpload actionable">
			<label for="mp3UploadInput" class="soften"><small>MP3</small></label>
			<input id="mp3URLInput" type="text" size="45" placeholder="Selectionnez un fichier..." disabled="disabled" />
			<input id="mp3UploadInput" type="file" class="upload" accept=".mp3">
			<img style="vertical-align: text-bottom;" width="16" src="/images/icons/explore.png" alt="explore">
		</span>
		<a id="mp3URLX" class="rollOverLink"><img style="vertical-align:sub; padding-left:5; width:14;" src="/images/icons/x.png"></a>
		<span class="separator soften">|</span>
		
		<!-- Choeurs -->
		<label for="choeursCb"><img style="vertical-align: text-bottom;" width="16px" src="/images/icons/heart.png" alt="choeurs" title="choeurs"></label>
		<input id="choeursCb" style="vertical-align: bottom;" type="checkbox"/>
		
		<!-- Soufflants -->
		<label for="soufflantsCb"><img style="vertical-align: text-bottom;" width="16px" src="/images/icons/tp.png" alt="soufflants" title="soufflants"></label>
		<input id="soufflantsCb" style="vertical-align: bottom;" type="checkbox"/>
		
		<span class="separator soften">|</span>

	</div>
	
	
	<!------ TOOLBAR VERSION ------>
	<div id="version_toolbar" class="block_footer" style="display:none;">		
		
		<div id="add_version" class="soften" style="display:none">
			<a id="add_version" class="ui_elem action_icon soften" href="javascript:add_version()"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/upload.png" alt="add">  envoyer la version</a>
		</div>
		
		<div id="update_version" style="display:none">
			<a class="ui_elem action_icon soften" href="javascript:update_version()"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/update.png" alt="update">  modifier la version</a>
		</div>
		<span class="separator soften" style="display:none"><small>|</small></span>

		<div id="delete_version" style="display:none">
			<a class="ui_elem action_icon soften" href="javascript:delete_version()"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/trash.png" alt="delete">  supprimer la version</a>
		</div>

		
	</div>

	
	<!------ MEDIA MENUBAR ------>
	<div id="media_menubar" class="block_footer" style="display:none; justify-content: space-between; width:100%">
		<div id="media_menubar_content">
		</div>
		
		<div id="display_media_block" class="soften" style="display:block; margin-right:15;">
			<a href="javascript:trig_add_media()" class="ui_elem soften" style="height:23px; line-height:23px;"><img style="vertical-align: middle;" src="/images/icons/add.png" alt="add">  ajouter un media</a>
		</div>
	</div>
	
	<!-- ******************  MEDIA DIV  ******************* !-->
	<div id="media_block" class="list_media" style="display:none">
		
		<div id="divMediaModel" class="list_media_elem" style="display:none; justify-content: space-between;  align-items: center; width:96%; height: 35;">
			<!-- MEDIA Upload -->
			<span class="fileUpload actionable">
				<label for="uploadInput" class="soften"></label>
				<input id="URLInput" type="text" size="45" placeholder="Selectionnez un fichier..." disabled="disabled" />
				<input id="uploadInput" type="file" class="upload">
				<img style="vertical-align: text-bottom;" width="16" src="/images/icons/explore.png" alt="explore">
			</span>
			<a id="URLX" class="rollOverLink" style="display:none"><img style="vertical-align:sub; padding-left:5; width:14;" src="/images/icons/x.png"></a>
			
			<span class="separator soften">|</span>
			
			<!-- Transpo -->
			<label for="transpoInput" class="soften"><small>Type</small></label>
			<select id="transpoInput">
				<?php foreach ($list_transpo as $transpo): ?>
					<option value="<?php echo $transpo->id; ?>"><?php echo ucfirst(htmlentities($transpo->label)); ?></option>
				<?php endforeach; ?>
			</select>
			
			<!-- Cat -->
			<label for="catInput" class="soften"><small>Cat.</small></label>
			<select id="catInput">
				<?php foreach ($list_cat as $cat): ?>
					<option value="<?php echo $cat['id']; ?>"  <?php if ($cat['id'] == "-1") echo "selected"; ?>><?php echo htmlentities($cat['name']); ?></option>
				<?php endforeach; ?>
			</select>
			
			<!-- Instru -->
			<label for="instruInput" class="soften"><small>Instru.</small></label>
			<select id="instruInput" style="width:60">
				<?php foreach ($list_instru as $instru): ?>
					<option value="<?php echo $instru['id']; ?>"><?php echo htmlentities($instru['name']); ?></option>
				<?php endforeach; ?>
			</select>
			
			<span class="separator soften">|</span>
			
			<div id="icon" class="soften">
				<a id="add_media" class="ui_elem action_icon soften" href="javascript:add_media()"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/upload.png" alt="add"></a>
				<a id="file_valid" class="ui_elem action_icon soften" style="display:none"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/file_valid.png" alt="valid"></a>
				<a id="file_update" class="ui_elem action_icon soften" style="display:none"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/file_update.png" alt="update"></a>
			</div>
			
		</div>
		
	</div>
	
	
	
	

	<br>
	<br>
	<hr style="width:100%; margin:0;">
	
	<!--*********************** REPERTOIRE ********************-->	
	<div id="songlist_content" class="block_content">
		
		<!-- PAGER -->	
		<div class="pager form-inline">

			<div class="btn-group btn-group-sm" role="group">
			  <button type="button" class="btn btn-default first"><span class="glyphicon glyphicon-step-backward"></span></button>
			  <button type="button" class="btn btn-default prev"><span class="glyphicon glyphicon-backward"></span></button>
			</div>
			
			<span class="pagedisplay"></span> <!-- this can be any element, including an input -->
			
			<div class="btn-group btn-group-sm" role="group">
			  <button type="button" class="btn btn-default next"><span class="glyphicon glyphicon-forward"></span></button>
			  <button type="button" class="btn btn-default last"><span class="glyphicon glyphicon-step-forward"></span></button>
			</div>
			
			<select class="form-control pagesize">
				<option value="10">10</option>
				<option value="20">20</option>
				<option value="30" selected>30</option>
				<option value="40">40</option>
			</select>

		</div>
		
		<!-- NBREF -->	
		<div class="small_block_list_title soften pull-right"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($list_song); ?></span> références)</small></span></div>
		
		<!---- SONGLIST ---->		
		<table id="songlist" class="tablesorter focus-highlight is_playable" cellspacing="0">
			<thead>
				<tr>
					<th connect="titreInput" style="width:150"><span>Titre</span></th>
					<th connect="compoInput"><span>Compositeur</span></th>
					<th class="centerTD" connect="anneeInput"><span>Année</span></th>
					<th class="centerTD" connect="groupInput"><span>Groupe</span></th>
					<th class="centerTD" connect="genreInput"><span>Genre</span></th>
					<th class="centerTD" connect="tonaInput"><span>Tona</span></th>
					<th class="centerTD" connect="modeInput"><span>Mode</span></th>
					<th class="centerTD" connect="tempoInput"><span>Tempo</span></th>
					<th class="centerTD" connect="langInput"><span>Langue</span></th>
					<th class="centerTD" connect="mp3URLInput"><span>mp3</span></th>
					<th class="centerTD" connect="choeursCb" width="10px" style="text-align:center"><img style='height: 12px;' src='/images/icons/heart.png'><span></span></th>
					<th class="centerTD" connect="soufflantsCb" width="10px" style="text-align:center"><img style='height: 16px; margin:0px 2px' src='/images/icons/tp.png'><span></span></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Titre</th>
					<th>Compositeur</th>
					<th>Année</th>
					<th>Groupe</th>
					<th>Genre</th>
					<th>Tona</th>
					<th>Mode</th>
					<th>Tempo</th>
					<th>Langue</th>
					<th>mp3</th>
					<th width="10" style="text-align:center"><img style='height: 10px;' src='/images/icons/heart.png'></th>
					<th width="10" style="text-align:center"><img style='height: 14px; margin:0px 2px' src='/images/icons/tp.png'></th>
				</tr>
			</tfoot>
			<tbody id="songlist_body">
				<?php foreach ($list_song_ex as $song): ?>
					<tr morceauId="<?php echo $song->morceauId; ?>" versionId="<?php echo $song->versionId; ?>">
						<td class="song"><?php echo $song->titre; ?></td>
						<td><?php echo $song->artisteLabel; ?></td>
						<td><?php echo $song->annee; ?></td>
						<td><?php echo $song->groupe; ?></td>
						<td><?php if (isset($song->genre)) {		// les styles sont ordonnés alphabétiquement
							foreach ($list_style as $genre) {
								if ($genre->id == $song->genre) {
									echo ucfirst($genre->label);
									break;
								}
							}
						} ?></td>
						<td><?php echo ucfirst($song->tona); ?></td>
						<td><?php echo $song->mode; ?></td>
						<td><?php echo $song->tempo; ?></td>
						<td><?php echo $song->langue; ?></td>
						<td><?php if ($song->mp3URL != "") echo "<span style='display:none'>1</span><img style='height: 12px' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
						<td><?php if ($song->choeurs == 1) echo "<span style='display:none'>1</span><img style='height: 12px' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
						<td><?php if ($song->soufflants == 1) echo "<span style='display:none'>1</span><img style='height: 12px' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		
	</div>

</div>