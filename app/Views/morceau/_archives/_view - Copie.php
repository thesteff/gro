<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/jquery.tablesorter.js"></script>
<!-- <script src="../js/jquery.tablesorter.widgets.js"></script> -->
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-storage.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-filter.js"></script>

<!-- Tablesorter: pager -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/addons/pager/jquery.tablesorter.pager.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-pager.js"></script>


<script type="text/javascript">

	$(function() {
		$table1 = $( 'table' )
			.tablesorter({
				theme : 'sand',
				// Le tableau n'est pas triable
				headers: {'.titre, .artiste, .choeurs, .cuivres, .stage' : {sorter: false}
				},
				
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
		
		
		// Pour activer la selection des tr
		$(".is_playable tbody tr").on("click", function() {
			// On déselectionne la tr précédente
			$(this).closest("tbody").find(".selected").removeClass("selected");
			// La tr devient selected
			$(this).addClass("selected");
			update_player($(this).attr("morceauId"), $(this).attr("versionId"));
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
		


		// **************** Upload
		$("#uploadInput").on("change", function() {
			$("#uploadFile").val($(this).val());
		});
		
		
		// **************** Songlist // Pour updater la section morceau en cas de clic dans le tableau
		$("#songlist tbody").on("click", function(event) {
			// On récupère la tr cliquée
			$tr = $(event.target).parent();
			$("#titreInput").val($tr.children(":first-child").html());
			$version_selected = $tr.attr("versionId");
			titre_change("blur");
		});
		
		
		// Data des versions
		$versions_data = "";
		$version_selected = "";
		
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
						return;
					}
					else if ($("#add_morceau").css("display") == "none") $("#add_morceau").css("display","block");
				}
				
				// Le Morceau FOUND !
				else {
					
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
					$new_id = msg.substr(msg.indexOf("=> "),msg.length);
					$("#morceau").attr("morceauId",$new_id);
					$("#add_morceau").css("display","none");
					$("#version_toolbar").css("display","inline-flex");
					
					
					// On ajoute le morceau dans la table
					$tr = "<tr morceauId='"+$new_id+"'><td class='song'>"+$("#titreInput").val()+"</td><td>"+$("#compoInput").val()+"</td><td>"+$("#anneeInput").val()+"</td></tr>";
					$("#songlist thead tr span").each( function() {
						//alert($(this).html());
						switch($(this).html()) {
							case "Titre": alert("titre");
						}
					});
					
					$("#songlist tbody").append($tr);
					$table1.trigger('addRows',[$tr, "true"]);
					
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
				
				// Le morceau a été ajouté dans la base
				if (msg == "success") {
					$("#update_morceau").css("display","none");			$("#update_separator").css("display","none");
					$("#titreInput").attr("old_value",$("#titreInput").val());
					$("#compoInput").attr("old_value",$("#compoInput").val());
					$("#anneeInput").attr("old_value",$("#anneeInput").val());
					
					// On fait un update dans la table
					$tr = $("#songlist tbody tr[morceauId='"+$("#morceau").attr("morceauId")+"']");
					$tr.children(":nth-child(2)").html($("#compoInput").val());
					$tr.children(":nth-child(3)").html($("#anneeInput").val());
					$table1.trigger("updateCache");
					
					TINY.box.show({html:"Le morceau a été modifié dans la base de donnée !",boxid:'success',animate:false,width:650, closejs:function(){}});
				}
				
				// Erreur à l'insertition de morceau
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
					
					TINY.box.show({html:"Le morceau a été supprimé de la base de donnée !",boxid:'success',animate:false,width:650, closejs:function(){}});
				}
				
				// Erreur à l'insertition de morceau
				else {
					TINY.box.show({html:"Le morceau n'a pas été supprimé de la base de donnée !<br>"+msg,boxid:'error',animate:false,width:650, closejs:function(){  } });
				}
			}
		);

	}
	
	
	
	// Permet d'ouvrir la section d'ajout de version
	function trig_add_version() {
		
		// On change la couleur du lien et on desectionne l'item de la menu bar
		$("#display_version_block a").addClass("active");
		$("#version_menubar_content [is_selected='true']").attr("is_selected","false");
		
		// On reset le version_block
		$("#version_block input[type='text']").val("");		$("#version_block input[type='text']").attr("old_value","");
		$("#version_block input[type='checkbox']").prop("checked",false);		$("#version_block input[type='checkbox']").attr("old_value","false");
		$("#version_block select").val(-1);		$("#version_block select").attr("old_value",-1);
		$("#version_block").css("display","block");
		
		// On actualise l'affichage de la toolbar
		$("#version_toolbar").css("display","inline-flex");
		$("#update_version").css("display","none");		$("#version_toolbar span.separator").css("display","none");
		$("#delete_version").css("display","none");
		$("#add_version").css("display","block");
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

				// Si pas de version, on ouvre le add_version
				if (msg == "versions_not_found") {
					trig_add_version();
					return;
				}
				
				// Sinon on traite les versions
				$versions_data = JSON.parse(msg);
				
				// On remplit la menu bar en mettant selected le premier
				$.each($versions_data, function($index, $value) {
					if ($version_selected == "") $selected = $index == 0 ? "true" : "false";
					else $selected = $value.id == $version_selected ? "true" : "false";
					$("#version_menubar_content").append("<a id="+$value.id+" is_selected='"+$selected+"' target='_self'>"+$value.groupe+"</a>");
				});
				
				// On set le onclick event sur un titre de version (groupe)
				$("#version_menubar_content a").on("click", function() {
					// On deselectionne tout
					$version_selected = "";
					$(this).parent().children("[is_selected='true']").attr("is_selected","false");
					if ($("#display_version_block a").hasClass("active")) $("#display_version_block a").removeClass("active");

					// On selectionne le this
					$(this).attr("is_selected","true");
					show_version($(this).index());
				});
				
				// Accès normal
				if ($version_selected == "") show_version(0);
				
				// Accès par un clic dans la songList
				else show_version($("#version_menubar_content a[id='"+$version_selected+"']").index());
				
			}
		);	
	}
	
	
	// Permet le populate du version_block en fonction de data_version
	function show_version($index) {
		
		// On affiche le version_block avec les valeurs actualisées
		$("#version_block #groupInput").val($versions_data[$index].groupe);
		$("#version_block #styleInput").val($versions_data[$index].genre);
		$("#version_block #tonaInput").val($versions_data[$index].tona);
		$("#version_block #modeInput").val($versions_data[$index].mode);
		$("#version_block #tempoInput").val($versions_data[$index].tempo);
		$("#version_block #langInput").val($versions_data[$index].langue);
		$("#version_block #uploadFile").val($versions_data[$index].mp3URL);
		$("#version_block #soufflantsCb").prop("checked", $versions_data[$index].soufflants == "1");
		$("#version_block #choeursCb").prop("checked", $versions_data[$index].choeurs == "1");

		
		// On affiche le block avec la toolbar
		$("#version_block").css("display","block");
		$("#version_toolbar").css("display","inline-flex");
		$("#update_version").css("display","block");	$("#version_toolbar span.separator").css("display","block");
		$("#delete_version").css("display","block");
		$("#add_version").css("display","none");
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
		$("#version_menubar a").each(function() {
			if ($(this).html() == $("#groupInput").val()) {
				$alert = "<p>Le morceau a déjà une version liée au groupe "+$("#groupInput").val()+" !</p>";
				TINY.box.show({html:$alert,boxid:'error',animate:false,width:650, closejs:function() {$("#groupInput").select();}});
				return;
			}
		});
		
		// On récupère les infos de la version		
		/*var formData = new FormData();
		formData.append('file', $('input[type=file]')[0].files[0]);
		$('#version_block input[type=text]').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).val());
		});
		$('#version_block select').each(function() {
			formData.append($(this).attr("id").replace("Input",""), $(this).children(":selected").val());
		});
		$('#version_block input[type=checkbox]:checked').each(function() {
			formData.append($(this).attr("id").replace("Cb",""), $(this).val());
		});
		
		// On ajoute les infos du morceau id (bd) + label (realpath)
		formData.append("morceauId",$("#morceau").attr("morceauId"));
		formData.append("morceauLabel",$("#titreInput").val());
		
		$.ajax({
		
			type: 'POST',
			url: "<?php echo site_url(); ?>/ajax/add_version",
			cache: false,
			contentType: false,
			processData: false,
			data: formData,
			success: function(data){
				alert("youpi");
			},

			xhr: function() {
				var xhr = new window.XMLHttpRequest();
				//Upload progress
				xhr.upload.addEventListener("progress", function(evt){
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						//Do something with upload progress
						console.log(percentComplete);
					}
				}, false);
				//Download progress
				xhr.addEventListener("progress", function(evt){
					if (evt.lengthComputable) {
						var percentComplete = evt.loaded / evt.total;
						//Do something with download progress
						console.log(percentComplete);
					}
				}, false);
				return xhr;
			}
			
		});*/
	}


 </script>

<div class="main_block">

	<!-- TITRE -->
	<div class="block_head">
		<h3 id="manage_title" class="block_title"><?php echo $page_title; ?></h3>
		<hr>
	</div>

	
	<div class="block">
	
		<!--*********************** MORCEAU ********************-->	
		<div id="morceau" morceauId="-1">
		
			<div>
				<!-- Titre -->
				<label for="titreInput"></label>
				<input id="titreInput" size="45" list="repertoire" placeholder="Titre du morceau" autocomplete="off" >
				<datalist id="repertoire">
					<?php foreach ($list_song as $song): ?>
						<option value="<?php echo htmlentities($song->titre); ?>"><?php echo htmlentities($song->titre); ?></option>
					<?php endforeach; ?>
				</datalist>
				
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
		<label for="styleInput" class="soften"><small>Genre</small></label>
		<select id="styleInput">
			<?php foreach ($list_style as $style): ?>
				<option value="<?php echo ucfirst(htmlentities($style->id)); ?>"><?php echo ucfirst(htmlentities($style->label)); ?></option>
			<?php endforeach; ?>
		</select>
		
		<span class="separator soften">|</span>
		
		<!-- Tona -->
		<label for="tonaInput" class="soften"><small>Tonalité</small></label>
		<select id="tonaInput">
			<?php foreach ($list_tona as $tona): ?>
				<option value="<?php echo ucfirst(htmlentities($tona->id)); ?>"><?php echo ucfirst(htmlentities($tona->label)); ?></option>
			<?php endforeach; ?>
		</select>
		
		<!-- Mode -->
		<label for="modeInput"></label>
		<select id="modeInput">
			<?php foreach ($list_mode as $mode): ?>
				<option value="<?php echo htmlentities($mode->id); ?>"><?php echo htmlentities($mode->label); ?></option>
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
				<option value="<?php echo htmlentities($lang->id); ?>"><?php echo htmlentities($lang->label); ?></option>
			<?php endforeach; ?>
		</select>
		
		<br><br>
		
		<!-- MP3 Upload -->
		<span class="fileUpload">
			<label for="uploadInput" class="soften"><small>MP3</small></label>
			<input id="uploadFile" size="45" placeholder="Selectionnez un fichier..." disabled="disabled" />
			<input id="uploadInput" type="file" class="upload">
			<img style="vertical-align: text-bottom;" width="16" src="/images/icons/explore.png" alt="explore">
		</span>
		
		<span class="separator soften">|</span>
		
		<!-- Choeurs -->
		<label for="choeursCb"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/heart.png" alt="choeurs" title="choeurs"></label>
		<input id="choeursCb" style="vertical-align: bottom;" type="checkbox"/>
		
		<!-- Soufflants -->
		<label for="soufflantsCb"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/tp.png" alt="soufflants" title="soufflants"></label>
		<input id="soufflantsCb" style="vertical-align: bottom;" type="checkbox"/>
		
		<span class="separator soften">|</span>
		

	</div>
	
	<!------ TOOLBAR VERSION ------>
	<div id="version_toolbar" class="block_footer" style="display:none;">		
		
		<div id="add_version" class="soften" style="display:none">
			<a id="add_version" class="ui_elem action_icon soften" href="javascript:add_version()"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/upload.png" alt="update">  envoyer la version</a>
		</div>
		
		<div id="update_version" style="display:none">
			<a class="ui_elem action_icon soften" href="javascript:update_version()"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/update.png" alt="update">  modifier la version</a>
			
		</div>
		<span class="separator soften" style="display:none"><small>|</small></span>

		<div id="delete_version" style="display:none">
			<a class="ui_elem action_icon soften" href="javascript:delete_version()"><img style="vertical-align: text-bottom;" width="16" src="/images/icons/trash.png" alt="delete">  supprimer la version</a>
		</div>

		
	</div>


	<br>
	<br>
	<hr style="width:100%; margin:0;">
	
	<!--*********************** REPERTOIRE ********************-->	
	<div id="songlist_content" class="block_content">
		
		<!-- PAGER -->	
		<div class="pager">
			<small>
			<select class="pagesize">
				<option value="10">10</option>
				<option value="20">20</option>
				<option value="30" selected>30</option>
				<option value="40">40</option>
			</select>
			&nbsp;&nbsp;&nbsp;
			<img src="/ressources/tablesorter-master/addons/pager/icons/first.png" class="first" alt="First" title="First page" />
			<img src="/ressources/tablesorter-master/addons/pager/icons/prev.png" class="prev" alt="Prev" title="Previous page" />
			<span class="pagedisplay"></span> <!-- this can be any element, including an input -->
			<img src="/ressources/tablesorter-master/addons/pager/icons/next.png" class="next" alt="Next" title="Next page" />
			<img src="/ressources/tablesorter-master/addons/pager/icons/last.png" class="last" alt="Last" title= "Last page" />
			</small>
		</div>
		
		<!-- NBREF -->	
		<div class="small_block_list_title soften right"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($list_song); ?></span> références)</small></span></div>
		
		<!---- SONGLIST ---->		
		<table id="songlist" class="tablesorter focus-highlight is_playable" cellspacing="0">
			<thead>
				<tr>
					<th><span>Titre</span></th>
					<th><span>Compositeur</span></th>
					<th><span>Année</span></th>
					<th><span>Groupe</span></th>
					<th><span>Genre</span></th>
					<th><span>Tona</span></th>
					<th><span>Mode</span></th>
					<th><span>Tempo</span></th>
					<th><span>Langue</span></th>
					<th><span>mp3</span></th>
					<th width="10" style="text-align:center"><img style='height: 12;' src='/images/icons/heart.png'></th>
					<th width="10" style="text-align:center"><img style='height: 16; margin:0 2' src='/images/icons/tp.png'></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><span>Titre</span></th>
					<th><span>Compositeur</span></th>
					<th><span>Année</span></th>
					<th><span>Groupe</span></th>
					<th><span>Genre</span></th>
					<th><span>Tona</span></th>
					<th><span>Mode</span></th>
					<th><span>Tempo</span></th>
					<th><span>Langue</span></th>
					<th><span>mp3</span></th>
					<th width="10" style="text-align:center"><img style='height: 10;' src='/images/icons/heart.png'></th>
					<th width="10" style="text-align:center"><img style='height: 14; margin:0 2' src='/images/icons/tp.png'></th>
				</tr>
			</tfoot>
			<tbody id="songlist_body">
				<?php foreach ($list_song_ex as $song): ?>
					<tr morceauId="<?php echo $song->morceauId; ?>" versionId="<?php echo $song->versionId; ?>">
						<td class="song"><?php echo $song->titre; ?></td>
						<td><?php echo $this->artists_model->get_artist_label($song->artisteId); ?></td>
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
						<td><?php if (isset($song->tona) && $song->tona > 0) echo strtoupper($list_tona[$song->tona-1]->label); ?></td>
						<td><?php if (isset($song->mode) && $song->mode > 0) echo $list_mode[$song->mode-1]->label; ?></td>
						<td><?php echo $song->tempo; ?></td>
						<td><?php if (isset($song->langue) && $song->langue > 0) echo $list_lang[$song->langue-1]->label; ?></td>
						<td style="text-align: center"><?php if ($song->mp3URL != "") echo "<span style='display:none'>1</span><img style='height: 12' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
						<td style="text-align: center"><?php if ($song->choeurs == 1) echo "<span style='display:none'>1</span><img style='height: 12' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
						<td style="text-align: center"><?php if ($song->soufflants == 1) echo "<span style='display:none'>1</span><img style='height: 12' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		
	</div>

</div>