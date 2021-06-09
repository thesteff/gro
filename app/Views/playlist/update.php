
<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/jquery.tablesorter.js"></script>

<!-- Tablesorter: filter -->
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-filter.js"></script>

<!-- Tablesorter: pager -->
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/tablesorter-master/addons/pager/jquery.tablesorter.pager.css">
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-pager.js"></script>


<script type="text/javascript">


/* ************** Gestion du swap double-list ************/
(function($, viewport){
	$(document).ready(function() {

	
        // On utilise le carousel uniquement en XS
        if(viewport.is('xs')) {
            addCarousel("double_list_bar");
        }

		
		/******** ECRAN TACTILES **********/
		var myElement = document.getElementById('double_list_bar');
		var hammertime = new Hammer(myElement);
		hammertime.on('swipeleft swiperight', function(ev) {
			// RIGHT
			if (ev.type == 'swipeleft') goRight("double_list_bar");
			// LEFT
			else if (ev.type == 'swiperight') goLeft("double_list_bar");
		});
		
		
		$(window).resize(
			viewport.changed(function() {
				if ( viewport.is('xs') && !$("#double_list_bar #carousel").length) {
					addCarousel("double_list_bar");
				}
				else if ( !viewport.is('xs') && $("#double_list_bar #carousel").length ) {
					removeCarousel("double_list_bar");
				}
			})
		); 
		
	});
})(jQuery, ResponsiveBootstrapToolkit);	



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
		
		
		// La saisie d'un titre active le bouton de création de playlist
		$("#titre").on('input', function() {
			if ($(this).val() != "") $("#action_btn").prop('disabled',false);
			else $("#action_btn").prop('disabled',true);
		});
		
		
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

		});
		
	});
	
	
	/* ******************* Gestion des actions ****************/

	function add_break(apply_to_hidden = true) {
	
		// On créé et insère la nouvelle tr
		new_tr = "<tr versionId='-1'><td colspan='4'><small>-= <i>pause</i> =-</small></td></td></tr>";
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
			versionId = selected_elem.attr("versionId");
			morceauId = selected_elem.attr("morceauId");
			titre = selected_elem.children("td:first-child").html();
			auteur = selected_elem.children("td:nth-child(2)").html();
			choeurs = selected_elem.children("td:nth-child(3)").html();
			cuivres = selected_elem.children("td:nth-child(4)").html();
			
			// Si l'élément est déjà présent à gauche, on ne l'ajoute pas
			if ($("#left_list tr[versionId='"+versionId+"']").length > 0) return;     //!!!!!!!!!!!!
			
			
			// Checkbox stylée
			new_tr = "<tr morceauId='"+morceauId+"' versionId='"+versionId+"'><td><b>"+titre+"</b> <small>-<small> "+auteur+"</small></small></td>";
			new_tr += "<td text-align:center'>"+choeurs+"</td><td text-align:center'>"+cuivres+"</td>";
			new_tr += "<td text-align:center'>";
			new_tr += '<div class="checkbox" style="margin: 0px;"><label>';
			new_tr += "<input class='form-control' type='checkbox' value='' onchange='updateCB("+versionId+")' />";
			new_tr += '<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>';
			new_tr += '</label></div>';
			new_tr += '</td>';
			new_tr += '</tr>';
			$("#left_list tbody").append(new_tr);
			new_tr = $("#left_list tr:last");

			
			// Gestion des event sur la nouvelle tr
			new_tr.on("click", function() {
				// On déselectionne la tr précédente
				$("body").find("tbody .selected").removeClass("selected");
				// La tr devient selected
				$(this).addClass("selected");
				update_player($(this).attr("morceauId"), $(this).attr("versionId"));
			});	
			

			// On barre l'original
			selected_elem.css('text-decoration','line-through');
			
			// On ajoute l'élément au formulaire caché
			if (apply_to_hidden) $("#hidden_select").append("<option versionId='"+versionId+"' stage='0' selected>"+versionId+" - 0 "+titre+"</option>");
		}
		
		
		// Si on veut déplacer à droite et que on a selectionné à gauche		
		else if (action == "to_right" && $(".selected").closest("table").attr("id") == "left_list") {
		
			// On récupère l'id du morceau selectionné
			select_id = $("#left_list .selected").attr("versionId");
			$index = $("#left_list .selected").index() + 1;
			
			// On supprime la tr + l'option du hidden select
			$("#left_list .selected").remove();
			$("#hidden_select option:nth-child("+$index+")").remove();
			
			// On actualise l'affichage de songlist
			$("#songlist tr[versionId='"+select_id+"']").css('text-decoration','none');
		
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
		$state = $("#left_list [versionId='"+id+"'] input[type=checkbox]").prop("checked") ? "1" : "0";
				
		// On récupère le titre
		$titre = $("#left_list [versionId='"+id+"'] td:first").html();
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
				$("#songlist tr[versionId='"+select_id+"']").addClass("selected");
				move_elem("to_left", false);
				$("#songlist tr[versionId='"+select_id+"']").removeClass("selected");
			}
		});
		// On populate la checkbox du stage si besoin
		$("#hidden_select option").each(function() {
			if ($(this).attr("stage") == 1) {
				select_id = $(this).attr("versionId");
				$("#left_list tr[versionId='"+select_id+"'] :checkbox").prop("checked",true);
			}
		});
		
	});
	
</script>


<div class="panel panel-default row">

	<!-- Header !-->
	<div class="row panel-heading panel-bright title_box">
		<h4><?php echo $page_title; ?></h4>
	</div>
	
	

	<div id="double_list_bar" class="container-fluid" style="padding:15px 0px; margin-left:-15px; margin-right:-15px;">	
		
		<?php $validation->listErrors() ?>
		<?php echo form_open('playlist/update/'.$playlist['infos']['id']) ?>
		
		<div class="row">
		
			<!------------ BLOCK GAUCHE + CENTRE + BOUTON-LG ---------------->
			<div class="item active col-xs-12 col-sm-5 col-md-5 col-lg-5">
			
				<div class="" style="display:flex; align-items:center; justify-content:space-between">
				
					<!------------ BLOCK GAUCHE ---------------->
					<div style="flex-grow:1">
						<input class="form-control" id="titre" type="input" name="title" placeholder="Titre de la playlist" value="<?php echo set_value('title', $playlist['infos']['title']); ?>" autofocus />

						<!-- **** PLAYLIST CREEE !-->
						<div style="overflow:auto">
							<table id="left_list" class="tablesorter-sand is_playable listTab" style="margin:0px">
								<thead>
									<tr>
										<th>
											<span>Titre</span>
											<!-- Affichage de nbRef -->	
											<span class="soften pull-right">(<span id="nbSelect">0</span>)</span>
										</th>
										<th class="centerTD" width="10px"><img style='height: 12px;' src='/images/icons/heart.png' title='choeurs'></th>
										<th class="centerTD" width="10px"><img style='height: 16px;' src='/images/icons/tp.png' title='cuivres'></th>
										<th class="centerTD" width="10px"><img style='height: 16px;' src='/images/icons/metro.png' title='réservé au stage'></th>
									</tr>
								</thead>
								<!-- Boutons de tri -->
								<tfoot>
									<tr>
										<th colspan="4" style="text-align:center">
											<input type="button" class="btn btn-xs btn-default" name="up" value="up" onclick="move_elem('to_top')"/>							
											<input type="button" class="btn btn-xs btn-default" name="down" value="down" onclick="move_elem('to_bottom')" />&nbsp;
											<input type="button" class="btn btn-xs btn-default" name="alpha" value="abc" onclick="sort_list('left_list','alpha')" />&nbsp;&nbsp;&nbsp;
											<input type="button" class="btn btn-xs btn-default" name="add" value="pause" onclick="add_break()" />
										</th>
									</tr>
								</tfoot>
								<tbody>
								</tbody>
							</table>
						</div>

						<!-- HIDDEN_SELECT -->
						<select id="hidden_select" name="song_list[]" multiple style="display:none">
							<?php
								////// On insère dans la hidden_select list la liste des morceaux correspondant au update
								foreach ($playlist['list'] as $ref) {
									echo "<option versionId='".$ref->versionId."' stage='".$ref->reserve_stage."' selected>".$ref->versionId." - ".$ref->reserve_stage." ".$ref->titre."</option>";
								}
							?>
						</select>
					</div>
					</form>	

	
					<!------------ BLOCK CENTRE ---------------->
					<div class="hidden-xs" style="text-align:center; margin-left:30px;">
						<div class="row">
							<button class="btn btn-default btn-sm" type="button" name="remove" onclick="move_elem('to_right')"/><span class="glyphicon glyphicon-menu-right"></span></button>
						</div>
						<div class="row">
							<button class="btn btn-default btn-sm" type="button" name="add" onclick="move_elem('to_left')" /><span class="glyphicon glyphicon-menu-left"></span></button>
						</div>
					</div>
					
					<!-- Grip pour le carrousel !-->
					<div class="hidden-sm hidden-md hidden-lg" style="margin: 10px; border-width: 0px 2px; border-style: solid; border-color: SaddleBrown; height: 100px; width: 5px">
					</div>
				
				
				</div>	<!----- Flexbox ------>
				
				
				<!-- BOUTON DE CREATION -->
				<br />
				<div class="container-fluid text-center">
					<button id="action_btn" class="btn btn-block btn-primary" type="submit" name="submit">Modifier la playlist</button>
				</div>
				
			</div>   <!----- End Gauche+centre ------>
	
	
	<!------------ BLOCK DROITE ---------------->
			
			<div class="item col-sm-7 col-md-7 col-lg-7">
			
				<!-- PAGER -->	
				<div class="pager form-inline" style="margin-top:0px">

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
				<div class="small_block_list_title soften" style="text-align:right; margin-bottom:-13;"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($list_song); ?></span> références)</small></span></div>

				<div>
					<!---- SONGLIST ---->	
					<table id="songlist" class="tablesorter focus-highlight is_playable" cellspacing="0" style="margin-bottom:0px">
						<thead>
							<tr>
								<th>Titre</th>
								<th>Artiste</th>
								<th class="centerTD" width="10"><img style='height: 12px;' src='/images/icons/heart.png'></th>
								<th class="centerTD" width="10"><img style='height: 16px; margin:0 2' src='/images/icons/tp.png'></th>
								<th class="centerTD" width="10">Joué</th>
								<th class="sorter-shortDate dateFormat-ddmmyyyy centerTD" width="68">Dernière fois</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th>Titre</th>
								<th>Artiste</th>
								<th><img style='height: 10px;' src='/images/icons/heart.png'></th>
								<th><img style='height: 14px; margin:0 2' src='/images/icons/tp.png'></th>
								<th>Joué</th>
								<th>Dernière fois</th>
							</tr>
						</tfoot>
						<tbody id="songlist_body">
							<?php foreach ($list_song as $song): ?>
								<tr morceauId="<?php echo $song->morceauId; ?>" versionId="<?php echo $song->versionId; ?>">
									<td><?php echo $song->titre; ?></td>
									<td><?php echo $song->artisteLabel; ?></td>							
									<td><?php if ($song->choeurs == 1) echo "<span style='display:none'>1</span><img style='height: 12px' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
									<td><?php if ($song->soufflants == 1) echo "<span style='display:none'>1</span><img style='height: 12px' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
									<td><?php echo $song->nbPlayed; ?></td>
									<td><?php echo $song->lastDate;	?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			
		</div> <!-- Row !-->
	</div>   <!-- Container-fluid !-->
	
</div>