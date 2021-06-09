

<script type="text/javascript">

	$(function() {
	
		/**************************** Instrument IHM ***************************/

		// On affiche la liste d'instrument quand la famille d'instrument a été select
		$("#updateInstruModal select[id='family']").change(function() {
			
			// On hide l'instruList
			if ($("#updateInstruModal #listInstru").css('display') == 'block') $("#updateInstruModal #listInstru").fadeOut("fast");
			
			// On change le curseur
			document.body.style.cursor = 'wait';

			// Requète ajax au serveur
			$.post("<?php echo site_url('ajax_instruments/get_family_instru_list'); ?>",
			
				{
					'familyId' : $("#updateInstruModal #family").val()
				},
		
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					
					// On change le curseur
					document.body.style.cursor = 'default';
					
					if ($obj['state'] == 1) {
						
						// On vide la liste
						$("#updateInstruModal #instru").empty();
						
						// On remplit la liste
						$optionBlock = "";
						for (i = 0; i < $obj['data'].length; i++) {
							$optionBlock += "<option value='"+$obj['data'][i]["instruId"]+"'>"+$obj['data'][i]["name"]+"</option>";
						}
						$("#updateInstruModal #instru").append($optionBlock);
						$("#updateInstruModal #instru").selectpicker('refresh');				
					}

					
					// On disabled les instruments déjà joués en parcourant listInstruDiv				
					$("#listInstruDiv .instruItem").each(function() {
						$("#updateInstruModal #instru option[value="+$(this).attr("instruId")+"]").prop("disabled", true);
						$("#updateInstruModal #instru").selectpicker('refresh');
					});

					
					// Si on vient d'ouvrir le update ...
					if ($first) {
						
						// ... il y a déjà un instru select
						$("#updateInstruModal #instru").selectpicker('val', <?php echo $instruId ?>);
						
						// On disable le boutton d'action
						$("#updateInstruModal #actionBtn").addClass("disabled");
						$first = false;
					}
					
					// Sinon on cale le select sur une valeur par défaut
					else $("#updateInstruModal #instru").selectpicker('val', 'Instrument');
					
					// On affiche la liste des instruments
					$("#updateInstruModal #listInstru").fadeIn("fast");
					
			});
			
		});

		
		// On disabled le boutton d'update si pas d'instru select
		$("#updateInstruModal #modalForm select[id='instru']").change(function() {
			// On active le btn d'action
			if ($("#updateInstruModal #instru").val() != <?php echo $instruId ?>) 
				$("#updateInstruModal #actionBtn").removeClass("disabled");
			else $("#updateInstruModal #actionBtn").addClass("disabled");
			
		});
		
		
		// On initialise les select
		$("#updateInstruModal #family").selectpicker('val', <?php echo $famille_item->id ?>);
		
		// On update l'ihm en récupérant la liste d'instru + les disabled par rapport à l'ihm
		$("#updateInstruModal select[id='family']").change();
		
		// Permet de savoir si on vient d'ouvrir la modal
		$first = true;

	});

	
	
	/************************ INSTRUMENT ***********************/
	//////////////////////////////////////////////////////////////
	
	
	function update_instrument() {
		
		// On récupère l'instruId à ajouter
		$instruId = $("#updateInstruModal #instru").val();
		
		// On récupère le memberId
		$memberId = <?php echo isset($member_item) ? $member_item->id : -1 ?>;
		
		// Si on est dans un update de profil, on actualise à la volée la BD
		if ($memberId != -1) {
			
			// On change le curseur
			document.body.style.cursor = 'wait';

			// Requète ajax au serveur
			$.post("<?php echo site_url('ajax_instruments/update_member_instrument'); ?>",
			
				{
					'instruId' : $instruId,
					'oldInstruId' : <?php echo $instruId ?>,
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
					else console.log("error");
					
					
				}
			);
		}
		
		// Le profil n'existe pas encore, on update juste l'ihm
		else {
			// On récupère l'item
			$instruItem = $("#listInstruDiv").find(".instruItem[instruId=<?php echo $instruId ?>]");
			
			// On récupère les infos du nouvel instru select
			$instruId = $("#updateInstruModal #listInstru :selected").val();
			$instruName = $("#updateInstruModal #listInstru :selected").html();

			// On change l'item
			$instruItem.attr("instruId",$instruId);
			$instruItem.attr("instruName",$instruName);
			$instruItem.html("&nbsp;&nbsp;&nbsp;&nbsp;"+$instruName+"&nbsp;&nbsp;&nbsp;&nbsp;");
			
			// On change l'action de l'update de l'item
			$instruItem.siblings(".update_btn").attr("href","<?php echo site_url('members/update_instrument/-1/'); ?>"+$instruId);
			
			// On ferme la modal
			$("#updateInstruModal").modal("hide");
		}
    }
	
	
 </script>


	<!-- MODAL CONTENT !-->
	<!-- Header !-->
	<div class="modal-header lead">Modifier un instrument</div>
	
	<!-- Body !-->
	<div class="modal-body">
		<div class="container-fluid" style="padding-bottom:15px">
		
			<form id="modalForm" class="form-horizontal" action="javascript:void(0)">
				
					<!-- Famille instru !-->
					<div class="col-sm-6">
						<select id="family" class="selectpicker show-tick" name="family" title="Famille d'instrument">
							<?php foreach ($famille_instru_list as $famille): ?>
								<option value="<?php echo $famille['id']; ?>">
									<?php echo $famille['label']; ?>
								</option>
							<?php endforeach ?>
						</select>
					</div>
						
						
					<!-- Liste instru !-->
					<div class="col-sm-6">
						<div id="listInstru">
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
		<button id="actionBtn" class="btn btn-primary disabled" onclick="javascript:update_instrument()">Modifier</button>
	</div>
	