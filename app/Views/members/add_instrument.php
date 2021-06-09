

<script type="text/javascript">

	$(function() {
	
		/**************************** Instrument IHM ***************************/
		
		$("#addInstruModal #family").selectpicker('val', 'Famille d\'instrument');
		$("#addInstruModal #instru").selectpicker('val', 'Instrument');
		
		
		// On affiche la liste d'instrument quand la famille d'instrument a été select
		$("#addInstruModal select[id='family']").change(function() {
			
			// On hide l'instruList
			if ($("#addInstruModal #listInstru").css('display') == 'block') $("#addInstruModal #listInstru").fadeOut("fast");
			
			
			// On change le curseur
			document.body.style.cursor = 'wait';

			// Requète ajax au serveur
			$.post("<?php echo site_url('ajax_instruments/get_family_instru_list'); ?>",
			
				{
					'familyId' : $("#addInstruModal #family").val()
				},
		
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					
					// On change le curseur
					document.body.style.cursor = 'default';
					
					if ($obj['state'] == 1) {
						
						// On vide la liste
						$("#instru").empty();
						
						// On remplit la liste
						$optionBlock = "";
						for (i = 0; i < $obj['data'].length; i++) {
							$optionBlock += "<option value='"+$obj['data'][i]["instruId"]+"'>"+$obj['data'][i]["name"]+"</option>";
						}
						$("#instru").append($optionBlock);
						$("#instru").selectpicker('refresh');				
					}

					
					// On disable le boutton d'action
					$("#addInstruModal #actionBtn").addClass("disabled");
					
					
					// On disabled les instruments déjà joués					
					$("#listInstruDiv .instruItem").each(function() {
						$("#addInstruModal #instru option[value="+$(this).attr("instruId")+"]").prop("disabled", true);
						$("#addInstruModal #instru").selectpicker('refresh');
					});

					
					// On affiche la liste des instruments
					$("#addInstruModal #listInstru").fadeIn("fast");
					
			});
			
		});
		
		
		// On disabled le boutton d'update si pas d'instru select
		$("#addInstruModal #modalForm select[id='instru']").change(function() {
			
			// On active le btn d'action
			$("#addInstruModal #actionBtn").removeClass("disabled");
			
		});
		

	});

	
	
	/************************ INSTRUMENT ***********************/
	//////////////////////////////////////////////////////////////
	
	
	function add_instrument()  {
		
		// On récupère l'instruId à ajouter
		$instruId = $("#instru").val();
		
		// On récupère le memberId
		$memberId = <?php echo isset($member_item) ? $member_item->id : -1 ?>;

		
		// Si on est dans un update de profil, on actualise à la volée la BD
		if ($memberId != -1) {
			
			// On change le curseur
			document.body.style.cursor = 'wait';
			
			// Requète ajax au serveur
			$.post("<?php echo site_url('ajax_instruments/add_member_instrument'); ?>",
			
				{
					'instruId' : $instruId,
					'memberId' : $memberId
				},
		
				function (return_data) {
								
					$obj = JSON.parse(return_data);
					
					// On change le curseur
					document.body.style.cursor = 'default';
					
					if ($obj['state'] == 1) {
						
						// On cache la modal
						$("[id$='InstruModal'").modal("hide");
						
						// L'instrument à été ajouté dans la bd => on actualise l'affichage
						show_instruList();
					}
					else console.log("error : " + $obj['data']);
				}
			);
		}
		
		// Le profil n'existe pas encore, on update juste l'ihm
		else {
						
			$numInstru = $("#listInstruDiv").children().length+1;
			$instruId = $("#addInstruModal #listInstru :selected").val();
			$instruName = $("#addInstruModal #listInstru :selected").html();
			
			// On remplit la liste
			$div = "";
			$div += "<div class='form-group instruDiv'>";
				$div += "<label class='control-label col-sm-2' style='white-space: nowrap'>Instrument "+$numInstru+"</label>";
				$div += "<div class='btn-group col-sm-5'>";
					<!-- Label !-->
					$div += "<div class='btn btn-static instruItem coloredItem' instruId='"+$instruId+"' instruName='"+$instruName+"'>&nbsp;&nbsp;&nbsp;&nbsp;"+$instruName+"&nbsp;&nbsp;&nbsp;&nbsp;</div>";
					<!-- Modifier -->
					$div += '<button class="btn btn-default update_btn" href="<?php echo site_url("members/update_instrument/-1/"); ?>'+$instruId+'") /'+$instruId+'" data-remote="false" data-toggle="modal" data-target="#updateInstruModal"><i class="glyphicon glyphicon-pencil"></i></button>';
					<!-- Supprimer -->
					$div += '<button class="btn btn-default delete_btn" title="Supprimer instrument"><i class="glyphicon glyphicon-trash"></i></button>';
				$div += "</div>";
			$div += "</div>";
			$("#listInstruDiv").append($div);
			
			
			// ADMIN IHM
			// On fixe le comportement des bouttons d'admin de delete
			$('#listInstruDiv').last().find('.delete_btn').on("click", function() {
				$(this).parents(".instruDiv").remove();
			});
			
			// On ferme la modal
			$("#addInstruModal").modal("hide");
		}
    }
	
 </script>


	<!-- MODAL CONTENT !-->
	<!-- Header !-->
	<div class="modal-header lead">Ajouter un instrument</div>
	
	<!-- Body !-->
	<div class="modal-body">
		<div class="container-fluid" style="padding-bottom:15px">
		
			<form id="modalForm" class="form-horizontal" action="javascript:void(0)">
				
					<!-- Famille instru !-->
					<div class="col-sm-6">
						<select id="family" class="selectpicker show-tick" name="family" title="Famille d'instrument">
							<?php foreach ($famille_instru_list as $famille): ?>
								<option value="<?php echo $famille['id']; ?>"><?php echo $famille['label']; ?></option>
							<?php endforeach ?>
						</select>
					</div>
						
						
					<!-- Liste instru !-->
					<div class="col-sm-6">
						<div id="listInstru" style="display:none">
							<select id="instru" class="selectpicker show-tick" name="instru" title="Instrument">
							</select>
						</div>
					</div>
				
			</form>

		</div>
	</div>
	
	<!-- Footer !-->
	<!-- Btn Ajouter un instrument !-->
	<div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
		<button id="actionBtn" class="btn btn-primary disabled" onclick="javascript:add_instrument()">Ajouter</button>
	</div>
	