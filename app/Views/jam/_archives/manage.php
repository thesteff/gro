<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/jquery.tablesorter.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-filter.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-columnSelector.js"></script>

<!-- Editeur html -->
<script src="<?php echo base_url();?>ressources/ckeditor/ckeditor.js"></script>

<script type="text/javascript">

	/* ******************* Gestion des tableaux ****************/
	$(function() {
	
		// initialize column selector using default settings
		// note: no container is defined!
		$table1 = $("#tab").tablesorter({
			theme: 'sand',
			headers: {'th' : {sorter: true}}, 	 // le tableau n'est pas triable
			widgets: ['zebra', 'columnSelector'],
			widgetOptions : {
				
				// remember selected columns (requires $.tablesorter.storage)
				columnSelector_saveColumns: true,
				
				// Responsive Media Query settings //
				// enable/disable mediaquery breakpoints
				columnSelector_mediaquery: true,
				// toggle checkbox name
				columnSelector_mediaqueryName: 'Auto',
				// breakpoints checkbox initial setting
				columnSelector_mediaqueryState: true,
				// hide columnSelector false columns while in auto mode
				columnSelector_mediaqueryHidden: true,
			}
		});
		
		// call this function to copy the column selection code into the popover
		$.tablesorter.columnSelector.attachTo( $('#tab'), '#popover-target');

		// Button qui gère affichage
		$('#popover').popover({
			placement: 'right',
			html: true, // required if content has HTML
			content: $('#popover-target')
		});
		
		
		
		
		$table2 = $("#tab2").tablesorter({
			theme: 'sand',
			cssChildRow: "tablesorter-childRow",
			headers: {'th' : {sorter: true}}, 	 // le tableau n'est pas triable
			widgets: ['zebra', 'columnSelector'],
			widgetOptions : {
				
				// remember selected columns (requires $.tablesorter.storage)
				columnSelector_saveColumns: true,
				
				// Responsive Media Query settings //
				// enable/disable mediaquery breakpoints
				columnSelector_mediaquery: true,
				// toggle checkbox name
				columnSelector_mediaqueryName: 'Auto',
				// breakpoints checkbox initial setting
				columnSelector_mediaqueryState: true,
				// hide columnSelector false columns while in auto mode
				columnSelector_mediaqueryHidden: true,
			}
		});
		
		// Gère le childrow des stagiaires
		$("#tab2").delegate('.toggle', 'click' ,function(){
			$(this).closest('tr').nextUntil('tr:not(.tablesorter-childRow)').find('td').toggle();
			return false;
		});
		
		// hide child rows
		$('.tablesorter-childRow td').hide();
		
		
		// On gère le DOUBLE CLICK (changement d'état cheque reçu)
		$("td.chequeCell").each(function() {
			$(this).dblclick(function() {
				// On récupère l'id du stage_membres_relation
				$tmemberId = $(this).parent().attr("tmemberId");
				// Si déjà coché
				if ($(this).children("span").html() == "1") {
					// Popup confirm
					msgBody = "<p>Etes-vous sûr de vouloir changer l'état de récéption de chèque ?</p>";
					btnDelete = "<button class='btn btn-primary' onclick='javascript:update_cheque_state("+$tmemberId+",\"0\",false)'>Valider</button>";
					btnAbort = "<button class='btn btn-default' data-dismiss='modal'>Annuler</button>";
					msgFooter = "<div style='display:flex; justify-content:center'><div class='btn-toolbar'>" + btnAbort + btnDelete + "</div></div>";
					$("#modal_confirm").modal({backdrop: true});
					$("#modal_confirm .modal-dialog").addClass("default");
					$("#modal_confirm .modal-header").html("Réception chèque");
					$("#modal_confirm .modal-body").html(msgBody);
					$("#modal_confirm .modal-footer").html(msgFooter);
					$("#modal_confirm").modal('show');
				}
				else {
					// Popup email confirmation de réception
					msgBody = "<p>Voulez-vous envoyer un email de bonne réception de chèque ?</p>";
					btnYes = "<button class='btn btn-primary' onclick='javascript:update_cheque_state("+$tmemberId+",\"1\",true)'>Oui</button>";
					btnNo = "<button class='btn btn-default' onclick='javascript:update_cheque_state("+$tmemberId+",\"1\",false)'>Non</button>";
					msgFooter = "<div style='display:flex; justify-content:center'><div class='btn-toolbar'>" + btnYes + btnNo + "</div></div>";
					$("#modal_confirm").modal({backdrop: true});
					$("#modal_confirm .modal-dialog").addClass("default");
					$("#modal_confirm .modal-header").html("Réception chèque");
					$("#modal_confirm .modal-body").html(msgBody);
					$("#modal_confirm .modal-footer").html(msgFooter);
					$("#modal_confirm").modal('show');
				}
			});
		});
		
		
		
		
		// ****** MODALS ********
		$("[id$='Modal'").on("show.bs.modal", function(e) {
			var link = $(e.relatedTarget);
			$(this).find(".modal-body").load(link.attr("href"));
		});		
		
		
		/******** CHECKBOXES et Shift+click **********/
		$shift = false;
		$lastpos = -1;
		// On réinitialise lastpos si on fait un tri
		$("th").click(function() {
			$lastpos = -1;
		});
		$tableId = "null"
		
		// CHANGE event
		$(".selector :checkbox").change(function() {
		
			// On récupère l'id de la liste concernée
			$newId = $(this).parents().parents().parents().parents().attr("id");
			if ($newId != $tableId || $tableId == "null") {
				$lastpos = -1;
				$tableId = $newId;
			}
		
			// On select la tr
			$tr = $(this).closest("tr");
			if ($tr.find(".selector input:checkbox").prop("checked")) {
				$tr.addClass("selected");
				$tr.children(".selector").children("span").html("1");
			}
			else {
				$tr.removeClass("selected");
				$tr.children(".selector").children("span").html("0");
			}
		
			// On récupère la pos du check cliqué
			$td = $(this).parents().parents();
			$pos = $("#"+$tableId+" tr").index($td)-1;

			// On gère le shift+click
			if ($shift && $lastpos > 0) {
				$min = Math.min($lastpos, $pos);
				$max = Math.max($lastpos, $pos);
				
				for (i=$min-1; i<= $max-1; i++) {
					// On coche la checkbox
					$("#"+$tableId+" tbody").children(":eq("+i+")").children().children("input:first-of-type").prop("checked",true);
					// On colore la selection
					$("#"+$tableId+" tbody").children(":eq("+i+")").addClass("selected");
					// On permet le tri
					$("#"+$tableId+" tbody").children(":eq("+i+")").children(".selector").children("span").html("1");
				}
			}
			
			// On actualise la pos du dernier check cliqué
			$lastpos = $pos;
			// On update le sort
			if ($tableId == "jam") $table1.trigger("updateCache");
			//else $table2.trigger("updateCache");
			
			// On actualise la liste des emails
			update_emails();

		});
		
		
		
		/******** KEYBOARD **********/
		$("body").on("keydown", function(event) {

			/*console.log($(".selected").length+"   "+$("body #mail_block :focus").length);
			console.log(event.which);*/
	
			// Pas d'action si pas de selectionné ou focus dans une input classique
			if ( $("#manage_content .selected").length == 0 || $("body #mail_block :focus").length > 0) return;
			
			// Echap => on déselectionne tout
			if (event.which == 27) {
				// On déselectionne tout le monde
				$("#manage_content .selector").each(function(index) {
					// On coche la checkbox
					$(this).children().prop("checked", 0);
					// On décolore la tr
					$(this).parent().removeClass("selected");
					$(this).children("span").html("0");
				});
				
				// On uncheck les selectall checkbox
				$("[id^='select_all']").prop("checked",0);
				
				// On update les emails
				update_emails();
			}
			
			// Suppr => permet de désinscrire un membre non stagiaire
			if (event.which == 46) {
				// Par sécurité, on ne supprime qu'une inscription à la fois
				if ($("#jam_list .selected").length != 1) return;
				
				// On récupère la tr du membre selectionné
				$tr = $("#jam_list .selected");
				
				// On récupère l'index de la colonne pseudo et le pseudo
				$index = 0;
				$tr.closest("table").children("thead").children("tr").children().each(function($i) {
					if ($(this).text() == "Pseudo") $index = $i+1;
				});
				$pseudo = $tr.children(":nth-child("+$index+")").html();
				
				// Modal
				btnDelete = "<button class='btn btn-primary' onclick='javascript:delete_inscr()'>Supprimer</button>";
				btnAbort = "<button class='btn btn-default' data-dismiss='modal'>Annuler</button>";
				msgBody = "<p>Etes-vous sûr de voulour supprimer l'inscription de '"+$pseudo+"' ?</p>";
				msgFooter = "<div style='display:flex; justify-content:center'><div class='btn-toolbar'>" + btnAbort + btnDelete + "</div></div>";
				$("#modal_confirm").modal({backdrop: true});
				$("#modal_confirm .modal-dialog").addClass("default");
				$("#modal_confirm .modal-header").html("Supprimer l'inscription");
				$("#modal_confirm .modal-body").html(msgBody);
				$("#modal_confirm .modal-footer").html(msgFooter);
				$("#modal_confirm").modal('show');
			}

		});
		
		// On enregistre le shift
		$("body").on("click",function(e) {
			$shift = e.shiftKey
		});
		

	});

	
	/**********************************************************************/
	/**********************************************************************/
	
	
	function update_cheque_state($tmemberId, $state, $send_email) {
	
		document.body.style.cursor = 'wait';
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/update_cheque_state",
			{	
				'tmemberId': $tmemberId,
				'state':$state,
				'send_email':$send_email
			},
		
			function (return_data) {
				
				// On masque la modal précédente
				$("#modal_confirm").modal('hide');
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';

				// Success // On affiche le message d'info et on actualise la cellule
				if ($obj['state']) {
					// Modal
					$("#modal_msg").modal({backdrop: true});
					$("#modal_msg .modal-dialog").addClass("default");
					$("#modal_msg .modal-header").html("Réception chèque");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg").modal('show');
					
					
					$("#modal_msg").on('hidden.bs.modal', function () {
						// On actualise l'état de la chequeCell
						$td = $("#stage_content tr[tmemberId="+$tmemberId+"]").children(".chequeCell");
						if ($state == 1) $app = '<span style="display:none">1</span><img style="height: 14px" src="<?php echo base_url() ?>images/icons/ok.png">';
						else $app = '<span style="display:none">0</span><img style="height: 12px" src="<?php echo base_url() ?>images/icons/x.png">';
						$td.empty();
						$td.append($app);
						
						// On actualise l'état de la table
						$table2.trigger("updateCache");
					})
				}
				
				// Erreur
				else {
					console.log("erreur");
				}
			}
		);
	}
	
	

	
	function select_all($list_id) {
		// On selectionne tout le monde
		$("#" + $list_id + " .selector").each(function(index) {
			// On coche la checkbox
			$(this).children().prop("checked", $("#select_all_" + $list_id).prop("checked"));
			// On colore la tr
			if ($("#select_all_" + $list_id).prop("checked")) {
				$(this).parent().addClass("selected");
				$(this).children("span:first-of-type").html("1");
			}
			else {
				$(this).parent().removeClass("selected");
				$(this).children("span").html("0");
			}
		});
		
		// On update les emails
		update_emails();		
	}

	
	
	/********************* MAIL *********************/
	
	function update_emails() {
		// On actualise la liste des emails
		$("#email_block").empty();
		mailList = "";
		$(".selector").children(":checked").each(function() {
			// On gère plusieurs email possibles
			$(this).parents().children(".email_used").each(function() {
				if ($(this).html() != "") mailList += "<span class='label label-success'>"+$(this).html()+"</span> ";
				if ($(this).is("[email_tut]") && $(this).attr("email_tut") != "") mailList += "<span class='label label-success'>"+$(this).attr("email_tut")+"</span> ";
			});
		});
		$("#email_block").append(mailList);
	}
	
	
	/******** SEND_MAIL **********/
	function send_email() {
		
		// Pas d'envoie si le message est vide
		$message = CKEDITOR.instances.editor1.getData();
		if ($message == "") {
			$("#modal_msg .modal-dialog").removeClass("success");
			$("#modal_msg .modal-dialog").addClass("error");
			$("#modal_msg .modal-header").html("Erreur !");
			$("#modal_msg .modal-body").html("Veuillez saisir un message à envoyer.");
			$("#modal_msg .modal-footer").html('<button type="button" class="btn" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
			$("#modal_msg").modal('show');
			return;
		}

		$adresses = [];
		// On parcourt chaque membres checkés
		$("tbody .selector :checked").each(function(index) {
			// On récupère les tr correspondantes
			$tr = $(this).parent().parent();
			$tr.children(".email_used").each(function() {
				// On rempli le tableau des adresses présentes dans la tr
				if ($(this).text() != "") $adresses[$adresses.length] = $(this).text();
			});
		});
		
		// Pas d'envoie si aucun membre n'a été selectionné
		if ($adresses.length == 0) {
			$("#modal_msg .modal-dialog").removeClass("success");
			$("#modal_msg .modal-dialog").addClass("error");
			$("#modal_msg .modal-header").html("Erreur !");
			$("#modal_msg .modal-body").html("Veuillez sélectionner au moins un destinataire.");
			$("#modal_msg .modal-footer").html('<button type="button" class="btn" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
			$("#modal_msg").modal('show');
			return;
		}
		
		// Par défaut on rajoute l'adresse de l'admin utilisateur car pas d'archivage d'email sur le site
		$adresses[$adresses.length] = "<?php echo $member->email; ?>";
		
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/send_email",
		
			// On récupère les données nécessaires
			{'subject':$("#email_subject").val(),
			'message':$message,
			'name':'<?php echo $member->pseudo; ?>',
			'from':'manage@le-gro.com',
			'reply_to':'<?php echo $member->email; ?>',
			'adresses': JSON.stringify($adresses)
			},
			
			// On traite la réponse du serveur			
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Email bien envoyé
				if ($obj['state'] == 1) {
					$("#modal_msg .modal-dialog").removeClass("error");
					$("#modal_msg .modal-dialog").addClass("success");
					$("#modal_msg .modal-header").html("Email envoyé.");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<button type="button" class="btn" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
					$("#modal_msg").modal('show').on('hidden.bs.modal', function () {
						$(this).unbind();
						init_page();
					});
				}
				else {
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html("Votre message n'a pas pu être correctement envoyé : "+$obj['data']);
					$("#modal_msg .modal-footer").html('<button type="button" class="btn" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
					$("#modal_msg").modal('show');
				}
			}
		);
	}
	
	
	/******** On supprime l'inscription du membre sélectionné **********/
	function delete_inscr() {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Par sécurité, on ne supprime qu'une inscription à la fois et pas les stagaires
		if ($("#jam_list .selected").length > 1) return;
		
		// On récupère le pseudo
		$tr = $("#jam_list .selected");
		// On récupère l'index de la colonne pseudo et le pseudo
		$index = 0;
		$tr.closest("table").children("thead").children("tr").children().each(function($i) {
			if ($(this).text() == "Pseudo") $index = $i+1;
		});
		$pseudo = $tr.children(":nth-child("+$index+")").text();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/delete_inscr",
		
			// On récupère les données nécessaires
			{
			'pseudo':$pseudo,
			'jamId':<?php echo $jam_item['id']; ?>
			},
			
			// On traite la réponse du serveur			
			function (return_data) {
				
				// On change le curseur et on masque la modal
				document.body.style.cursor = 'default';

				
				// Inscription bien supprimée
				if (return_data == "success") {
					
					$("#modal_msg .modal-header").empty();
					$("#modal_msg .modal-footer").empty();
					
					$("#modal_confirm").modal('hide').on('hidden.bs.modal', function () {
						$(this).unbind();
						
						// On remove la tr et on update le cache du tablesorter
						$tableId = $tr.parent().parent().prop("id");
						$tr.remove();
						if ($tableId == "jam") $table1.trigger("updateCache");
						//else $table2.trigger("updateCache");
						
						// Modal de confirmation de suppression
						msg = "L'inscription de <b>"+$pseudo+"<b> a été supprimée.";
						$("#modal_msg .modal-dialog").removeClass("error");
						$("#modal_msg .modal-dialog").addClass("success");
						$("#modal_msg .modal-header").html("Membre supprimé.");
						$("#modal_msg .modal-body").html(msg);
						$("#modal_msg .modal-footer").html('<button type="button" class="btn" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
						$("#modal_msg").modal('show').on('hidden.bs.modal', function () {
							$(this).unbind();
							init_page();
							$("#modal_msg .modal-dialog").removeClass("success");
							$("#modal_msg .modal-dialog").addClass("default");
						});
					});
				}
				
				// Erreur
				else {
					$("#modal_confirm").modal('hide').on('hidden.bs.modal', function () {
						$(this).unbind();
						
						// Modal d'erreur
						msg = "L'inscription n'a pas pu être supprimée : "+return_data;
						$("#modal_msg .modal-dialog").removeClass("success");
						$("#modal_msg .modal-dialog").addClass("error");
						$("#modal_msg .modal-header").html("Erreur !");
						$("#modal_msg .modal-body").html(msg);
						$("#modal_msg .modal-footer").html('<button type="button" class="btn" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
						$("#modal_msg").modal('show').on('hidden.bs.modal', function () {
							$(this).unbind();
							$("#modal_msg .modal-dialog").removeClass("error");
							$("#modal_msg .modal-dialog").addClass("default");
						});
					});
				}

			}
		);
		
	}
	
	
	/******** Décoche les checkbox et vide le ckeditor **********/
	function init_page() {
		// On déselectionne tout le monde
		$(".selector").each(function(index) {
			// On coche la checkbox
			$(this).children().prop("checked", false);
			// On colore la tr
			$(this).parent().removeClass("selected");
			$(this).children("span").html("0");
		});
		
		// On remet à zéro le ckeditor
		$("#email_block").empty();
		$("#email_subject").val("");
		CKEDITOR.instances.editor1.setData("");
		
		// On actualise le nombre de jammeurs
		$("#nbJammeur small").empty().append("("+$("#jam_list tbody").children().length+")")
	}

</script>


<!-- CONTAINER GLOBAL !-->
<div class="row">
	
	
	<!-- PANEL !-->
	<div class="panel-default panel">

	
		<!-- Header !-->
		<div class="row panel-heading panel-bright title_box">
			<h4><a href="<?php echo site_url().'/jam/'.$jam_item['slug']; ?>"><?php echo $jam_item['title']; ?></a> <small>:</small> tableau des participants</h4>
		</div>


		<!-- Options !-->
		<div class="row">
		<div class="panel-body col-lg-12">
			
		
			<!-- AFFICHAGE !-->
			<div class="row">

				<!-- Colomn selector !-->
				<button id="popover" type="button" class="btn btn-default">
					Affichage
				</button>
				<div class="hidden">
					<div id="popover-target"></div>
				</div>
				
				
				<!-- BOUTTONS SUPER ADMIN   -->
				<?php if($this->session->userdata('admin') == "1") : ?>
				<div class="btn-group">
					<!-- AJOUTER UN MEMBRE -->
					<a class="btn btn-default" href='<?php echo site_url();?>/jam/add_member/<?php echo $jam_item['id']; ?>'" data-remote="false" data-toggle="modal" data-target="#addModal"><img style="height: 16px; vertical-align:text-top;" src="/images/icons/add_member.png" alt="Ajouter un membre"><span class="hidden-xs">&nbsp;&nbsp;Ajouter un membre</span></a>
				</div>	
				<?php endif ?>
				
			</div>
		
		</div>  <!-- PANEL BODY !-->
		</div>
		
	
		<!-- **************** TABLEAU PARTICIPANTS *************** -->	
		<div id="manage_content" class="row">
		<div class="col-lg-12" style="overflow:auto">
		
		<!-- Affichage des membres inscrits -->
		<?php if (sizeof($list_members) > 0) :?>
			<div class="small_block_list_title soften">Liste des jammeurs <span id="nbJammeur" class="soften"><small>(<?php echo sizeof($list_members); ?>)</small></span></div>
			<div id="jam_list">
				<table id="tab" class="tablesorter focus-highlight" cellspacing="0">
					<thead>
						 <tr>
							<th data-priority="critical" class="centerTD">&nbsp;</th>
							<th data-priority="6" class="centerTD" style="width:15px">Admin</th>
							<th data-priority="critical">Pseudo</th>
							<th data-priority="6">Prénom</th>
							<th data-priority="6">Nom</th>
							<th data-priority="critical" class="centerTD">Cat1</th>
							<th data-priority="critical" class="centerTD">Instru1</th>
							<th data-priority="4"  class="centerTD">Instru2</th>
							<th data-priority="3">Email</th>
							<th data-priority="critical" class="centerTD">Mobile</th>
							<th data-priority="6" class="sorter-shortDate dateFormat-ddmmyyyy centerTD">Inscr</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><input id="select_all_jam_list" type="checkbox" onclick="select_all('jam_list')" /></th>
							<th>Admin</th>
							<th>Pseudo</th>
							<th>Prénom</th>
							<th>Nom</th>
							<th>Cat1</th>
							<th>Instru1</th>
							<th>Instru2</th>
							<th>Email</th>
							<th>Mobile</th>
							<th>Inscr</th>
						</tr>
					</tfoot>
					<tbody>
						<?php 
							foreach ($list_members as $tmember) {
								echo '<tr tmemberId="'.$tmember->memberId.'">';
								
									echo '<td class="selector"><span style="display:none">0</span><input type="checkbox" /></td>';
									echo '<td>'.($tmember->admin > 0 ? "<span style='display:none'>1</span><img style='height: 14px' src='/images/icons/gear.png'>" : "<span style='display:none'>0</span>").'</td>';
									echo '<td><b>'.$tmember->pseudo.'</b></td>';
									echo '<td><b>'.$tmember->prenom.'</b></td>';
									echo '<td><b>'.$tmember->nom.'</b></td>';
									// Catégorie
									echo '<td>';
										echo '<span style="display:none">'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['view_order'].'</span>';
										echo '<img style="height: 16px;" src="'.base_url().'images/icons/'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['iconURL'].'" title="'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['name'].'">';
									echo '</td>';
									echo '<td>'.$instru_list[$tmember->idInstru1 - 1]['name'].'</td>';
									echo '<td>'.$instru_list[$tmember->idInstru2 - 1]['name'].'</td>';
									echo '<td class="email_used">'.$tmember->email.'</td>';
									echo '<td style="text-align: center">';
										if ($tmember->mobile) echo substr($tmember->mobile,0,2).' '.substr($tmember->mobile,2,2).' '.substr($tmember->mobile,4,2).' '.substr($tmember->mobile,6,2).' '.substr($tmember->mobile,8,2);
									echo '</td>';
									echo '<td style="text-align:right">'.$tmember->date_inscr.'</td>';
								echo "</tr>\n";
							}
						?>
					</tbody>
				</table>
			</div>
		<?php else : ?>
			<div class="small_block_list_title soften">Il n'y a actuellement aucun participant inscrit à cette jam.</div>
		<?php endif ?>

				
		</div>
		</div>
		
		
		<!-- **************** TABLEAU STAGIAIRES *************** -->	
		<div id="stage_content" class="row">
		<div class="col-lg-12" style="overflow:auto">
		
		<!-- Affichage des membres inscrits -->
		<?php if (sizeof($list_stage_members) > 0) :?>
			<br>
			<div class="small_block_list_title soften">Liste des stagiaires <span id="nbStagiaire" class="soften"><small>(<?php echo sizeof($list_stage_members); ?>)</small></span></div>
			<div id="stage_list">
				<table id="tab2" class="tablesorter focus-highlight" cellspacing="0">
					<thead>
						 <tr>
							<th data-priority="critical" class="centerTD">&nbsp;</th>
							<th data-priority="critical">Pseudo</th>
							<th data-priority="6">Prénom</th>
							<th data-priority="critical" class="centerTD">Age</th>
							<th data-priority="critical" class="centerTD">Cat1</th>
							<th data-priority="critical" class="centerTD">Instru1</th>
							<th data-priority="4"  class="centerTD">Instru2</th>
							<th data-priority="3">Email</th>
							<th data-priority="critical" class="centerTD">Mobile</th>
							<th data-priority="6" class="sorter-shortDate dateFormat-ddmmyyyy centerTD">Inscr</th>
							<th data-priority="6" class="sorter-shortDate dateFormat-ddmmyyyy centerTD">Relance</th>
							<th data-priority="6" class="centerTD"><img style="height: 16px;" src="<?php echo base_url().'images/icons/cheque.png' ?>"></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><input id="select_all_stage_list" type="checkbox" onclick="select_all('stage_list')" /></th>
							<th>Pseudo</th>
							<th>Prénom</th>
							<th>Age</th>
							<th>Cat1</th>
							<th>Instru1</th>
							<th>Instru2</th>
							<th>Email</th>
							<th>Mobile</th>
							<th>Inscr</th>
							<th>Relance</th>
							<th><img style="height: 16px;" src="<?php echo base_url().'images/icons/cheque.png' ?>"></th>
						</tr>
					</tfoot>
					<tbody>
						<?php 
							foreach ($list_stage_members as $tmember) {
								echo '<tr tmemberId="'.$tmember->id.'">';
								
									echo '<td class="selector"><span style="display:none">0</span><input type="checkbox" /></td>';
									echo '<td><a href="#" class="toggle"><b>'.$tmember->pseudo.'</b></a></td>';
									echo '<td><b>'.$tmember->prenom.'</b></td>';
									echo '<td>'.$tmember->age.'</td>';
									// Catégorie
									echo '<td>';
										echo '<span style="display:none">'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['view_order'].'</span>';
										echo '<img style="height: 16px;" src="'.base_url().'images/icons/'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['iconURL'].'" title="'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['name'].'">';
									echo '</td>';
									echo '<td>'.$instru_list[$tmember->idInstru1 - 1]['name'].'</td>';
									echo '<td>'.$instru_list[$tmember->idInstru2 - 1]['name'].'</td>';
									echo '<td class="email_used" email_tut="'.$tmember->email_tuteur.'">'.$tmember->email.'</td>';
									echo '<td style="text-align: center">';
										if ($tmember->mobile) echo substr($tmember->mobile,0,2).' '.substr($tmember->mobile,2,2).' '.substr($tmember->mobile,4,2).' '.substr($tmember->mobile,6,2).' '.substr($tmember->mobile,8,2);
									echo '</td>';
									echo '<td style="text-align:right">'.$tmember->date_inscr.'</td>';
									echo '<td style="text-align:right">'.$tmember->date_relance.'</td>';
									// Chèque
									echo '<td class="chequeCell unselectable"><span style="display:none">'.$tmember->cheque.'</span>';
										if ($tmember->cheque) echo '<img style="height: 14px;" src="'.base_url().'images/icons/ok.png">';
										else echo '</span><img style="height: 12px;" src="'.base_url().'images/icons/x.png">';
									echo '</td>';
								echo "</tr>";
								echo '<tr class="tablesorter-childRow">';
									echo '<td></td>';
									echo '<td colspan="4">|| <b>'.$tmember->prenom.' '.$tmember->nom.'</b><br>';
									echo '|| Professeur : '.$tmember->prof.'<br>';
									echo '|| Ecole : '.$tmember->ecole.'<br>';
									echo '</td>';
									echo '<td colspan="3">|| <b>Tuteur</b><br>';
									echo '|| Tel : '.substr($tmember->tel_tuteur,0,2).' '.substr($tmember->tel_tuteur,2,2).' '.substr($tmember->tel_tuteur,4,2).' '.substr($tmember->tel_tuteur,6,2).' '.substr($tmember->tel_tuteur,8,2).'<br>';								
									echo '|| Email : <span class="email_used">'.$tmember->email_tuteur.'</span><br>';
									echo '</td>';
									echo '<td colspan="4">|| <b>Remarque</b><br>';
										echo $tmember->remarque.'<br>';
									echo '</td>';
								echo "</tr>\n";
							}
						?>
					</tbody>
				</table>
			</div>
		<?php else : ?>
			<div class="small_block_list_title soften">Il n'y a actuellement aucun stagiaires inscrit à cette jam.</div>
		<?php endif ?>

				
		</div>
		</div>
		
		
	</div>	
	
	<!-- ******* MAIL BLOCK ******** !-->
	<div id="mail_block" class="row">
	
		<!-- MAIL TAB PANEL !-->
		<div class="panel panel-default">
			
			<!-- Header !-->
			<div class="row panel-heading panel-bright title_box">
				<h4>Mailing</h4>
			</div>

	
			<!-- **************** MAIL INFOS *************** -->
			<div class="row">
			<div class="panel-body col-lg-12">
			
				<form action="javascript:send_email()">
				
					<div class="form-group">
						<label for="receiver">Destinataires</label>
						<div id="email_block"></div>
					</div>
					
					<div class="form-group">
						<label for="email_subject">Sujet</label>
						<input id="email_subject" class="form-control" type="text" name="email_subject" value="" required />
					</div>
					
					<div class="form-group">
						<textarea name="editor1" id="editor1" rows="10" cols="80">
						</textarea>
						<script>
							// Replace the <textarea id="editor1"> with a CKEditor
							// instance, using default configuration.
							CKEDITOR.replace( 'editor1' );
						</script>
					</div>
					
					<input class="btn btn-default pull-right" type="submit" name="submit" value="Envoyer mail" />
					
				</form>
				
			</div>
			</div>
			
		</div> <!-- RECAP TAB PANEL !-->
		
	</div> <!-- ROW 2 !-->
	
</div>  <!-- GLOBAL CONTENT !-->
	
	
<!-- ******** MODAL ******* !-->
<div id="modal_msg" class="modal fade" role="dialog">   <!-- nom pourri car comportement commun pour les id$=Modal !-->
	<div class="modal-dialog default">
		<div class="modal-content">
			<div class="modal-header lead"></div>
			<div class="modal-body"></div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>

<div id="modal_confirm" class="modal fade" role="dialog">   <!-- nom pourri car comportement commun pour les id$=Modal !-->
	<div class="modal-dialog default">
		<div class="modal-content">
			<div class="modal-header lead"></div>
			<div class="modal-body"></div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>


<div id="addModal" class="modal fade" role="dialog">
	<div class="modal-dialog default">
		<div class="modal-content">
			<div class="modal-header lead">Ajouter un membre</div>
			<div class="modal-body"></div>
		</div>
	</div>
</div>