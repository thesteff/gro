<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/jquery.tablesorter.js"></script>

<!-- Tablesorter: filter -->
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-filter.js"></script>

<!-- Tablesorter -->
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-pager.js"></script>
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-columnSelector.js"></script>



<script type="text/javascript">

	$(function() {
	
		$table1 = $( '#songlist' ).tablesorter({
			
			theme : 'sand',
			
			// Le tableau n'est pas triable
			headers: {'.no_sort' : {sorter: false} },
			
			// initialize zebra and filter widgets
			widgets : [ "zebra", "filter", "pager" , 'columnSelector'],

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
		update_style();
		
		// On active les handlers pour le player
		setupTriggers();
		
		// On fait un get_playlist si on ne visualise pas la page de page (idPlaylist dans l'URL)
		temp = window.location.href.split("/");
		if (temp[temp.length-1] !== "playlist") get_playlist();

	});

	
	
	// Récupère une playlist sur le serveur
	function get_playlist() {
	
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax/get_playlist'); ?>",
		
			{'idPlaylist':$("#select_playlist").val()},
		
			function (return_data) {
			
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
			
			
				if ($obj['state'] == 1) {
			
					// On vide la liste actuellement affichée
					$("#songlist_body").empty();
					
					// On rempli le tableau avec les nouvelles valeurs
					$.each($obj.data.list,function(index) {
						if ($obj.data.list[index].versionId != -1) {
							
							// On gère les colonnes nécessitant un picto
							mark = "<span style='display:none'>1</span><img style='height: 12px;' src='/images/icons/ok.png'>";
							empty = "<span style='display:none'>0</span>";
							if ($obj.data.list[index].choeurs == 1) choeurs = mark; else choeurs="";
							if ($obj.data.list[index].soufflants == 1) soufflants = mark; else soufflants="";
							if ($("#select_playlist").val() >= 0 && $obj.data.list[index].reserve_stage == 1) stage = mark; else stage="";  // Si stage
							
							// On gère les autre colonnes
							$compositeur = $obj.data.list[index].artisteLabel;
							$annee = $obj.data.list[index].annee ? $obj.data.list[index].annee : "?";
							$tona = $obj.data.list[index].tona ? $obj.data.list[index].tona.charAt(0).toUpperCase()+$obj.data.list[index].tona.slice(1) : "?";
							$mode = $obj.data.list[index].mode ? $obj.data.list[index].mode : "?";
							$tempo = $obj.data.list[index].tempo ? $obj.data.list[index].tempo : "?";
							$langue = $obj.data.list[index].langue ? $obj.data.list[index].langue : "?";
							
							$("#songlist_body").append("<tr morceauId="+$obj.data.list[index].morceauId+" versionId="+$obj.data.list[index].versionId+"><td>"+$obj.data.list[index].titre+"</td><td>"+$compositeur+"</td><td>"+$annee+"</td><td>"+$tona+"</td><td>"+$mode+"</td><td>"+$tempo+"</td><td>"+$langue+"</td><td style='text-align: center'>"+choeurs+"</td><td style='text-align: center'>"+soufflants+"</td><td style='text-align: center'>"+stage+"</td></tr>");
						}
						// On gère les pauses
						else $("#songlist_body").append("<tr morceauId="+$obj.data.list[index].morceauId+" versionId='"+$obj.data.list[index].versionId+"'><td colspan='"+$("#songlist th").children().length+"'>-= <i>pause</i> =-</td></tr>");
					});

					// On actualise le cache du tableau
					$("#songlist").trigger("update");
					
					// On stylise les colonnes
					$("#songlist .centerTD").each(function() {
						$(this).css("text-align","center");
						$(this).parents("table").find("tbody tr td:nth-child("+($(this).index()+1)+")").css("text-align","center");
					});
					
					// On stylise les colonnes
					update_style();
					
					// On actualise les handlers pour le player
					$(".is_playable").removeClass("updated");
					setupTriggers();
					
					// On compte les pauses
					nb_break = $("#songlist_body [idSong='-1']").length;
					
					// On actualise le nombre de ref affichées
					$("#nbRef").empty();
					$("#nbRef").append($obj.data.list.length - nb_break);
					
					// On actualise l'affichage de la tool bar
					if ($("#select_playlist").val() == -1) $("#admin_bar").addClass("hidden");
					else $("#admin_bar").removeClass("hidden");
				}
			}
		);

    }
	

	
	/********** DELETE PLAYLIST ***************/
	function popup_delete_playlist() {
		
		// On récupère les infos de la playlist		
		$title = $("#select_playlist :selected").html();
		$playlistId = $("#select_playlist :selected").val();
		
		$text = "Etes-vous sûr de voulour supprimer la playlist <b>"+$title+"</b> ?";
		$confirm = "<div class='modal-footer'>";
			$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
			$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_playlist("+$playlistId+")'>Supprimer</button>";
		$confirm += "</div>";
		
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-dialog").addClass("backdrop","static");
		$("#modal_msg .modal-header").html("Supprimer la playlist");
		$("#modal_msg .modal-body").html($text);
		$("#modal_msg .modal-footer").html($confirm);
		$("#modal_msg").modal('show');
	}
	
	
	function delete_playlist($playlistId) {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/playlist/delete",
	
			{'playlistId':$playlistId},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					// Succés
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
	
	
	function update_playlist() {
		//alert($("#select_playlist :selected").val());
		window.location.href = "<?php echo site_url('playlist/update/'); ?>"+$("#select_playlist :selected").val();
	}	
	
 </script>


<div class="panel panel-default row">

	<!-- Header !-->
	<div class="row panel-heading panel-bright title_box">
		<h4>
			<?php echo $page_title; ?>
			<!-- CREER -->
			<button class="pull-right btn btn-default btn-xs" onclick="window.location.href='<?php echo site_url("playlist/create");?>'"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;Créer</button>
		</h4>
	</div>
	
	<!-- SELECT playlist + nb ref -->
	<div class="row panel-heading panel-default">
		
		<div>
			<div class="form-row">
			
				<!-- SELECT -->
				<div class="form-group col-sm-4">
					<select id="select_playlist" class="form-control" name="select_playlist" onchange="get_playlist()">
						<option value="-1">GRO</option>
						<?php foreach ($playlists as $list): ?>
							<option value="<?php echo $list['id']; ?>" <?php if ($idPlaylist == $list['id']) echo "selected"; ?>><?php echo $list['title']; ?></option>
						<?php endforeach ?>
					</select>
				</div>
						
				<!-- BOUTTONS UPDATE ET SUPPR -->
				<div id="admin_bar" class="form-group col-sm-1 btn-group btn-group-xs <?php if ($idPlaylist == -1) echo "hidden"; ?>" style="display:flex">
					<!-- MODIFIER -->
					<button class="form-control btn btn-default" onclick="javascript:update_playlist()" title="Modifier playlist"><i class="glyphicon glyphicon-pencil"></i></button>
					<!-- SUPRRIMER -->
					<button class="form-control btn btn-default" onclick="javascript:popup_delete_playlist()" title="Supprimer playlist"><i class="glyphicon glyphicon-trash"></i></button>
				</div>
				
			</div>
		</div>

		
	</div>
		
	
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
	<div class="row">
		<div class="col-lg-12">
		
			<table id="songlist" class="tablesorter lineHighLight is_playable">
				<thead>
					<tr>
						<th data-priority="critical" class="no_sort" style="width:150px"><span>Titre</span></th>
						<th data-priority="critical" class="no_sort"><span>Compositeur</span></th>
						<th data-priority="6" class="no_sort centerTD"><span>Année</span></th>
						<th data-priority="4" class="no_sort centerTD"><span>Tona</span></th>
						<th data-priority="4" class="no_sort centerTD"><span>Mode</span></th>
						<th data-priority="6" class="no_sort centerTD"><span>Tempo</span></th>
						<th data-priority="6" class="no_sort centerTD"><span>Langue</span></th>
						<th data-priority="critical" class="no_sort centerTD" width="10px" style="text-align:center"><img style='height: 12px;' src='/images/icons/heart.png'><span></span></th>
						<th data-priority="critical" class="no_sort centerTD" width="10px" style="text-align:center"><img style='height: 16px; margin:0px 2px' src='/images/icons/tp.png'><span></span></th>
						<th data-priority="3" class="no_sort centerTD" width="10px"><span class="stage"><img style='height: 16px;' src='/images/icons/metro.png'></span></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>Titre</th>
						<th>Compositeur</th>
						<th class="centerTD">Année</th>
						<th class="centerTD">Tona</th>
						<th class="centerTD">Mode</th>
						<th class="centerTD">Tempo</th>
						<th class="centerTD">Langue</th>
						<th class="centerTD" width="10px" style="text-align:center"><img style='height: 10px;' src='/images/icons/heart.png'></th>
						<th class="centerTD" width="10px" style="text-align:center"><img style='height: 14px; margin:0px 2px' src='/images/icons/tp.png'></th>
						<th class="centerTD" width="10px"><img style='height: 14px; margin:0px 2px' src='/images/icons/metro.png'><span class="stage"></span></th>
					</tr>
				</tfoot>
				<tbody id="songlist_body">
					<?php if ($idPlaylist == -1) :?>	
						<?php foreach ($list_song as $song): ?>
							<tr morceauId="<?php echo $song->morceauId; ?>" versionId="<?php echo $song->versionId; ?>">
								<td class="song"><?php echo $song->titre; ?></td>
								<td><?php echo $song->artisteLabel; ?></td>							
								<td><?php echo $song->annee; ?></td>
								<td><?php echo ucfirst($song->tona); ?></td>
								<td><?php echo $song->mode; ?></td>
								<td><?php echo $song->tempo; ?></td>
								<td><?php echo $song->langue; ?></td>
								<td><?php if ($song->choeurs == 1) echo "<span style='display:none'>1</span><img style='height: 12px' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
								<td><?php if ($song->soufflants == 1) echo "<span style='display:none'>1</span><img style='height: 12px' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
								<td></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
		

</div>