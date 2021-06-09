<!-- Pour le text ellipsis multi-line !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/jQuery.succinct.min.js"></script>

<!-- Bootstrap multiple select !-->
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/bootstrap-select/bootstrap-select.min.css" />
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-select/bootstrap-select.min.js"></script>


<script type="text/javascript">

	$(function() {
	
		$('.truncate').succinct({
			size: 375
		});

		// ****** REPETITION MODALS ********
		$("[id$='RepeModal'").on("show.bs.modal", function(e) {
			var link = $(e.relatedTarget);
			$(this).find(".modal-body").load(link.attr("href"));
		});


		// On initialise le dropdown des catégories
		$('.selectpicker').selectpicker({
			noneSelectedText: "Aucun filtre"
		});
		
		
		// On gère le filtrage
		$('#pupitreSelect').change(function () {
			filtrer();
		});
		filtrer("init");
		
		
		// On fixe le comportement des bouttons d'admin de delete
		$('.list_item_data_box .delete_btn').each(function(index) {
			$(this).on("click", function() {
				repetId = $(this).closest(".list_item").attr('id');
				popup_delete_repet(repetId);
			});
		});
		
		
	});
	
	
	
	// ************** FILTRER REPETITION **************/
	function filtrer(action) {
		
		// On récupère les catégories à filtrer
		filter = $('.selectpicker').val();
		
		// On parcourt les répêt et on filtre
		if (filter != undefined) {
			$(".list_item").each(function() {
				/*if (filter.includes($(this).attr("catId")) || filter.length == 0) $(this).css("display","flex");
				else  $(this).css("display","none");*/
				if (filter.includes($(this).attr("pupitreId")) || filter.length == 0) {
					$(this).stop().fadeIn(100);
					$(this).css({"visibility":"visible",display:'flex'});
				}
				else if ($(this).css("visibility") == "visible" && action != "init")
					$(this).fadeOut(500,function(){
						$(this).css({"visibility":"hidden",display:'block'}).slideUp();
					});
				else $(this).css({"visibility":"hidden",display:'none'});
			});
		}
	}
	
	
	// ******** DELETE REPETITION *********/
	function popup_delete_repet(repetId) {
		$text = "Etes-vous sûr de voulour supprimer la répétition ?";
		$confirm = "<div class='modal-footer'>";
			$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
			$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_repet(\""+repetId+"\")'>Supprimer</button>";
		$confirm += "</div>";
		
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-dialog").addClass("backdrop","static");
		$("#modal_msg .modal-header").html("Supprimer la répétition");
		$("#modal_msg .modal-body").html($text);
		$("#modal_msg .modal-footer").html($confirm);
		$("#modal_msg").modal('show');
	}
	
	
	function delete_repet($repetId) {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_jam/delete_repet'); ?>",
		
			{
			'login':$("#memberLogin").html(),
			'repetId':$repetId
			},
	
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					// On actualise le DOM
					$(".list_item[id="+$repetId+"]").remove();
					$("#modal_msg").modal('hide');
				}
				
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
					$("#modal_msg").modal('show');
				}
			}
		);
	}
	
 </script>


<!----------- REPETITIONS  ------------>

<div class="row">
<div class="panel panel-default">

	<div id="repetBlock" class="row">
	<div class="panel panel-default no-border" style="margin-bottom:0px">
	
		<!-- Heading !-->
		<div class="panel-heading">
			<span class="soften">Répétitions</span>
			
			<!--****** ADMIN ***** Créer une répétition -->
			<?php if (  (isset($is_admin) && $is_admin)  &&  (isset($is_archived) && !$is_archived)  ): ?>
			<div class="form-group pull-right">
				<button class="btn btn-default btn-xs" href="<?php echo site_url("jam/create_repetition/").$jam_item['slug'] ?>" data-remote="false" data-toggle="modal" data-target="#createRepeModal"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;Créer</button>
			</div>
			<?php endif; ?>
			
		</div>
		
		
		<?php if (isset($pupitre_list) && isset($repetitions)): ?>
		<!-- Options !-->
		<div class="row panel-body">
		<div class="col-lg-12">
		
			<div class="form-inline">
				<div class="form-group">
					<select id="pupitreSelect" class="form-control selectpicker show-tick col-sm-8" name="pupitreSelect" multiple title="Affichage">
						<option class="pupitreId_-1" value="-1" <?php if (isset($member)) echo "selected" ?>>Générale</option>
						<?php foreach ($pupitre_list as $pupitre): ?>
							<option class="pupitreId_<?php echo $pupitre['pupitreId'] ?>" value="<?php echo $pupitre['pupitreId'] ?>" <?php if ( ( isset($pupitre['couldPlay']) && $pupitre['couldPlay'] == 1 ) || (isset($is_admin) && $is_admin )  ) echo "selected" ?>><?php echo ucfirst($pupitre['pupitreLabel']); ?></option>
						<?php endforeach ?>
					</select>
				</div>
			</div>
			
		</div>
		</div>
		<?php endif; ?>
		

		
		<!-- **** LISTE DES REPETITIONS **** !-->
		<?php 
			$repemonth = -1;
			$repeyear = -1;
			$firstyear = true;
		?>
		
		<?php if ($repetitions != false) : ?>
			<div class="row">
			<div class="col-lg-12" style="padding-bottom:20px">
			<?php foreach ($repetitions as $ref) : ?>	
			
				<?php
					/* On affiche l'année si besoin */
					if($repeyear < $ref['year']) {
						$repeyear = $ref['year'];
						$repemonth = -1;
						if (!$firstyear) echo "<div class='text-muted event_year small'>".$ref['year']."</div>";
						else $firstyear = false;
					}
					/* On affiche le mois si besoin */
					if($repemonth < $ref['month']) {
						$repemonth = $ref['month'];
						echo "<div class='text-muted event_year small'>".$ref['month_name']."</div>";
					}
				?>
			
				<!-- **** REPETITION **** !-->
				<!-- on disable les répétitions qui sont passées lorsque la jam n'est pas archivées -->
				<div id="<?php echo $ref['id']; ?>" class="list_item panel panel-default <?php /*if ($ref['date_debut'] < date("Y-m-d H:i:s")) echo 'hidden'*/ ?>" pupitreId="<?php echo $ref['pupitreId']; ?>">
		
					<!-- Date !-->
					<div class="date_box" style="font-size: 100%">
						<!--<div><small><?php echo $ref['month_name'] ?></small></div>!-->
						<div><strong><?php echo $ref['day'] ?></strong></div>
						<div><small><?php echo $ref['day_name'] ?></small></div>
					</div>
				
					<!-- Titre, adresse et horaires de la répêt  !-->
					<div class="list_item_title_box">
						<h4 class="panel-heading small" style="font-family:rimouski; font-size: 110%">
							<a <?php if (!isset($member)) echo "class='disabled_link'"; ?> href="<?php echo site_url("jam/view_repetition/").$ref['id']; ?>" data-remote="false" data-toggle="modal" data-target="#viewRepeModal"><?php echo $ref['lieuName']; ?></a>
						</h4>
						<?php $br = array("<br>", "<br />"); ?>
						<div class="panel-body">
							<!-- Si heure_debut == heure_fin => horaires non définis !-->
							<?php if ($ref['heure_debut'] == $ref['heure_fin']) :?>
								<div class="soften"><small>horaires non définis</small></div>
							<?php else : ?>
								<div class="numbers soften"><small><b><?php echo $ref['heure_debut'].' &#10145; '.$ref['heure_fin']; ?></b></small></div>
							<?php endif ?>
						</div>
						
					</div>
				
					<!-- MIDDLE_BLOCK !-->
					<div class="list_item_middle_box">
						<div class="panel-body hidden-xs hidden-sm"><span class="truncate"><?php echo $ref['text']; ?></span></div>
					</div>
				
					<!-- DATA_BLOCK !-->
					<div class="list_item_data_box">
						<div class="panel-body small">
								<div class="row">
									<div class="btn-group btn-group-xs">
										<!-- PARTICIPER   Bouton d'inscription à la jam -->
										<!--<button class="btn btn-default disabled" type="submit" name="join_repet"	onclick="">Participer</button>!-->
									</div>
									<?php if( ( isset($is_admin) && $is_admin )  && !$is_archived) : ?>
									<div class="btn-group btn-group-xs">
										<!-- MODIFIER -->
										<button class="btn btn-default update_btn" href="<?php echo site_url("jam/update_repetition/").$ref['id']; ?>" data-remote="false" data-toggle="modal" data-target="#updateRepeModal"><i class="glyphicon glyphicon-pencil"></i></button>
										<!-- SUPPRIMER -->
										<button class="btn btn-default delete_btn"><i class="glyphicon glyphicon-trash"></i></button>
									</div>
									<?php endif; ?>
								</div>
						</div>
					</div>
					
				</div>
				
			<?php endforeach ?>
			</div>
			</div>
		
		<?php else : ?>
			<div class="panel-default">
				<div class="panel-body alert-warning">
					<i class='glyphicon glyphicon-warning-sign'></i>&nbsp; Aucune répétition n'a encore été calée pour cette jam.
				</div>
			</div>
		<?php endif; ?>
		
	
	</div>
	</div>


</div>
</div>	




<!-- ******** MODAL CREATE REPETITION ******* !-->
<div id="createRepeModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		<div class="modal-header lead">Créer une répétition</div>
		<div class="modal-body">
		...
		</div>
	</div>
	</div>
</div>

<!-- ******** MODAL UPDATE REPETITION ******* !-->
<div id="updateRepeModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		<div class="modal-header lead">Modifier une répétition</div>
		<div class="modal-body">
		...
		</div>
	</div>
	</div>
</div>

<!-- ******** MODAL VIEW REPETITION ******* !-->
<div id="viewRepeModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		<div class="modal-header lead">Répétition...</div>
		<div class="modal-body">
		...
		</div>
	</div>
	</div>
</div>

