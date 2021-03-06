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
				headers: {'.no_sort' : {sorter: false}
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
			
		// On stylise les colonnes
		$("#songlist .centerTD").each(function() {
			$(this).css("text-align","center");
			$(this).parents("table").find("tbody tr td:nth-child("+($(this).index()+1)+")").css("text-align","center");
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
			// On d??selectionne la tr pr??c??dente
			$(this).closest("tbody").find(".selected").removeClass("selected");
			// La tr devient selected
			$(this).addClass("selected");
			update_player($(this).attr("morceauId"), $(this).attr("versionId"));
		});
		
		// On surcharge le css pour les pause
		$(".is_playable tbody tr[idSong='-1'] > td").css("background-color","#dddddd");		
	}
	
	
	// R??cup??re une playlist sur le serveur
	function get_playlist() {
	
		// Requ??te ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/get_playlist",
		
			{'idPlaylist':$("#select_playlist").val()},
		
			function (msg) {
			
				// On vide la liste actuellement affich??e
				$("#songlist_body").empty();
				
				// On rempli le tableau avec les nouvelles valeurs
				$playlist = JSON.parse(msg);
				$.each($playlist.list,function(index) {
					if ($playlist.list[index].versionId != -1) {
						mark = "<span style='display:none'>1</span><img style='height: 12;' src='/images/icons/ok.png'>";
						empty = "<span style='display:none'>0</span>";
						if ($playlist.list[index].choeurs == 1) choeurs = mark; else choeurs="";
						if ($playlist.list[index].soufflants == 1) soufflants = mark; else soufflants="";
						if ($("#select_playlist").val() >= 0 && $playlist.list[index].reserve_stage == 1) stage = mark; else stage="";  // Si stage
						
						$("#songlist_body").append("<tr morceauId="+$playlist.list[index].morceauId+" versionId="+$playlist.list[index].versionId+"><td>"+$playlist.list[index].titre+"</td><td>"+$playlist.list[index].artisteLabel+"</td><td>"+$playlist.list[index].annee+"</td><td>"+$playlist.list[index].tona+"</td><td>"+$playlist.list[index].mode+"</td><td>"+$playlist.list[index].tempo+"</td><td>"+$playlist.list[index].langue+"</td><td style='text-align: center'>"+choeurs+"</td><td style='text-align: center'>"+soufflants+"</td><td style='text-align: center'>"+stage+"</td></tr>");
					}
					// On g??re les pauses
					else $("#songlist_body").append("<tr morceauId="+$playlist.list[index].morceauId+" versionId='"+$playlist.list[index].versionId+"'><td colspan='"+$("#songlist th").children().length+"'>-= <i>pause</i> =-</td></tr>");
				});

				// On actualise le cache du tableau
				$("#songlist").trigger("update");
				
				// On actualise les handlers pour le player
				song_update();
				
				// On compte les pauses
				nb_break = $("#songlist_body [idSong='-1']").length;
				
				// On actualise le nombre de ref affich??es
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
		$confirm = "<p>Etes-vous s??r de voulour supprimer la playlist \"<b>"+$("#select_playlist").find(":selected").html()+"</b>\" et les fichiers associ??s ?</p>";
		$confirm += "<p style='text-align:center'><input type='button' value='supprimer' onclick='javascript:delete_playlist()'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()'></p>";
		TINY.box.show({html:$confirm,boxid:'confirm',animate:false,width:650});
    }
	
	
	
	function delete_playlist() {

		TINY.box.hide();
		$id_item_selected = $("#select_playlist").find(":selected").val();
		
		// Requ??te ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/delete_playlist",
		
			{'idPlaylist':$id_item_selected},
		
			function (msg) {
			
				if (msg != "success") TINY.box.show({html:msg,boxid:'error',animate:false,width:650});
				else {
					// Message de succ??s // On supprime la playlist dans l'UI et on r??affiche le r??pertoire du gro
					TINY.box.show({html:"Playlist supprim??e !",boxid:'success',animate:false,width:650, closejs:function(){$("#select_playlist").find(":selected").remove();get_playlist();}});
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
				$("#zipmp3 span:first-child").append("Le fichier <b>zip</b> principal n'existe pas ou a ??t?? effac??.");
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
				$("#pdf span:first-child").append("Le fichier <b>pdf</b> principal n'existe pas ou a ??t?? effac??.");
				$("#pdf .ui_elem").css("display","inline");
				$("#pdf #file_size").css("display","none");
			}
			else {
				// On actualise le nom de fichier
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
	
	
	// POPUP Tri alphab??tique
	function popup_generate($file_type) {	
		$confirm = "<p>Quel tri voulez-vous utiliser pour le pdf g??n??ral ?</p>";
		$confirm += "<p style='text-align:center'><input type='button' value='tri alphab??tique' onclick='TINY.box.hide(); generate_playlist_file(\""+$file_type+"\",1)'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='pas de tri' onclick='TINY.box.hide(); generate_playlist_file(\""+$file_type+"\",0)'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()'></p>";
		TINY.box.show({html:$confirm,boxid:'confirm',animate:false,width:650});

	}
	
	
	function generate_playlist_file($file_type, $alpha) {
	
		if (typeof $alpha == 'undefined') $alpha = 0;

		// On r??cup??re l'id de la playlist
		$id_item_selected = $("#select_playlist").find(":selected").val();
		
		$target ="";
		if ($file_type == "zip") $target = "#zipmp3";
		else if ($file_type == "pdf") $target = "#pdf";
		
		// On actualise l'affichage avec l'icone d'attente et curseur d'attente
		$($target+" .ui_elem").css("display","none");
		$($target+" #wait_block").css("display","block");
		$("body").addClass("wait");
		
		
		// Requ??te ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/generate_playlist_file",
		
			{
			'idPlaylist':$id_item_selected,
			'file_type':$file_type,
			'alpha':$alpha
			},
		
			function (msg) {

			// On masque le wait_block et on r??tablit le pointeur
			$($target+" #wait_block").css("display","none");
			$("body").removeClass("wait");

				if (msg == "error") TINY.box.show({html:"Le fichier n'a pas pu ??tre g??n??r?? !",boxid:'error',animate:false,width:650});
				else {
					TINY.box.show({html:msg,boxid:'error',animate:false,width:650});
					// On actualise le file_block
					$file_infos = JSON.parse(msg);
					if ($file_type == "zip") $playlist.infos.zipmp3URL = $file_infos.name;
					else if ($file_type == "pdf") $playlist.infos.pdfURL = $file_infos.name;
					update_file_block();
				
					// Message de succ??s
					TINY.box.show({html:"Le fichier "+$file_type+" a ??t?? g??n??r?? avec succ??s !",boxid:'success',animate:false,width:650, closejs:function(){ 
					}});
				}
			}
		);
	}
	
	
	function suppr_playlist_file($file_type) {
	
		// On r??cup??re l'id de la playlist
		$id_item_selected = $("#select_playlist").find(":selected").val();

		// On change le curseur
		document.body.style.cursor = 'progress';
	
		if ($file_type == "zip" || $file_type == "pdf") {
			// Requ??te ajax au serveur
			$.post("<?php echo site_url(); ?>/Ajax/delete_playlist_file",
			
				{
				'idPlaylist':$id_item_selected,
				'file_type':$file_type
				},
			
				function (msg) {

				// On r??tablit le pointeur
				document.body.style.cursor = 'default';

					if (msg == false) TINY.box.show({html:"Le fichier n'a pas pu ??tre effac?? !",boxid:'error',animate:false,width:650});
					else {
						//TINY.box.show({html:msg,boxid:'error',animate:false,width:650});
						// On actualise le file_block
						$file_infos = JSON.parse(msg);
						if ($file_type == "zip") $playlist.infos.zipmp3URL = $file_infos.name;
						else if ($file_type == "pdf") $playlist.infos.pdfURL = $file_infos.name;
						update_file_block();
					
						// Message de succ??s
						TINY.box.show({html:"Le fichier "+$file_type+" a ??t?? effac?? avec succ??s !",boxid:'success',animate:false,width:650, closejs:function(){ 
						}});
					}
				}
			);
		}
	}
	
	// Actualise target avec le file size de path
	function set_file_size($path,$target) {
		// Requ??te ajax au serveur
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
				<?php // Lien vers la cr??ation d'une playlist !
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
							<!-- G??n??rate -->
							<a href="javascript:generate_playlist_file('zip')" class="ui_elem action_icon soften" style="width:85">
								<img class="action_icon" style="height: 12; vertical-align:middle;" src="/images/icons/gear.png" alt="G??n??rer"> g??n??rer zip
							</a>
							<!-- Wait block -->
							<div id="wait_block" style="display:none"><img class="action_icon" style="height: 14; vertical-align:middle; margin-right:5;" src="/images/icons/wait.gif"><small>cr??ation du zip...</small></div>
						</li>
						<li id="pdf">
							<!-- Nom de fichier ou texte -->
							<span></span>
							<!-- Taille fichier -->
							<small><span id="file_size" class="numbers soften"></span></small>
							<!-- G??n??rate -->
							<a href="javascript:popup_generate('pdf')" class="ui_elem action_icon soften" style="width:85">
								<img class="action_icon" style="height: 12; vertical-align:middle;" src="/images/icons/gear.png" alt="G??n??rer"> g??n??rer pdf
							</a>
							<!-- Wait block -->
							<div id="wait_block" style="display:none"><img class="action_icon" style="height: 14; vertical-align:middle; margin-right:5;" src="/images/icons/wait.gif"><small>cr??ation du pdf...</small></div>
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
		<div class="small_block_list_title soften right"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($list_song_ex); ?></span> r??f??rences)</small></span></div>
		
		<!---- SONGLIST ---->		
		<table id="songlist" class="tablesorter focus-highlight is_playable" cellspacing="0">
			<thead>
				<tr>
					<th class="no_sort" connect="titreInput" style="width:150"><span>Titre</span></th>
					<th class="no_sort" connect="compoInput"><span>Compositeur</span></th>
					<th class="no_sort centerTD" connect="anneeInput"><span>Ann??e</span></th>
					<th class="no_sort centerTD" connect="tonaInput"><span>Tona</span></th>
					<th class="no_sort centerTD" connect="modeInput"><span>Mode</span></th>
					<th class="no_sort centerTD" connect="tempoInput"><span>Tempo</span></th>
					<th class="no_sort centerTD" connect="langInput"><span>Langue</span></th>
					<th class="no_sort centerTD" connect="choeursCb" width="10" style="text-align:center"><img style='height: 12;' src='/images/icons/heart.png'><span></span></th>
					<th class="no_sort centerTD" connect="soufflantsCb" width="10" style="text-align:center"><img style='height: 16; margin:0 2' src='/images/icons/tp.png'><span></span></th>
					<th class="no_sort centerTD" width="10"><span class="stage"><img style='height: 16;' src='/images/icons/metro.png'></span></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Titre</th>
					<th>Compositeur</th>
					<th>Ann??e</th>
					<th>Tona</th>
					<th>Mode</th>
					<th>Tempo</th>
					<th>Langue</th>
					<th width="10" style="text-align:center"><img style='height: 10;' src='/images/icons/heart.png'></th>
					<th width="10" style="text-align:center"><img style='height: 14; margin:0 2' src='/images/icons/tp.png'></th>
					<th width="10"><img style='height: 14; margin:0 2' src='/images/icons/metro.png'><span class="stage"></span></th>
				</tr>
			</tfoot>
			<tbody id="songlist_body">
				<?php if ($idPlaylist == -1) :?>	
					<?php foreach ($list_song_ex as $song): ?>
						<tr morceauId="<?php echo $song->morceauId; ?>" versionId="<?php echo $song->versionId; ?>">
							<td class="song"><?php echo $song->titre; ?></td>
							<td><?php echo $this->artists_model->get_artist_label($song->artisteId); ?></td>
							<td><?php echo $song->annee; ?></td>
							<td><?php if (isset($song->tona) && $song->tona > 0) echo strtoupper($list_tona[$song->tona-1]->label); ?></td>
							<td><?php if (isset($song->mode) && $song->mode > 0) echo $list_mode[$song->mode-1]->label; ?></td>
							<td><?php echo $song->tempo; ?></td>
							<td><?php if (isset($song->langue) && $song->langue > 0) echo $list_lang[$song->langue]->label; ?></td>
							<td><?php if ($song->choeurs == 1) echo "<span style='display:none'>1</span><img style='height: 12' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
							<td><?php if ($song->soufflants == 1) echo "<span style='display:none'>1</span><img style='height: 12' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
							<td></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>

		
	</div>

</div>