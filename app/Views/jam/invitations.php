<!-- flexdatalist pour les input !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.js"></script>
<link href="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.css" rel="stylesheet" type="text/css" />


<!-- Tablesorter -->
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/jquery.tablesorter.js"></script>
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-filter.js"></script>


<script type="text/javascript">

	$(function() {
	
		// FLEXDATALIST MEMBRES
		$('#memberInput').flexdatalist({
			 minLength: 0,
			 selectionRequired: true,
			 data: [
					<?php foreach ($list_membres as $membre): ?>
						{ 'id':'<?php echo $membre->id ?>',
							'pseudo':'<?php echo addslashes(htmlspecialchars($membre->pseudo)) ?>',
							'nom':'<?php echo addslashes(htmlspecialchars($membre->nom)) ?>',
							'prenom':'<?php echo addslashes(htmlspecialchars($membre->prenom)) ?>',
							'mainInstru':'<?php echo addslashes(htmlspecialchars($membre->mainInstruName)) ?>'
						},
					<?php endforeach ?>
					],
			 searchIn: ["pseudo","prenom","nom","mainInstru"],
			 visibleProperties: ["pseudo","prenom","nom","mainInstru"],
			 searchByWord: true,
			 valueProperty: ['id','pseudo']	// on envoie l'attribut 'id' quand on appelle la méthode val()
			});
		
		
		// MEMBERLIST CHANGE
		// Gestion du bouton d'ajout d'admin en fonction de l'input
		$('#memberInput.flexdatalist').on('change:flexdatalist', function(event, set, options) {
			if ($('#memberInput').val() == '') $("#addBtn").addClass("disabled");
			else $("#addBtn").removeClass("disabled");
		});

		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// On remplit la liste d'invitations
		$.post("<?php echo site_url(); ?>/ajax/get_invitations",
		
			{
				'targetTag': 2,		// 1 : group	2 : jam		3 : repetition
				'targetId': <?php echo $jam_item['id']; ?>
			},
			
			function (return_data) {
				
				//console.log("$:get_invitations : "+return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {

					if ($obj['data'].length > 0) {
						
						// On rempli le tableau
						$.each($obj['data'], function(index) {
							// On rajoute l'invit à l'ui
							add_invitation($obj['data'][index], index%2 == 1);
							
						});
						
						// On update le tableau pour le trie
						var resort = true;
						$("#invitTab").trigger("updateAll", [ resort ]);
						
						// On s'asssure que le tableau est visible
						update_style();
						if ($("#list").hasClass("hidden")) $("#list").removeClass("hidden");
						if (!$(".alert").hasClass("hidden")) $(".alert").addClass("hidden");
					}
					// Aucune invitation
					else {
						$(".alert").removeClass("hidden");
					}
					//console.log("data2 : "+$('#memberInput').flexdatalist('data').length);
					
				}
				else {
					console.log($obj['data']);
				}
			}
		);
		
		
		// TABLESORTER
		$table1 = $("#invitTab").tablesorter({
			theme: 'sand',
			headers: {'th' : {sorter: true}},
			widgets: ['zebra', 'filter'],
		});
		
	});
	
	
	
	/************** INVITATIONS ************/
	
	// Requête ajax d'ajout d'admin   !! le boutton d'ajout garanti l'existence de la saisie dans la liste des membres
	function add_request() {

		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/add_invitation",
		
			{	
				'senderId': $("#memberLogin").attr("idKey"),
				'receiverId': $("#memberInput").flexdatalist('value')['id'],	// renvoie l'id du membre
				'targetTag': 2,		// 1 : group	2 : jam		3 : repetition
				'targetId': <?php echo $jam_item['id']; ?>
			},
		
			function (return_data) {
				
				//console.log("add_request : "+return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// On rempli le tableau
					add_invitation($obj['data'], ($("#list").length)%2 == 1);
					
					// On update le tableau pour le trie
					var resort = true;
					$("#invitTab").trigger("updateAll", [ resort ]);
					
					// On s'asssure que le tableau est visible
					update_style();
					if ($("#list").hasClass("hidden")) $("#list").removeClass("hidden");
					
				}
				else {
					console.log($obj['data']);
				}
			}
		);
	}
	
	
	// Ajout d'admin dans la liste et retrait dans la flexdatalist
	function add_invitation($invit, even) {
	
		//console.log("******** add_invitation : "+JSON.stringify($invit));
		//console.log("******** add_invitation : "+$invit['receiverPseudo']);
	
		// On créé l'item qui sera affiché
		$listElem = $("<tr id='"+$invit['id']+"' receiverId='"+$invit['receiverId']+"' pseudo='"+$invit['receiverPseudo']+"' prenom='"+$invit['receiverPrenom']+"' nom='"+$invit['receiverNom']+"' mainInstru='"+$invit['receiverMainInstruName']+"'></tr>");
		
			// Pseudo
			$content = "<td id='pseudo'>"+$invit['receiverPseudo']+"</td>";
			// Prénom + Nom
			$content += "<td id='prenom'>"+$invit['receiverPrenom']+"&nbsp;"+$invit['receiverNom']+"</td>";
			// Nom
			//$content += "<td id='nom'>"+$invit['receiverNom']+"</td>";
			// Instru
			$content += "<td id='mainInstru'>"+$invit['receiverMainInstruName']+"</td>";
			
			// Invité par
			$content += "<td id='senderPseudo'>"+$invit['senderPseudo']+"</td>";
			
			// State et couleur de la tr
			if ($invit['state'] == -1) {
				$state = '<span class="hidden">-1</span><i class="bi bi-question-circle bi_nopadding"></i>';
				$color = '';
			}
			else if ($invit['state'] == 0) {
				$state = '<span class="hidden">0</span><i class="bi bi-x-circle bi_nopadding"></i>';
				$color = "red";
			}
			else if ($invit['state'] == 1) {
				$state = '<span class="hidden">1</span><i class="bi bi-check-circle bi_nopadding"></i>';
				$color = "green";
			}
			$content += "<td id='state' class='"+$color+"'>"+$state+"</td>";
			
			// Supprimer
			$content += "<td id='suppr'><i class='btn disabled bi bi-trash bi_nopadding'></i></td>";
			
		$listElem.append($content);
		
		// On définit le btn delete
		if ($invit['senderId'] == $("#memberLogin").attr("idKey") || <?php echo $isSuperAdmin ? 1 : 0; ?>) {
			$listElem.find("#suppr .btn").removeClass("disabled");
			
			$listElem.find("#suppr .btn").on({
				click: function(event) {
					delete_invitation_request($(this).parents("tr").attr("id"));
					event.preventDefault();
				}
			});
		}
	
	
		// On stylise la ligne
		if (even) $listElem.addClass("even");
		else $listElem.addClass("odd");
	
		//console.log("********  : "+$listElem.html());
	
		// On ajoute l'admin à la liste
		$("#invitTab tbody").append($listElem);
		
		
		// On actualise la FLEXDATALIST
		// On vide l'input
		$("#memberInput").val('');
		// Index du receiver dans la flexdatalist
		foundIndex = $('#memberInput').flexdatalist('data').findIndex(x => x.id === $invit["receiverId"]);
		// On retire cette valeur de la flexdatalist
		$('#memberInput').flexdatalist('data').splice(foundIndex, 1);

	}
	
	
	// Requête ajax pour retirer un admin de l'event
	function delete_invitation_request($invitId) {
		
		//console.log("delete_invitation_request : "+$invitId);
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/delete_invitation",
		
			{	
				'invitId': $invitId
			},
			
			function (return_data) {
				
				//console.log("ajax::delete_invitation : "+return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// On rajoute l'élément supprimé dans la flexdatalist
					$('#memberInput').flexdatalist('data').push({					
							'id': $("#invitTab tbody tr[id="+$invitId+"]").attr("receiverId"),
							'pseudo': $("#invitTab tbody tr[id="+$invitId+"]").attr("pseudo"),
							'nom': $("#invitTab tbody tr[id="+$invitId+"]").attr("nom"),
							'prenom': $("#invitTab tbody tr[id="+$invitId+"]").attr("prenom"),
							'mainInstru': $("#invitTab tbody tr[id="+$invitId+"]").attr("mainInstru"),
					});
					
					// On remove l'invit du tableau
					$("#invitTab tbody tr[id="+$invitId+"]").remove();
		
					// On actualise l'affichage si plus aucune invit (message)
					if ($("#invitTab tbody tr").length == 0) {
						$("#list").addClass("hidden");
						$(".alert").removeClass("hidden");
					}
				}
			}
		);
	}

 </script>


<!----------- INVITATIONS  ------------>

<!-- Formulaire !-->
<div id="invitations_form" class="">

	<form class="form-horizontal">

		<!--------------  INPUT  ----------------->
		<div class="form-group">
			<label class="control-label col-sm-1" for="memberInput"><i class="bi bi-search"></i></label>
			<div class="input-group col-sm-10">
				<input id="memberInput" class="form-control flexdatalist" type="input" name="memberInput" placeholder="Membres du gro">
				<div class="input-group-btn">
					<button id="addBtn" class="btn btn-default disabled" type="button" onclick="add_request()">
						<i class="glyphicon glyphicon-plus"></i>
					</button>
				</div>
			</div>			
		</div>
	</form>
	
		
	<!---------------- TABLEAU INVITATIONS ----------------------->	
	
	<!-- Affichage des membres invités -->
	<div id="list" class="hidden">
		<table id="invitTab" class="tablesorter focus-highlight" cellspacing="0">
			<thead>
				 <tr>
					<th>Pseudo</th>
					<th></th>
					<th class="centerTD">Instru</th>
					<th class="centerTD">Invit par</th>
					<th class="centerTD" style="width: 30px">Etat</th>
					<th class="centerTD dark" style="width: 30px"></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Pseudo</th>
					<th></th>
					<th class="centerTD">Instru</th>
					<th class="centerTD">Invit par</th>
					<th class="centerTD">Etat</th>
					<th class="centerTD"></th>
				</tr>
			</tfoot>
			<tbody>
				
			</tbody>
		</table>
	</div>
	
	<!-- Alerte aucune invitations -->
	<div class="alert alert-warning alert-dismissible hidden">
		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<i class="bi bi-exclamation-triangle"></i>&nbsp; Il n'y a actuellement aucune invitation pour cette jam.
	</div>
	
	
</div>
