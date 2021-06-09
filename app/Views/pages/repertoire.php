
<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url("ressources/tablesorter-master/css/theme.sand.css");?>">
<script src="<?php echo base_url("ressources/tablesorter-master/js/jquery.tablesorter.js");?>"></script>
<script src="<?php echo base_url("ressources/tablesorter-master/js/widgets/widget-storage.js");?>"></script>
<script src="<?php echo base_url("ressources/tablesorter-master/js/widgets/widget-filter.js");?>"></script>
<script src="<?php echo base_url("ressources/tablesorter-master/js/widgets/widget-columnSelector.js");?>"></script>

<!-- Tablesorter: pager -->
<!--<link rel="stylesheet" href="<?php echo base_url();?>/ressources/tablesorter-master/addons/pager/jquery.tablesorter.pager.css">
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-pager.js"></script>
!-->
<link rel="stylesheet" href="<?php echo base_url("ressources/tablesorter-master/addons/pager/jquery.tablesorter.pager.css");?>">
<script src="<?php echo base_url("ressources/tablesorter-master/js/widgets/widget-pager.js");?>"></script>


<script type="text/javascript">

	$(function() {

		$table1 = $( '#songlist' ).tablesorter({
			
			theme : 'sand',
			//debug: "core filter",
			// Le tableau n'est pas triable
			headers: {'.titre, .artiste, .choeurs, .cuivres, .stage' : {sorter: false}
			},
			
			// initialize zebra and filter widgets
			widgets : [ "zebra", "filter", "pager", "columnSelector" ],

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
				filter_ignoreCase : true,
				
				
				// target the column selector markup
				columnSelector_container : $('#columnSelector'),
				// column status, true = display, false = hide
				// disable = do not display on list
				columnSelector_columns : {
				0: 'disable' /* set to disabled; not allowed to unselect it */
				},
				// remember selected columns (requires $.tablesorter.storage)
				columnSelector_saveColumns: true,

				// container layout
				columnSelector_layout : '<label><input type="checkbox">{name}</label>',
				// layout customizer callback called for each column
				// function($cell, name, column){ return name || $cell.html(); }
				columnSelector_layoutCustomizer : null,
				// data attribute containing column name to use in the selector container
				columnSelector_name  : 'data-selector-name',

				/* Responsive Media Query settings */
				// enable/disable mediaquery breakpoints
				columnSelector_mediaquery: true,
				// toggle checkbox name
				columnSelector_mediaqueryName: 'Auto: ',
				// breakpoints checkbox initial setting
				columnSelector_mediaqueryState: true,
				// hide columnSelector false columns while in auto mode
				columnSelector_mediaqueryHidden: true,

				// set the maximum and/or minimum number of visible columns; use null to disable
				columnSelector_maxVisible: null,
				columnSelector_minVisible: null,
				// responsive table hides columns with priority 1-6 at these breakpoints
				// see http://view.jquerymobile.com/1.3.2/dist/demos/widgets/table-column-toggle/#Applyingapresetbreakpoint
				// *** set to false to disable ***
				columnSelector_breakpoints : [ '20em', '30em', '40em', '50em', '60em', '70em' ],
				// data attribute containing column priority
				// duplicates how jQuery mobile uses priorities:
				// http://view.jquerymobile.com/1.3.2/dist/demos/widgets/table-column-toggle/
				columnSelector_priority : 'data-priority',

				// class name added to checked checkboxes - this fixes an issue with Chrome not updating FontAwesome
				// applied icons; use this class name (input.checked) instead of input:checked
				columnSelector_cssChecked : 'checked',
			},	
				
				
			initialized : function(table) {
				// On active les handlers pour le player
				//song_update();
			}
			
		});
		
	});
	
 </script>

 <!-- ****************************************** !-->
 
 <!-- Span caché pour obtenir les infos du membres -->
<!-- <span id="logged" style="display:none"><?php if (isset($member)) echo $member->pseudo; ?></span> !-->
 


<div class="panel panel-default row">

	<!-- Header !-->
	<div class="row panel-heading panel-bright title_box">
		<h4><?php echo $page_title; ?></h4>
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
	<div class="small_block_list_title soften pull-right"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($list_song_ex); ?></span> références)</small></span></div>
	
	<!---- SONGLIST ---->	
	<div class="row">
		<div class="col-lg-12">
		
			<table id="songlist" class="tablesorter focus-highlight is_playable" cellspacing="0">
				<thead>
					<tr>
						<th data-priority="critical" style="width:150px"><span>Titre</span></th>
						<th data-priority="critical"><span>Compositeur</span></th>
						<th class="centerTD" data-priority="3"><span>Année</span></th>
						<th class="centerTD" data-priority="5"><span>Tona</span></th>
						<th class="centerTD" data-priority="5"><span>Mode</span></th>
						<th class="centerTD" data-priority="4"><span>Tempo</span></th>
						<th class="centerTD" data-priority="3"><span>Langue</span></th>
						<th class="centerTD" data-priority="2" width="10" style="text-align:center"><img style='height: 12px;' src='/images/icons/heart.png'><span></span></th>
						<th class="centerTD" data-priority="2" width="10" style="text-align:center"><img style='height: 16px; margin:0 2' src='/images/icons/tp.png'><span></span></th>
						<?php if (isset($is_admin) && $is_admin) : ?>
							<th class="centerTD" width="10">Joué</th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy centerTD" width="68">Dernière fois</th>
						<?php endif ?>
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
						<th class="centerTD" width="10" style="text-align:center"><img style='height: 10px;' src='/images/icons/heart.png'></th>
						<th class="centerTD" width="10" style="text-align:center"><img style='height: 14px; margin:0 2' src='/images/icons/tp.png'></th>
						<?php if (isset($is_admin) && $is_admin) : ?>
							<th>Joué</th>
							<th>Dernière fois</th>
						<?php endif ?>
					</tr>
				</tfoot>
				<tbody id="songlist_body">
					<?php foreach ($list_song_ex as $song): ?>
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
							<?php if (isset($is_admin) && $is_admin) : ?>
								<td><?php echo $song->nbPlayed; ?></td>
								<td><?php echo $song->lastDate;	?></td>
							<?php endif ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
		</div>
	</div>


</div>
