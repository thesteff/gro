
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

		// On active les handlers pour le player
		song_update();
		
		// On fait un get_playlist si on ne visualise pas la page de page (idPlaylist dans l'URL)
		temp = window.location.href.split("/");
		if (temp[temp.length-1] !== "playlist") get_playlist();
		
		
		// On stylise les colonnes
		$("#songlist .centerTD").each(function() {
			$(this).css("text-align","center");
			$(this).parents("table").find("tbody tr td:nth-child("+($(this).index()+1)+")").css("text-align","center");
		});
		
	});

	
	// Permet de fixer le comportement des titre de morceaux
	function song_update() {
		$(".is_playable tbody tr[idSong!='-1']").on("click", function() {
			// On déselectionne la tr précédente
			$(this).closest("tbody").find(".selected").removeClass("selected");
			// La tr devient selected
			$(this).addClass("selected");
			update_player($(this).attr("idSong"));
		});
		
		// On surcharge le css pour les pause
		$(".is_playable tbody tr[idSong='-1'] > td").css("background-color","#dddddd");		
	}
	
	
	// Récupère une playlist sur le serveur
	function get_playlist() {
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/get_playlist",
		
			{'idPlaylist':$("#select_playlist").val()},
		
			function (msg) {
			
				// On vide la liste actuellement affichée
				$("#songlist_body").empty();
				
				// On rempli le tableau avec les nouvelles valeurs
				$playlist = JSON.parse(msg);
				$.each($playlist.list,function(index) {
					if ($playlist.list[index].idSong != -1) {
						mark = "<span style='display:none'>1</span><img style='height: 12;' src='/images/icons/ok.png'>";
						empty = "<span style='display:none'>0</span>";
						if ($playlist.list[index].choeurs == 1) choeurs = mark; else choeurs="";
						if ($playlist.list[index].cuivres == 1) cuivres = mark; else cuivres="";
						if ($("#select_playlist").val() >= 0 && $playlist.list[index].reserve_stage == 1) stage = mark; else stage="";  // Si stage
						
						$("#songlist_body").append("<tr idSong="+$playlist.list[index].idSong+"><td>"+$playlist.list[index].titre+"</td><td>"+$playlist.list[index].label+"</td><td style='text-align: center'>"+choeurs+"</td><td style='text-align: center'>"+cuivres+"</td><td style='text-align: center'>"+stage+"</td></tr>");
					}
					// On gère les pauses
					else $("#songlist_body").append("<tr idSong='"+$playlist.list[index].idSong+"'><td colspan='"+$("#songlist th").children().length+"'>-= <i>pause</i> =-</td></tr>");
				});

				// On actualise le cache du tableau
				$("#songlist").trigger("update");
				
				// On actualise les handlers pour le player
				song_update();
				
				// On compte les pauses
				nb_break = $("#songlist_body [idSong='-1']").length;
				
				// On actualise le nombre de ref affichées
				$("#nbRef").empty();
				$("#nbRef").append($playlist.list.length - nb_break);
				
				
				// On actualise l'affichage du file_block
				update_file_block();
				
				// On actualise l'affichage de la tool bar
				$("#left_tool_bar").css("display",$("#select_playlist").val() == -1?"none":"block");

			}
		);

    }
	

	
	// Popup de confirmation de suppression de playlist
	function popup_confirm() {

		// POPUP Confirm
		$confirm = "<p>Etes-vous sûr de voulour supprimer la playlist \"<b>"+$("#select_playlist").find(":selected").html()+"</b>\" et les fichiers associés ?</p>";
		$confirm += "<p style='text-align:center'><input type='button' value='supprimer' onclick='javascript:delete_playlist()'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()'></p>";
		TINY.box.show({html:$confirm,boxid:'confirm',animate:false,width:650});
    }
	
	
	
	function delete_playlist() {

		TINY.box.hide();
		$id_item_selected = $("#select_playlist").find(":selected").val();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/delete_playlist",
		
			{'idPlaylist':$id_item_selected},
		
			function (msg) {
			
				if (msg != "success") TINY.box.show({html:msg,boxid:'error',animate:false,width:650});
				else {
					// Message de succés // On supprime la playlist dans l'UI et on réaffiche le répertoire du gro
					TINY.box.show({html:"Playlist supprimée !",boxid:'success',animate:false,width:650, closejs:function(){$("#select_playlist").find(":selected").remove();get_playlist();}});
				}
			}
		);
	}
	
	
	function update_playlist() {
		//alert($("#select_playlist :selected").val());
		window.location.href = "<?php echo site_url(); ?>/playlist/update/"+$("#select_playlist :selected").val();
	}

	
	function update_file_block() {
	
		// On actualise le file_block
		if ($("#select_playlist").val() != -1) {
			
			// ZIP
			$("#zipmp3 span:first-child").empty();
			if ($playlist.infos.zipmp3URL == null || $playlist.infos.zipmp3URL == "") {
				$("#zipmp3 span:first-child").removeClass('numbers');
				$("#zipmp3 span:first-child").addClass('line_alert');
				$("#zipmp3 span:first-child").css('font-weight','normal');
				$("#zipmp3 span:first-child").append("Le fichier <b>zip</b> principal n'existe pas ou a été effacé.");
				$("#zipmp3 .ui_elem").css("display","inline");
				$("#zipmp3 #file_size").css("display","none");
			}
			else {
				// On actulise le nom de fichier
				$("#zipmp3 span:first-child").removeClass('line_alert');
				$("#zipmp3 span:first-child").addClass('numbers');
				$("#zipmp3 span:first-child").css('font-weight','bold');
				$("#zipmp3 span:first-child").append("<a class='actionable' href='<?php echo base_url() ?>ressources/jam/"+$playlist.infos.zipmp3URL+"' target='_blanck'>"+$playlist.infos.zipmp3URL+"</a>");
				// On ajoute le suppr_icon
				$("#zipmp3 span:first-child").append('<a class="rollOverLink" href="javascript:suppr_playlist_file(\'zip\')"><img style="vertical-align:sub; padding-left:10; width:14;" src="/images/icons/x.png"></a>');
				// On actualise le file_size
				$("#zipmp3 #file_size").empty();
				set_file_size("ressources/jam/"+$playlist.infos.zipmp3URL,"#zipmp3");
				
				$("#zipmp3 .ui_elem").css("display","none");
			}
			
			// PDF
			$("#pdf span:first-child").empty();
			if ($playlist.infos.pdfURL == null || $playlist.infos.pdfURL == "") {
				//alert("toto");
				$("#pdf span:first-child").removeClass('numbers');
				$("#pdf span:first-child").addClass('line_alert');
				$("#pdf span:first-child").css('font-weight','normal');
				$("#pdf span:first-child").append("Le fichier <b>pdf</b> principal n'existe pas ou a été effacé.");
				$("#pdf .ui_elem").css("display","inline");
				$("#pdf #file_size").css("display","none");
			}
			else {
				// On actulise le nom de fichier
				$("#pdf span:first-child").removeClass('line_alert');
				$("#pdf span:first-child").addClass('numbers');
				$("#pdf span:first-child").css('font-weight','bold');
				$("#pdf span:first-child").append("<a class='actionable' href='<?php echo base_url() ?>ressources/jam/"+$playlist.infos.pdfURL+"' target='_blanck'>"+$playlist.infos.pdfURL+"</a>");
				// On ajoute le suppr_icon
				$("#pdf span:first-child").append('<a class="rollOverLink" href="javascript:suppr_playlist_file(\'pdf\')"><img class="x" style="vertical-align:sub; padding-left:10; width:14;" src="/images/icons/x.png"></a>');
				// On actualise le file_size
				$("#pdf #file_size").empty();
				set_file_size("ressources/jam/"+$playlist.infos.pdfURL,"#pdf");
				
				$("#pdf .ui_elem").css("display","none");
			}
		}
		
		// On actualise l'affichage du file_block
		$("#file_block").css("display",$("#select_playlist").val() == -1?"none":"flex");
	
	}
	
	
	// POPUP Tri alphabétique
	function popup_generate($file_type) {	
		$confirm = "<p>Quel tri voulez-vous utiliser pour le pdf général ?</p>";
		$confirm += "<p style='text-align:center'><input type='button' value='tri alphabétique' onclick='TINY.box.hide(); generate_playlist_file(\""+$file_type+"\",1)'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='pas de tri' onclick='TINY.box.hide(); generate_playlist_file(\""+$file_type+"\",0)'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()'></p>";
		TINY.box.show({html:$confirm,boxid:'confirm',animate:false,width:650});

	}
	
	
	function generate_playlist_file($file_type, $alpha) {
	
		if (typeof $alpha == 'undefined') $alpha = 0;

		// On récupère l'id de la playlist
		$id_item_selected = $("#select_playlist").find(":selected").val();
		
		$target ="";
		if ($file_type == "zip") $target = "#zipmp3";
		else if ($file_type == "pdf") $target = "#pdf";
		
		// On actualise l'affichage avec l'icone d'attente et curseur d'attente
		$($target+" .ui_elem").css("display","none");
		$($target+" #wait_block").css("display","block");
		$("body").addClass("wait");
		
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/generate_playlist_file",
		
			{
			'idPlaylist':$id_item_selected,
			'file_type':$file_type,
			'alpha':$alpha
			},
		
			function (msg) {

			// On masque le wait_block et on rétablit le pointeur
			$($target+" #wait_block").css("display","none");
			$("body").removeClass("wait");

				if (msg == "error") TINY.box.show({html:"Le fichier n'a pas pu être généré !",boxid:'error',animate:false,width:650});
				else {
					TINY.box.show({html:msg,boxid:'error',animate:false,width:650});
					// On actualise le file_block
					$file_infos = JSON.parse(msg);
					if ($file_type == "zip") $playlist.infos.zipmp3URL = $file_infos.name;
					else if ($file_type == "pdf") $playlist.infos.pdfURL = $file_infos.name;
					update_file_block();
				
					// Message de succés
					TINY.box.show({html:"Le fichier "+$file_type+" a été généré avec succès !",boxid:'success',animate:false,width:650, closejs:function(){ 
					}});
				}
			}
		);
	}
	
	
	function suppr_playlist_file($file_type) {
	
		// On récupère l'id de la playlist
		$id_item_selected = $("#select_playlist").find(":selected").val();

		// On change le curseur
		document.body.style.cursor = 'progress';
	
		if ($file_type == "zip" || $file_type == "pdf") {
			// Requète ajax au serveur
			$.post("<?php echo site_url(); ?>/Ajax/delete_playlist_file",
			
				{
				'idPlaylist':$id_item_selected,
				'file_type':$file_type
				},
			
				function (msg) {

				// On rétablit le pointeur
				document.body.style.cursor = 'default';

					if (msg == false) TINY.box.show({html:"Le fichier n'a pas pu être effacé !",boxid:'error',animate:false,width:650});
					else {
						//TINY.box.show({html:msg,boxid:'error',animate:false,width:650});
						// On actualise le file_block
						$file_infos = JSON.parse(msg);
						if ($file_type == "zip") $playlist.infos.zipmp3URL = $file_infos.name;
						else if ($file_type == "pdf") $playlist.infos.pdfURL = $file_infos.name;
						update_file_block();
					
						// Message de succés
						TINY.box.show({html:"Le fichier "+$file_type+" a été effacé avec succès !",boxid:'success',animate:false,width:650, closejs:function(){ 
						}});
					}
				}
			);
		}
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

<div class="main_block">

	<!-- TITRE -->
	<div class="block_head">
		<h3 id="manage_title" class="block_title"><?php echo $page_title; ?></h3>
		<hr>
	</div>

	

	<div id="songlist_content" class="block_content">
		
		<!-- SELECT playlist + nb ref -->
		<div>
			<select id="select_playlist" name="select_playlist" onchange="get_playlist()">
				<option value="-1">GRO</option>
				<?php foreach ($playlists as $list): ?>
					<option value="<?php echo $list['id']; ?>" <?php if ($idPlaylist == $list['id']) echo "selected"; ?>><?php echo $list['title']; ?></option>
				<?php endforeach ?>
			</select>
			
			
			<!-- TOOL BAR -->
			<div class="submenu_block list_menu" style="height:30; position:relative;">	
				<!-- Items de gauche !-->
				<div id="left_tool_bar" class="list_bar soften" style="display:<?php if ($idPlaylist == -1) echo "none"; else echo "block"; ?>">
					<a href="javascript:update_playlist()" class="ui_elem action_icon soften"><img class="action_icon" style="height: 20; vertical-align:middle;" src="/images/icons/edit.png" alt="Modifier"> modifier</a>
					|
					<a href="javascript:popup_confirm()" class="ui_elem action_icon soften"><img style="height: 20; vertical-align:middle;" src="/images/icons/suppr.png" alt="repert"> supprimer</a>
				</div>
				<!-- Items de droite (archives) !-->
				<div class="right_list_bar">
				<?php // Lien vers la création d'une playlist !
						echo '<a class="ui_elem action_icon soften" href="'.site_url().'/playlist/create"><img style="vertical-align: text-bottom;" src="/images/icons/add.png" alt="add">  ajouter une playlist</a>';
					?>
				</div>
			</div>
		
		</div>
		
		<br>
		
		
		<!----- FILE BLOCK ----->
		<div id="file_block" style="display:none; flex-direction:row-reverse">
			<div>
				<div class="small_block_list_title soften">Fichiers principaux</div>
				<div class="small_block_info file_list" style="text-align:left; margin:inherit;">
					<ul>
						<li id="zipmp3">
							<!-- Nom de fichier ou texte -->
							<span></span>
							<!-- Taille fichier -->
							<small><span id="file_size" class="numbers soften"></span></small>
							<!-- Générate -->
							<a href="javascript:generate_playlist_file('zip')" class="ui_elem action_icon soften" style="width:85">
								<img class="action_icon" style="height: 12; vertical-align:middle;" src="/images/icons/gear.png" alt="Générer"> générer zip
							</a>
							<!-- Wait block -->
							<div id="wait_block" style="display:none"><img class="action_icon" style="height: 14; vertical-align:middle; margin-right:5;" src="/images/icons/wait.gif"><small>création du zip...</small></div>
						</li>
						<li id="pdf">
							<!-- Nom de fichier ou texte -->
							<span></span>
							<!-- Taille fichier -->
							<small><span id="file_size" class="numbers soften"></span></small>
							<!-- Générate -->
							<a href="javascript:popup_generate('pdf')" class="ui_elem action_icon soften" style="width:85">
								<img class="action_icon" style="height: 12; vertical-align:middle;" src="/images/icons/gear.png" alt="Générer"> générer pdf
							</a>
							<!-- Wait block -->
							<div id="wait_block" style="display:none"><img class="action_icon" style="height: 14; vertical-align:middle; margin-right:5;" src="/images/icons/wait.gif"><small>création du pdf...</small></div>
						</li>
					</ul>
				</div>
			</div>
		</div>
		
		
		
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
		<div class="small_block_list_title soften right"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($songlist); ?></span> références)</small></span></div>
		
		<!---- SONGLIST ---->		
		<table id="songlist" class="tablesorter focus-highlight is_playable" cellspacing="0">
			<thead>
				<tr>
					<th><span class="titre">Titre</span></th>
					<th><span class="artiste">Artiste</span></th>
					<th class="centerTD" width="10"><span class="choeurs"><img style='height: 12;' src='/images/icons/heart.png'></span></th>
					<th class="centerTD" width="10"><span class="cuivres"><img style='height: 16; margin:0 2' src='/images/icons/tp.png'></span></th>
					<th class="centerTD" width="10"><span class="stage"><img style='height: 16;' src='/images/icons/metro.png'></span></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Titre</th>
					<th>Artiste</th>
					<th><img style='height: 10;' src='/images/icons/heart.png'></th>
					<th><img style='height: 14; margin:0 2' src='/images/icons/tp.png'></th>
					<th width="10"><img style='height: 14; margin:0 2' src='/images/icons/metro.png'><span class="stage"></span></th>
				</tr>
			</tfoot>
			<tbody id="songlist_body">
				<?php if ($idPlaylist == -1) :?>				
					<?php foreach ($songlist as $song): ?>
						<tr idSong="<?php echo $song->idSong; ?>">
							<td class="song"><?php echo $song->titre; ?></td>
							<td><?php echo $this->artists_model->get_artist_label($song->idArtiste); ?></td>
							<td><?php if ($song->choeurs == 1) echo "<span style='display:none'>1</span><img style='height: 12;' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
							<td><?php if ($song->cuivres == 1) echo "<span style='display:none'>1</span><img style='height: 12;' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
							<td></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		
	</div>

</div>