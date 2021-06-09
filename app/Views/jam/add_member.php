<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/validator.js"></script>


<script type="text/javascript">
	
	$(function() {
		
		/******** Bootstrap validator ********/
		$('#add_member_form form').validator().on('submit', function (e) {
			
			if (e.isDefaultPrevented()) {
				// handle the invalid form...
			}
			else {
				// On bloque le comportement par défault du submit
				e.preventDefault();
				// Pas de problem avec le validator, on ajoute le membre à la jam

				
				// On change le curseur
				document.body.style.cursor = 'wait';
			
				// Requète ajax au serveur
				$.post("<?php echo site_url('ajax_jam/add_inscription'); ?>",
				
					{
					'jamId': <?php echo $jam_item['id'] ?>,
					'memberId':$("#add_member_form #memberId").html()
					},
			
					function (return_data) {
					
						$data = JSON.parse(return_data);
						// On change le curseur
						document.body.style.cursor = 'default';
						
						// On ferme la modal initiale
						$("#addModal").modal('hide');
						
						// Modal
						if ($data['state'] == 1) {
							// Succés
							$("#modal_msg").modal({backdrop: 'static', keyboard: true });
							$("#modal_msg .modal-dialog").removeClass("error");
							$("#modal_msg .modal-dialog").addClass("success");
							$("#modal_msg .modal-header").html("Membre ajouté.");
							$("#modal_msg .modal-body").html($data['data']);
							$("#modal_msg .modal-footer").html('<button type="button" class="btn" id="modal_close" href="#" onclick="javascript:location.reload()">Fermer</button>');
							$("#modal_msg").modal('show');
						}
						else {
							// Erreur
							$("#modal_msg").modal({backdrop: 'static', keyboard: true });
							$("#modal_msg .modal-dialog").removeClass("success");
							$("#modal_msg .modal-dialog").addClass("error");
							$("#modal_msg .modal-header").html("Erreur !");
							$("#modal_msg .modal-body").html($data['data']);
							$("#modal_msg .modal-footer").html('<button type="button" class="btn" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
							$("#modal_msg").modal('show');
						}
						
					}
				);
				
			}
		})
		
	});
	
	
	function reset() {
		if ($("#add_member_form #member_details").css("display") == "block") {
			$("#add_member_form #member_details").css("display","none");
			$("#add_member_form #prenom_nom").empty();
			$("#add_member_form #instru1_span").empty();
			$("#add_member_form #email_span").empty();
		}
	}
	
	
	/************** MEMBER ************/
	function member_change($action) {
	
		if ($("#add_member_form #pseudo").val() == "") {
			reset();
			return;
		}

		// On cherche si la saisie existe dans le datalist
		var searched = $("#add_member_form #pseudo").val();
		var members_array = document.getElementById("members");
		var state = "";
		var i = 0;
		while (i < members_array.options.length && state != 'find') {
			if (members_array.options[i].innerHTML == searched) state = 'find'
			i++;
		}
		
		// Si la saisie n'est pas présente dans la liste des membres, on empty les détails et on quitte
		if (state != 'find') {
			reset();
			return;
		}
		
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_members/get_member_and_listInstru'); ?>",
		
			{
			'pseudo':$("#add_member_form #pseudo").val()
			},
	
			function (return_data) {
			
				//console.log(return_data);
				$data = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				$("#add_member_form #member_details").css("display","block");
				
				// On actualise l'id hidden du membre (évite une recherche de l'id dans la datalist sur un add)
				$("#add_member_form #memberId").empty();
				$("#add_member_form #memberId").append($data["member"].id);
				
				if ($data["member"].nom.length) {
					$("#add_member_form #prenom_nom").empty();
					$("#add_member_form #prenom_nom").append("<b>"+$data["member"].prenom+" "+$data["member"].nom+"</b>");
					$("#add_member_form #prenom_nom").css("display","block");
				}
				if (typeof $data["listInstru"][0].instruName !== 'undefined' && $data["listInstru"][0].instruName.length) {
					$("#add_member_form #instru1_span").empty();
					$("#add_member_form #instru1_span").append($data["listInstru"][0]['instruName']);
					$("#add_member_form #instru1").css("display","block");
				}
				if ($data["member"].email.length) {
					$("#add_member_form #email_span").empty();
					$("#add_member_form #email_span").append($data["member"].email);
					$("#add_member_form #email").css("display","block");
				}
			}
		);
		
	}
	
 </script>
 
 
<!-- Formulaire !-->
<div id="add_member_form" class="container-fluid">
	<form role="form" class="form-horizontal" data-toggle="validator">

		<!-------- LIEU --------->
		<div class="form-group">
			<div class="col-sm-12">
				<label for="pseudo" class="hidden">Pseudo</label>
				<input id="pseudo" class="form-control" list="members" type="text" name="membre"
						autocomplete="off" oninput="member_change('input')" placeholder="Saisissez le pseudo d'un membre"
						required />
				<datalist id="members">
				<?php foreach ($list_members as $member): ?>
					<option value="<?php echo $member->pseudo ?>"><?php echo $member->pseudo; ?></option>
				<?php endforeach ?>
				</datalist>
				<!-- On affiche les détails s'il y en a !-->
				<div id="member_details" class="soften small panel panel-default" style="display:none; padding: 5px 10px; margin-bottom: 0px">
					<div id="memberId" class="hidden"></div>
					<span id="prenom_nom" style="display:none; padding-bottom:4px"></span>
					<div id="instru1" style="display:none"><i class="glyphicon glyphicon-music"></i>&nbsp;&nbsp;<span id="instru1_span"></span></div>
					<div id="email" style="display:none"><i class="glyphicon glyphicon-envelope"></i>&nbsp;&nbsp;<span id="email_span"></span></div>
				</div>
			</div>
		</div>
		
		<hr>
		
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
			<button id="addBtn" type="submit" class="btn btn-primary">Ajouter</button>
		</div>

	</form>
</div>