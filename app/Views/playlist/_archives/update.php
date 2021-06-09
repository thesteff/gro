
<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/jquery.tablesorter.js"></script>

<!-- Tablesorter: filter -->
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-filter.js"></script>

<!-- Tablesorter: pager -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/addons/pager/jquery.tablesorter.pager.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-pager.js"></script>


<script type="text/javascript">

	/* ******************* Gestion des tableaux ****************/
	$(function() {
	
		$table1 = $( '#songlist' )
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
		
		// On stylise les colonnes
		update_style();
		
		
		
		// Pour activer le control par clavier
		$("body").on("keydown", function(event) {

			// Pas d'action si pas de selectionné ou focus dans une input classique
			if ( !$(".selected").length || $("body :focus").length) return;

			// On annule le scroll si haut ou bas
			if (event.which == 40 || event.which == 38) {
				event.stopPropagation();
				event.preventDefault();
			}
			
			if (event.which == 40) move_elem("to_bottom");
			if (event.which == 38) move_elem("to_top");
			if (event.which == 39) move_elem("to_right");
			if (event.which == 37) move_elem("to_left");
			
			
			// On déselectionne la tr précédente (qu'un seul selected pour les 2 tableaux)
			/*$("body").find("tbody .selected").removeClass("selected");
			// La tr devient selected
			$(this).addClass("selected");
			update_player($(this).attr("idSong"));*/
		});

		
		// On stylise les colonnes
		$("#songlist .centerTD").each(function() {
			$(this).css("text-align","center");
			$(this).parents("table").find("tbody tr td:nth-child("+($(this).index()+1)+")").css("text-align","center");
		});
		
	});
	
	
	// Permet de fixer le comportement des titre de morceaux playable (selection verte)
	function song_update() {
		$(".is_playable tbody tr[idSong!='-1']").on("click", function() {
			// On déselectionne la tr précédente
			$(this).closest("tbody").find(".selected").removeClass("selected");
			// La tr devient selected
			$(this).addClass("selected");
			update_player($(this).attr("morceauId"), $(this).attr("versionId"));
		});
		
		// On surcharge le css pour les pause
		$(".is_playable tbody tr[idSong='-1'] > td").css("background-color","#dddddd");		
	}
	
	
	/* ******************* Gestion des actions ****************/

	function add_break(apply_to_hidden = true) {
	
		// On créé et insère la nouvelle tr
		new_tr = "<tr idSong='-1'><td colspan='4'><small>-= <i>pause</i> =-</small></td></td></tr>";
		$("#left_list").append(new_tr);
		new_tr = $("#left_list tr:last");
		
		// Gestion des event sur la nouvelle tr
		new_tr.on("click", function() {
			// On déselectionne la tr précédente
			$("body").find("tbody .selected").removeClass("selected");
			// La tr devient selected
			$(this).addClass("selected");
		});

		// On ajoute l'élément au formulaire caché
		if (apply_to_hidden) $("#hidden_select").append("<option versionId='-1' selected>-1</option>");
		
	}
	
	/****************************/
	function move_elem(action, apply_to_hidden = true) {
				
		if ($(".selected").length == 0) return;

		// Si on veut déplacer à gauche et que on a selectionné à droite
		if (action == "to_left" && $(".selected").closest("table").attr("id") == "songlist") {

			// On récupère les données de la selection dans le répertoire
			selected_elem = $("#songlist .selected");
			select_id = selected_elem.attr("idSong");
			titre = selected_elem.children("td:first-child").html();
			auteur = selected_elem.children("td:nth-child(2)").html();
			choeurs = selected_elem.children("td:nth-child(3)").html();
			cuivres = selected_elem.children("td:nth-child(4)").html();
			
			// Si l'élément est déjà présent à gauche, on ne l'ajoute pas
			if ($("#left_list tr[idSong='"+select_id+"']").length > 0) return;     //!!!!!!!!!!!!

			new_tr = "<tr idSong='"+select_id+"'><td><b>"+titre+"</b> <small>-<small> "+auteur+"</small></small></td>";
			new_tr += "<td text-align:center'>"+choeurs+"</td><td text-align:center'>"+cuivres+"</td><td text-align:center'><input type='checkbox' onchange='updateCB("+select_id+")' /></td></tr>";
			$("#left_list tbody").append(new_tr);
			new_tr = $("#left_list tr:last");

			
			// Gestion des event sur la nouvelle tr
			new_tr.on("click", function() {
				// On déselectionne la tr précédente
				$("body").find("tbody .selected").removeClass("selected");
				// La tr devient selected
				$(this).addClass("selected");
				update_player($(this).attr("idSong"));
			});	
			

			// On barre l'original
			selected_elem.css('text-decoration','line-through');
			
			// On ajoute l'élément au formulaire caché
			if (apply_to_hidden) $("#hidden_select").append("<option versionId='"+select_id+"' stage='0' selected>"+select_id+" - 0 "+titre+"</option>");
		}
		
		
		// Si on veut déplacer à droite et que on a selectionné à gauche		
		else if (action == "to_right" && $(".selected").closest("table").attr("id") == "left_list") {
		
			// On récupère l'id du morceau selectionné
			select_id = $("#left_list .selected").attr("idSong");
			$index = $("#left_list .selected").index() + 1;
			
			// On supprime la tr + l'option du hidden select
			$("#left_list .selected").remove();
			$("#hidden_select option:nth-child("+$index+")").remove();
			
			// On actualise l'affichage de songlist
			$("#songlist tr[idSong='"+select_id+"']").css('text-decoration','none');
		
		}

		
		// On monte un élem dans la l'ordre de la liste
		else if ( (action == "to_top" || action == "to_bottom") && $(".selected").closest("table").attr("id") == "left_list") {
			
			// On récupère la tr selectionnée (et la hidden, obligé de compter la position pour les cas de plusieurs pause ayant même id)
			$select_elem = $("#left_list .selected");
			$index = $("#left_list .selected").index() + 1;
			$hidden_select = $("#hidden_select option:nth-child("+$index+")");

			// Si ce n'est pas la première, on swap avec le précédent (et pareil avec la hidden list)
			if (action == "to_top" && $select_elem.prev().length) {
				$select_elem.prev().before($select_elem);
				$hidden_select.prev().before($hidden_select);
			}
			
			// Si ce n'est pas la dernière, on swap avec le suivant (et pareil avec la hidden list)
			else if (action == "to_bottom" && $select_elem.next().length) {
				$select_elem.next().after($select_elem);
				$hidden_select.next().after($hidden_select);
			}
			
		}

		// On actualise le nombre de ref moins les pauses
		if (action == "to_right" || action == "to_left") {
			$("#nbSelect").html($("#hidden_select").children(":not([versionId='-1'])").length);
		}
		
	}
	
	
	
	// Gestion d'un update de checkbox
	function updateCB(id) {
		// On récupère l'état de la CB (pas obligé mais pour sécu)
		$state = $("#left_list [idSong='"+id+"'] input[type=checkbox]").prop("checked") ? "1" : "0";
				
		// On récupère le titre
		$titre = $("#left_list [idSong='"+id+"'] td:first").html();
		// On modifie la hidden_select
		$("#hidden_select > option[versionId='"+id+"']").prop("stage",$state);
		$("#hidden_select > option[versionId='"+id+"']").html("<option versionId='"+id+"' stage='"+$state+"' selected>"+id+" - "+$state+" "+$titre+"</option>");
	}

	
	// Gestion d'un tri de liste
	function sort_list(id_list,type) {
		// On traite la liste affichée
		$("#"+id_list+" tbody tr").sort(asc_sort).appendTo("#"+id_list);
		
		// On traite la liste cachée
		$("#hidden_select option").sort(alpha_special_sort).appendTo("#hidden_select");
	}
	
	
	// Fonctions de tri
	function asc_sort(a, b) {
		str1 = $(a).text().trim();
		str2 = $(b).text().trim();
		return (str2 < str1) ? 1 : -1;
	}
	function alpha_special_sort(a, b) {
		arr1 = $(a).text().split(' ');
		str1 = arr1[3];
		arr2 = $(b).text().split(' ');
		str2 = arr2[3];
		// On remonte les pauses
		if (arr1[0] == -1) return -1;
		else if (arr2[0] == -1) return 1;
		else return (str2 < str1) ? 1 : -1;
	}
	
	
	
	$(document).ready(function() {

		// On remplit la #left_list avec la hidden_list
		$("#hidden_select option").each(function() {
			select_id = $(this).attr("versionId");
			if (select_id == -1) add_break(false);
			else {
				$("#songlist tr[idSong='"+select_id+"']").addClass("selected");
				move_elem("to_left", false);
				$("#songlist tr[idSong='"+select_id+"']").removeClass("selected");
			}
		});
		// On populate la checkbox du stage si besoin
		$("#hidden_select option").each(function() {
			if ($(this).attr("stage") == 1) {
				select_id = $(this).attr("versionId");
				$("#left_list tr[idSong='"+select_id+"'] :checkbox").prop("checked",true);
			}
		});
		
	});
	
</script>


<div class="main_block" style="display:flex; justify-content:center;">


	<!--  //////  BLOCK LEFT !-->
	<div class="block_content_left">	
	
		<h3 class="block_title"><?php echo $page_title; ?></h3>
		
		<?php echo form_open('playlist/update/'.$playlist['infos']['id']) ?>
		
			<div>
				<input type="input" name="title" size="35" placeholder="Titre de la playlist" value="<?php echo set_value('title', $playlist['infos']['title']); ?>" autofocus />
				<?php echo form_error('title'); ?>
			</div>
			
			<br />
			<!-- ///////  PLAYLIST CREEE !-->
			<!-- Affichage de nbRef -->	
			<div class="small_block_list_title soften" style="text-align:right;"><small><span class="soften">(<span id="nbSelect">0</span> réf.)</small></span></div>

			<div class="bright_bg" style="height:550; width:330; overflow:auto;">
				<table id="left_list" class="tablesorter-sand is_playable listTab list_content" cellspacing="0" style="margin:0">
					<thead>
						<tr>
							<th>Titre</th>
							<th width="10" style="text-align:center"><img style='height: 12;' title='choeurs' src='/images/icons/heart.png'></th>
							<th width="10" style="text-align:center"><img style='height: 16; margin:0 2' title='cuivres' src='/images/icons/tp.png'></th>
							<th width="10" style="text-align:center"><img style='height: 16;' src='/images/icons/metro.png' title='réservé au stage'></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
			
			<!-- Boutons de tri -->
			<div class="list_action_elem">
				<input type="button" class="flat_button" name="up" value="   up   " onclick="move_elem('to_top')"/>
				<input type="button" class="flat_button" name="down" value="  down   " onclick="move_elem('to_bottom')" />&nbsp;
				<input type="button" class="flat_button" name="alpha" value="abc" onclick="sort_list('left_list','alpha')" />&nbsp;&nbsp;&nbsp;
				<input type="button" class="flat_button" name="add" value="pause" onclick="add_break()" />
			</div>

			<!-- HIDDEN_SELECT -->
			<select id="hidden_select" name="song_list[]" multiple style="display:none">
				<?php
					////// On insère dans la hidden_select list la liste des morceaux correspondant au update
					foreach ($playlist['list'] as $ref) {
						echo "<option versionId='".$ref->morceauxId."' stage='".$ref->reserve_stage."' selected>".$ref->morceauxId." - ".$ref->reserve_stage." ".$ref->titre."</option>";
					}
				?>
			</select>
				
			<br />
			<input class="button" type="submit" name="submit" value="Modifier la playlist" />
		</form>	
	</div>
	
	
	<!--  //////  BLOCK CENTER !-->
	
	<div class="block_content_center" style="display:flex; align-items: center;">
		<div>
			<input type="button" style="margin:5 0;" name="remove" value=">>" onclick="move_elem('to_right')"/><br />
			<input type="button" name="add" value="<<" onclick="move_elem('to_left')" /><br /><br />
		</div>
	</div>
	
	
	<!--  //////  BLOCK RIGHT !-->
	
	<div style="margin-right:0; width:100%;">
	
		<!-- PAGER -->	
		<div class="pager">
			<small>
			<select class="pagesize" style="display:none;">
				<option value="25" selected>30</option>
			</select>
			&nbsp;&nbsp;&nbsp;
			<img src="/ressources/tablesorter-master/addons/pager/icons/first.png" class="first" alt="First" title="First page" />
			<img src="/ressources/tablesorter-master/addons/pager/icons/prev.png" class="prev" alt="Prev" title="Previous page" />
			<span class="pagedisplay"></span> <!-- this can be any element, including an input -->
			<img src="/ressources/tablesorter-master/addons/pager/icons/next.png" class="next" alt="Next" title="Next page" />
			<img src="/ressources/tablesorter-master/addons/pager/icons/last.png" class="last" alt="Last" title= "Last page" />
			</small>
		</div>
		
	
		<!-- Affichage de nbRef -->	
		<div class="small_block_list_title soften" style="text-align:right; margin-bottom:-13;"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($songlist); ?></span> références)</small></span></div>

		<div>
			<!-- Affichage de la songlist -->		
			<table id="songlist" class="tablesorter focus-highlight is_playable" cellspacing="0">
				<thead>
					<tr>
						<th>Titre</th>
						<th>Artiste</th>
						<th class="centerTD" width="10" style="text-align:center"><img style='height: 12;' src='/images/icons/heart.png'></th>
						<th class="centerTD" width="10" style="text-align:center"><img style='height: 16; margin:0 2' src='/images/icons/tp.png'></th>
						<th class="centerTD" width="10">Joué</th>
						<th class="centerTD" width="54">Dernière fois</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>Titre</th>
						<th>Artiste</th>
						<th><img style='height: 10;' src='/images/icons/heart.png'></th>
						<th><img style='height: 14; margin:0 2' src='/images/icons/tp.png'></th>
						<th>Joué</th>
						<th>Dernière fois</th>
					</tr>
				</tfoot>
				<tbody id="songlist_body">
					<?php foreach ($songlist as $song): ?>
						<tr id="list_elem<?php echo $song->idSong; ?>" idSong="<?php echo $song->idSong ?>">
							<td><?php echo $song->titre; ?></td>
							<td><?php echo $this->artists_model->get_artist_label($song->idArtiste); ?></td>
							<td><?php if ($song->choeurs == 1) echo "<span style='display:none'>1</span><img style='height: 12;' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
							<td><?php if ($song->cuivres == 1) echo "<span style='display:none'>1</span><img style='height: 12;' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
							<td><?php echo $this->songs_model->nbPlayed($song->idSong); ?></td>
							<td><?php
								$lastDate = $this->songs_model->lastTimePlayed($song->idSong);
								if ($lastDate) {
									$tempDate = date_create_from_format("Y-m-d",$lastDate);
									echo date_format($tempDate,"d/m/Y");
								}
								else echo "jamais";
							?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	
</div>