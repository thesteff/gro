<script type="text/javascript" src="<?php echo base_url();?>ressources/tableSorter/jquery.tablesorter.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>images/icons/sand/style.css" type="text/css" media="print, projection, screen" />


<script type="text/javascript">

	$(function() {
		$.tablesorter.defaults.widgets = ['zebra']; 
		$("#songlist").tablesorter();
		
		//setCellElemBehavior("#songlist",'#CC2900','#FFC0B2','#851f00','white');
		setCellElemBehavior("#songlist",'#255625','#BEEFBE','#851f00','white');
		
	});


    function show_infos(id)  {
	
		elem = document.getElementById("song_infos"+id);
		picto = document.getElementById("showInfos"+id);
		
		if (elem.style.display == "none" || !elem.style.display) {
			elem.style.display = "block";
			picto.innerHTML = "<b>-</b>";
		}
		else {
			elem.style.display = "none";
			picto.innerHTML = "<b>+</b>";
		}
    }

	
	function get_playlists() {

		//alert($("#select_evt").val());
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_evt_playlists",
		
			{'idEvent':$("#select_evt").val()},
		
			function (msg) {
			
				// On récupère les données du serveur
				$data = JSON.parse(msg);
			
				// On vide la liste actuellement affichée
				$("#songlist_body").empty();
		
				// On actualise le tableau
				$.each($data.playlist.list,function(index) {
					$("#songlist_body").append("<tr id='list_elem' onclick='update_player("+$data.playlist.list[index].idSong+")' idSong='"+$data.playlist.list[index].idSong+"'><td>"+$data.playlist.list[index].titre+"</td><td>"+$data.playlist.list[index].label+"</td></tr>");
				});
				
				// On rétablit les propriétés graphiques et d'interaction
				setCellElemBehavior("#songlist",'#255625','#BEEFBE','#851f00','white');
				$("#songlist").trigger("update");
				$("#songlist").trigger("applyWidgets");
				// On actualise le nombre de ref affichées
				$("#nbRef").empty();
				$("#nbRef").append($data.playlist.list.length);
				

				// On actualise la date de l'évènement consulté
				$("#date_label").empty();
				var dateParts = $data.jam.date.split("-");
				var jsDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2].substr(0,2));
				// manip à la con pour rajouter un "0" avant les num de mois < 10
				month = (jsDate.getMonth()+1);
				if (month < 10) monthStr = "0"+month; else monthStr = month;
				$("#date_label").append("     ["+jsDate.getDate()+"/"+monthStr+"/"+jsDate.getFullYear()+"]");
				
			}
		);

    }

	
 </script>

<div class="main_block">

	<div id="archives_content" class="block_content">
	
		<div class="block_head">
			<h3 class="block_title"><?php echo $page_title; ?></h3>
			<hr>
		</div>
		
		<!-- SELECT jam + nb ref -->
		<div>
			<div>
				<select id="select_evt" name="select_evt" onchange="get_playlists()">
					<option value="0">Répertoire GRO</option>
					<?php foreach ($jamlist as $jam): ?>
						<option value="<?php echo $jam['id']; ?>"><?php echo $jam['title']; ?></option>
					<?php endforeach ?>
				</select>
				<small><span class="soften" id="date_label"></span></small>
			</div>
			<div class="small_block_list_title soften right"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($songlist); ?></span> références)</small></span></div>
		</div>
		
		<!-- Affichage de la songlist -->		
		<table id="songlist" class="tablesorter" cellspacing="0">
			<thead>
				 <tr>
					<th>Titre</th>
					<th>Artiste</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Titre</th>
					<th>Artiste</th>
				</tr>
			</tfoot>
			<tbody id="songlist_body">
				<?php foreach ($songlist as $song): ?>
					<tr id="list_elem<?php echo $song->idSong; ?>"
						<?php if ($this->session->userdata('logged') == true) : ?>
							onclick="update_player('<?php echo str_replace("'", "\'",$song->idSong); ?>')"
							idSong="<?php echo str_replace("'", "\'",$song->idSong); ?>"
						<?php endif; ?>
					>
						<td><?php echo $song->titre; ?></td>
						<td><?php echo $this->artists_model->get_artist($song->idArtiste); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		
	</div>

</div>