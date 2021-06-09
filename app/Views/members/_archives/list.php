
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

		setCellElemBehavior("table",'#255625','#BEEFBE','#851f00','white');
	
		var $table1 = $( 'table' )
		.tablesorter({
			theme : 'sand',
			// this is the default setting
			//cssChildRow : "tablesorter-childRow",

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

		
		// Make table cell focusable
		// http://css-tricks.com/simple-css-row-column-highlighting/
		if ( $('.focus-highlight').length ) {
			$('.focus-highlight').find('td, th')
			.attr('tabindex', '1')
			// add touch device support
			.on('touchstart', function() {
			$(this).focus();
			});
		}
		
		
		// hide child rows - get in the habit of not using .hide()
		// See http://jsfiddle.net/Mottie/u507846y/ & https://github.com/jquery/jquery/issues/1767
		// and https://github.com/jquery/jquery/issues/2308
		// This won't be a problem in jQuery v3.0+
		$table1.find( '.tablesorter-childRow td' ).addClass( 'hidden' );

		// Toggle child row content (td), not hiding the row since we are using rowspan
		// Using delegate because the pager plugin rebuilds the table after each page change
		// "delegate" works in jQuery 1.4.2+; use "live" back to v1.3; for older jQuery - SOL
		$table1.delegate( '.toggle', 'click' ,function() {
			// use "nextUntil" to toggle multiple child rows
			// toggle table cells instead of the row
			$( this )
				.closest( 'tr' )
				.nextUntil( 'tr.tablesorter-hasChildRow' )
				.find( 'td' )
				.toggleClass( 'hidden' );
				return false;
		});

		// Toggle filter_childRows option
		$( 'button.toggle-combined' ).click( function() {
			var wo = $table1[0].config.widgetOptions,
			o = !wo.filter_childRows;
			wo.filter_childRows = o;
			$( '.state1' ).html( o.toString() );
			// update filter; include false parameter to force a new search
			$table1.trigger( 'search', false );
			return false;
		});
		

		// On fait un get_playlist si on ne visualise pas la page de page (idPlaylist dans l'URL)
		temp = window.location.href.split("/");
		if (temp[temp.length-1] !== "playlist") get_playlist();
		
	});

	
	function get_playlist() {
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_playlist",
		
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
						$("#songlist_body").append("<tr onclick='update_player("+$playlist.list[index].idSong+");' idSong='"+$playlist.list[index].idSong+"'><td>"+$playlist.list[index].titre+"</td><td>"+$playlist.list[index].label+"</td><td style='text-align: center'>"+choeurs+"</td><td style='text-align: center'>"+cuivres+"</td></tr>");
					}
					// On gère les pauses
					else $("#songlist_body").append("<tr idSong='"+$playlist.list[index].idSong+"'><td colspan='"+$("#songlist th").children().length+"'>-= <i>pause</i> =-</td></tr>");
				});

				$("#songlist").trigger("update");
				setCellElemBehavior("#songlist",'#255625','#BEEFBE','#851f00','white');
				
				// On compte les pauses
				nb_break = $("#songlist_body [idSong='-1']").length;
				
				// On actualise le nombre de ref affichées
				$("#nbRef").empty();
				$("#nbRef").append($playlist.list.length - nb_break);
				
				
				// On actualise la tool bar
				$state = "block";
				if ($("#select_playlist").val() == -1) $state = "none";
				$("#left_tool_bar").css("display",$state);
				
			}
		);

    }
	
	
	// Popup de confirmation de suppression de playlist
	function popup_confirm() {

		// POPUP Confirm
		$confirm = "<p>Etes-vous sûr de voulour supprimer la playlist '"+$("#select_playlist").find(":selected").html()+"' ?</p>";
		$confirm += "<hr>";
		$confirm += "<p style='text-align:center'><input type='button' value='supprimer' onclick='javascript:delete_playlist()'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()'></p>";
		TINY.box.show({html:$confirm,boxid:'confirm',animate:false,width:650});
    }
	
	
	
	function delete_playlist() {

		TINY.box.hide();
		$id_item_selected = $("#select_playlist").find(":selected").val();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/delete_playlist",
		
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

	
 </script>

<div class="main_block">

	<div id="memberlist_content" class="block_content">

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
		<div class="small_block_list_title soften right"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($memberlist); ?></span> références)</small></span></div>
		<!-- SONGLIST -->		
		<table id="songlist" class="tablesorter focus-highlight" cellspacing="0">
			<thead>
				<tr>
					<th>Titre</th>
					<th>Artiste</th>
					<th width="10" style="text-align:center"><img style='height: 12;' src='/images/icons/heart.png'></th>
					<th width="10" style="text-align:center"><img style='height: 16; margin:0 2' src='/images/icons/tp.png'></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Titre</th>
					<th>Artiste</th>
					<th style="text-align:center"><img style='height: 10;' src='/images/icons/heart.png'></th>
					<th style="text-align:center"><img style='height: 14; margin:0 2' src='/images/icons/tp.png'></th>
				</tr>
			</tfoot>
			<tbody id="memberlist_body">
				<?php if ($idPlaylist == -1) :?>				
					<?php foreach ($memberlist as $member): ?>
						<tr id="list_elem<?php echo $song->idSong; ?>"
							<?php if ($this->session->userdata('logged') == true) : ?>
								onclick="update_player('<?php echo str_replace("'", "\'",$song->idSong); ?>'); select_song(<?php echo $song->idSong; ?>);"
								idSong="<?php echo str_replace("'", "\'",$song->idSong); ?>"
							<?php endif; ?>
						>
							<td><?php echo $song->titre; ?></td>
							<td><?php echo $this->artists_model->get_artist($song->idArtiste); ?></td>
							<td style="text-align: center"><?php if ($song->choeurs == 1) echo "<span style='display:none'>1</span><img style='height: 12;' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
							<td style="text-align: center"><?php if ($song->cuivres == 1) echo "<span style='display:none'>1</span><img style='height: 12;' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

</div>