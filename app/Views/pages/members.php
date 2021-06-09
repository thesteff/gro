<?php
	// On récupère les variables de sessions
	$session = \Config\Services::session();
?>

<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/jquery.tablesorter.js"></script>

<!-- Tablesorter: filter -->
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-filter.js"></script>

<!-- Tablesorter -->
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-pager.js"></script>
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-columnSelector.js"></script>


<script type="text/javascript">

	$(function() {
		
		// ****** SEND_MAIL MODAL ********
		$("[id$='Modal'").on("show.bs.modal", function(e) {
			var link = $(e.relatedTarget);
			$(this).find(".modal-body").load(link.attr("href"));
		});
		
		
		// Hack pour faire marcher le CKEditor dans une modal
		$.fn.modal.Constructor.prototype.enforceFocus = function() {
			$( document )
			.off( 'focusin.bs.modal' ) // guard against infinite focus loop
			.on( 'focusin.bs.modal', $.proxy( function( e ) {
				if (
					this.$element[ 0 ] !== e.target && !this.$element.has( e.target ).length
					// CKEditor compatibility fix start.
					&& !$( e.target ).closest( '.cke_dialog, .cke' ).length
					// CKEditor compatibility fix end.
				) {
					this.$element.trigger( 'focus' );
				}
			}, this ) );
		};

		
		// ****** TABLESORTER ********
		var $table1 = $( '#memberlist' ).tablesorter({
		
			theme : 'sand',

			// initialize zebra and filter widgets
			widgets : [ "zebra", "filter", "pager", 'columnSelector' ],
			
			dateFormat : "ddmmyy",

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
			}

		});
		
		// On stylise les colonnes
		update_style();


	});
	
	
	/* Transférer les vieux idInstru à la liste d'instrument dynamique */
	function go() {
		console.log("GO!");
		
		// On change le curseur
		document.body.style.cursor = 'progress';
		

		$.post("<?php echo site_url(); ?>/ajax_instruments/transfert_member_instrument",
		
			{},
	
			function (return_data) {
								
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					console.log("Transfer complete !!");
				}
				else console.log("error");
			}
		);
	}
	
	
 </script>
 

<div class="panel panel-default row">

	<!-- Header !-->
	<div class="row panel-heading panel-bright title_box">
		<h4><?php echo $page_title; ?></h4>
	</div>

	
	<!-- Options !-->
	<div class="row">
	<div class="panel-body col-lg-12">
		
	
		<!-- BOUTTONS SUPER ADMIN   -->
		<?php if($session->admin == "1") : ?>
		<div class="row">			
			<div class="btn-group">
				<!-- ENVOYER UN MAIL AU GROUPE -->
				<button class="btn btn-default" href="<?php echo site_url();?>/group/send_mail" data-remote="false" data-toggle="modal" data-target="#MailModal"><i class="cr-icon glyphicon glyphicon-envelope"></i><span class="hidden-xs">&nbsp;&nbsp;&nbsp;&nbsp;Envoyer un mail au groupe</span></button>
			</div>	
		</div>
		<?php endif ?>
	
	</div>  <!-- PANEL BODY !-->
	</div>
	
	<!-- PAGER -->	
	<div class="pager form-inline" style="margin-top: 0px">

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
		
		<!-- Transferer les nouveaux instruments !-->
		<!--<button type="button" class="btn btn-default" onclick="javascript:go()"><span class="glyphicon glyphicon-warning"></span>GO !</button> !-->

		
	</div>
	
	<!-- NBREF -->	
	<div class="small_block_list_title soften pull-right"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($list_members); ?></span> références)</small></span></div>

	<!-- MEMBER LIST -->
	<div class="row">
		<div class="col-lg-12">
			<table id="memberlist" class="tablesorter lineHighLight" cellspacing="0">
				<thead>
					<tr>
						<th data-priority="4" class="centerTD" style="width:15px">&nbsp;</th>
						<th data-priority="critical">Pseudo</th>
						<th data-priority="5">Prénom</th>
						<th data-priority="5">Nom</th>
						<th data-priority="2">Email</th>
						<th data-priority="6" class="centerTD">Age</th>
						<th data-priority="6" class="centerTD">Genre</th>
						<th data-priority="critical" class="centerTD">Mobile</th>
						<th data-priority="critical" class="centerTD" style="width:15px">Famille</th>
						<th data-priority="3" class="centerTD">Instru</th>
						<th data-priority="6" class="sorter-shortDate dateFormat-ddmmyyyy centerTD">Inscr.</th>
						<th data-priority="6" class="sorter-shortDate dateFormat-ddmmyyyy centerTD">Access</th>
						<th data-priority="6" class="centerTD" style="width:10px">Valid.</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>&nbsp;</th>
						<th>Pseudo</th>
						<th>Prénom</th>
						<th>Nom</th>
						<th>Email</th>
						<th>Age</th>
						<th>Genre</th>
						<th>Mobile</th>
						<th>Pupitre</th>
						<th>Instru</th>
						<th>Inscr.</th>
						<th>Access</th>
						<th>Valid.</th>
					</tr>
				</tfoot>
				<tbody id="memberlist_body">
						<?php 
							foreach ($list_members as $tmember) {
								echo '<tr>';
									echo '<td>'.($tmember->admin > 0 ? "<span style='display:none'>1</span><img style='height: 14px' src='/images/icons/gear.png'>" : "<span style='display:none'>0</span>").'</td>';
									echo '<td><b>'.$tmember->pseudo.'</b></td>';
									echo '<td>'.$tmember->prenom.'</td>';
									echo '<td>'.$tmember->nom.'</td>';
									echo '<td class="email_used">'.$tmember->email.'</td>';
									
									// Age
									echo '<td>'.$tmember->age.'</td>';
									
									// Genre
									echo '<td><span style="display:none">'.$tmember->genre.'</span>';
										switch ($tmember->genre) {
											case 0:
												echo "<b>?</b>";
												break;
											case 1:
												echo "<img style='height: 16px' src='/images/icons/man.png'>";
												break;
											case 2:
												echo "<img style='height: 16px' src='/images/icons/woman.png'>";
												break;
										}
									echo '</td>';
									
									// Mobile(s)
									echo '<td class="nobr">';
										if ($tmember->mobile) echo substr($tmember->mobile,0,2).' '.substr($tmember->mobile,2,2).' '.substr($tmember->mobile,4,2).' '.substr($tmember->mobile,6,2).' '.substr($tmember->mobile,8,2);
									echo '</td>';
									
									// Famille de l'instrument principal
									echo '<td>'.$tmember->mainFamily.'</td>';
									// Instrument(s)
									echo '<td>';
										echo $tmember->instruList;
									echo '</td>';
									
									// Dates
									echo '<td>'.$tmember->date_inscr.'</td>';
									echo '<td>'.$tmember->date_access.'</td>';
									
									// Email valid
									//echo '<td>'.$tmember->validMail.'</td>';
									echo '<td>';
									if ($tmember->validMail == 1) echo "<span style='display:none'>1</span><img style='height: 12px' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span><img style='height: 12px' src='/images/icons/x.png'>";
									echo '</td>';
									
								echo '</tr>';
							}
						?>
					</tbody>
			</table>
			
		</div>
	</div>

</div>



<?php if ($session->admin == "1" ): ?>
<!-- ******** MODAL CREATE News ******* !-->
<div id="MailModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg default">
	<div class="modal-content">
		<div class="modal-header lead">Envoyer un mail au groupe</div>
		<div class="modal-body">
		...
		</div>
	</div>
	</div>
</div>
<?php endif ?>