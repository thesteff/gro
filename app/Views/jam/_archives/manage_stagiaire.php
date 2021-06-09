<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/jquery.tablesorter.js"></script>

<!-- Editeur html -->
<script src="<?php echo base_url();?>ressources/ckeditor/ckeditor.js"></script>

<script type="text/javascript">

	/* ******************* Gestion des tableaux ****************/
	$(function() {
	
		/********* TABLE 1 **********/
		$table1 = $( '#jam' ).tablesorter({
			theme : 'sand',
			widgets : [ "zebra" ],
			widgetOptions: {
				// Set this option to false to make the searches case sensitive
				filter_ignoreCase : true
			}
		});

		
		/********* TABLE 2 **********/
		$table2 = $( '#stage' ).tablesorter({
			theme : 'sand',
			cssChildRow: "tablesorter-childRow",
			dateFormat : "ddmmyy",
			widgets : [ "zebra" ],
			widgetOptions: {
				// Set this option to false to make the searches case sensitive
				filter_ignoreCase : true
			}
		});
		
		// Gère le childrow des stagiaires
		$table2.delegate('.toggle', 'click' ,function(){
			$(this).closest('tr').nextUntil('tr:not(.tablesorter-childRow)').find('td').toggle();
			return false;
		});
		
		
		// hide child rows
		$('.tablesorter-childRow td').hide();
		
		
		// On stylise les colonnes
		//update_style();
		
		
		// On gère le DOUBLE CLICK (changement d'état cheque reçu)
		$("td[name='chequeCell']").each(function() {
			$(this).dblclick(function() {
				// On récupère l'id du stage_membres_relation
				$tmemberId = $(this).parent().attr("tmemberId");
				// Si déjà coché
				if ($(this).children("span").html() == "1") {
					//$(this).empty();
					// Popup confirm
					$confirm = "<p>Etes-vous sûr de vouloir changer l'état de récéption de chèque ?</p>";
					$confirm += "<p style='text-align:center'><input type='button' value='valider' onclick='javascript:update_cheque_state("+$tmemberId+",\"0\",false)'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()'></p>";
					TINY.box.show({html:$confirm,boxid:'error',animate:false,width:650});
				}
				else {
					// Popup email confirmation de réception
					$confirm = "<p>Voulez-vous envoyer un email de bonne réception de chèque ?</p>";
					$confirm += "<p style='text-align:center'><input type='button' value='oui' onclick='javascript:update_cheque_state("+$tmemberId+",\"1\",true);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='non' onclick='javascript:update_cheque_state("+$tmemberId+",\"1\",false)'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()'></p>";
					TINY.box.show({html:$confirm,boxid:'confirm',animate:true,width:650});

					//update_cheque_state($tmemberId, 1);
				}
			});
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
			else $table2.trigger("updateCache");
			
			// On actualise la liste des emails
			update_emails();

		});
		
		
		
		/******** KEYBOARD **********/
		$("body").on("keydown", function(event) {

			//console.log($(".selected").length+"   "+$("body #mail_block :focus").length);
	
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
				
				$confirm = "<p>Etes-vous sûr de voulour supprimer l'inscription de '"+$pseudo+"' ?</p>";
				$confirm += "<p style='text-align:center'><input type='button' value='supprimer' onclick='javascript:delete_inscr()'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()'></p>";
				TINY.box.show({html:$confirm,boxid:'confirm',animate:false,width:650});
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
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/update_cheque_state",
			{	
				'tmemberId': $tmemberId,
				'state':$state,
				'send_email':$send_email
			},
		
			function (msg) {
				// On affiche le message d'info et on actualise la cellule
				TINY.box.show({html:msg,boxid:'success',animate:false,width:650, closejs:function(){
									// Reception de cheque
									$td = $("#stage tr[tmemberId="+$tmemberId+"] ").children("[name='chequeCell']");
									if ($state == 1) $app = '<span style="display:none">1</span><img style="height: 12;" src="/images/icons/ok.png">';
									else $app = '<span style="display:none">0</span>';
									$td.empty();
									$td.append($app);
									
									// On actualise l'état de la table
									$table2.trigger("updateCache");
								}});
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

	
	
	
	function update_emails() {
	// On actualise la liste des emails
		$(".email_block").empty();
		$(".selector").children(":checked").each(function() {
			// On gère plusieurs email possibles
			$(this).parents().children(".email_used").each(function() {
				if ($(this).html() != "") {
					$(".email_block").append("<");
					$(".email_block").append($(this).html());
					$(".email_block").append("> ");
				}
			});
		});
	}
	
	
	/******** SEND_MAIL **********/
	function send_email() {
		
		// Pas d'envoie si le message est vide
		$message = CKEDITOR.instances.editor1.getData();
		if ($message == "") {
			TINY.box.show({html:"Veuillez saisir un message à envoyer !",boxid:'error',animate:false,width:650});
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
			TINY.box.show({html:"Veuillez sélectionner un destinataire !",boxid:'error',animate:false,width:650});
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
			'email_sender':'<?php echo $member->email; ?>',
			'adresses': JSON.stringify($adresses)
			},
			
			// On traite la réponse du serveur			
			function (msg) {
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if (msg != "success") TINY.box.show({html:"Votre message n'a pas pu être correctement envoyé : "+msg,boxid:'error',animate:false,width:650});
				else {
					// Email bien envoyé
					TINY.box.show({html:"Votre message a bien été envoyé.",boxid:'success',animate:false,width:650, closejs:function(){init_page();}});
				}
			}
		);
	}
	
	
	/******** On supprime l'inscription du membre sélectionné **********/
	function delete_inscr() {
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
			{'pseudo':$pseudo,
			'jamId':<?php echo $jam_item['id']; ?>
			},
			
			// On traite la réponse du serveur			
			function (msg) {
				
				if (msg != "success") TINY.box.show({html:"L'inscription n'a pas pu être supprimée : "+msg,boxid:'error',animate:false,width:650});
				else {
					// Inscription bien supprimée
					$tableId = $tr.parent().parent().prop("id");
					$tr.remove();
					if ($tableId == "jam") $table1.trigger("updateCache");
					else $table2.trigger("updateCache");
					
					TINY.box.show({html:"L'inscription de "+$pseudo+" a été supprimée.",boxid:'success',animate:false,width:650, closejs:function(){init_page();}});
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
		$(".email_block").empty();
		$("#email_subject").val("");
		CKEDITOR.instances.editor1.setData("");
	}

</script>


<div class="panel panel-default row">
	
	<!-- Header !-->
	<div class="row">
		<h4 class="panel-heading"><?php echo $page_title; ?></h4>
	</div>

	
	<div class="row">
	<div class="col-lg-12">
		
		<!-- Affichage des membres inscrits -->
		<?php if (sizeof($list_members) > 0) :?>
			<div class="small_block_list_title soften">Liste des jammeurs <span class="soften"><small>(<?php echo sizeof($list_members); ?>)</small></span></div>
			<div id="jam_list">
				<table id="jam" class="tablesorter focus-highlight" cellspacing="0">
					<thead>
						 <tr>
							<th class="centerTD">&nbsp;</th>
							<th class="centerTD">Cat1</th>
							<th>Pseudo</th>
							<th>Prénom</th>
							<th>Nom</th>
							<th class="centerTD">Admin</th>
							<th>Instru1</th>
							<th>Instru2</th>
							<th>Email</th>
							<th class="centerTD">Mobile</th>
							<th class="centerTD" width="10px"><span class="buffet"><img style='height: 16px;' src='/images/icons/fork.png' title='Buffet'></span></th></th>
							<th class="centerTD" width="10px"><span class="billet"><img style='height: 16px;' src='/images/icons/ticket.png' title='Billeterie'></span></th></th>
							<th class="centerTD" width="10px"><span class="balance"><img style='height: 16px;' src='/images/icons/mix.png' title='Buffet'></span></th></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy">Inscr</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><input id="select_all_jam_list" type="checkbox" onclick="select_all('jam_list')" /></th>
							<th>Cat1</th>
							<th>Pseudo</th>
							<th>Prénom</th>
							<th>Nom</th>
							<th>Admin</th>
							<th>Instru1</th>
							<th>Instru2</th>
							<th>Email</th>
							<th>Mobile</th>
							<th width="10px"><span class="buffet"><img style='height: 16px;' src='/images/icons/fork.png' title='Buffet'></span></th></th>
							<th width="10px"><span class="billet"><img style='height: 16px;' src='/images/icons/ticket.png' title='Billeterie'></span></th></th>
							<th width="10px"><span class="balance"><img style='height: 16px;' src='/images/icons/mix.png' title='Balance'></span></th></th>
							<th>Inscr</th>
						</tr>
					</tfoot>
					<tbody>
						<?php 
							foreach ($list_members as $tmember) {
								echo '<tr tmemberId="'.$tmember->memberId.'">';
								
									echo '<td class="selector"><span style="display:none">0</span><input type="checkbox" /></td>';
									// Catégorie
									echo '<td>';
										echo '<span style="display:none">'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['view_order'].'</span>';
										echo '<img style="height: 16px;" src="'.base_url().'images/icons/'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['iconURL'].'" title="'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['name'].'">';
									echo '</td>';
									echo '<td><b>'.$tmember->pseudo.'</b></td>';
									echo '<td><b>'.$tmember->prenom.'</b></td>';
									echo '<td><b>'.$tmember->nom.'</b></td>';
									echo '<td>'.$tmember->admin.'</td>';
									echo '<td>'.$instru_list[$tmember->idInstru1 - 1]['name'].'</td>';
									echo '<td>'.$instru_list[$tmember->idInstru2 - 1]['name'].'</td>';
									echo '<td class="email_used">'.$tmember->email.'</td>';
									echo '<td style="text-align: center">';
										if ($tmember->mobile) echo substr($tmember->mobile,0,2).'.'.substr($tmember->mobile,2,2).'.'.substr($tmember->mobile,4,2).'.'.substr($tmember->mobile,6,2).'.'.substr($tmember->mobile,8,2);
									echo '</td>';
									echo '<td>';
									if ($tmember->buffet == 1) echo "<span style='display:none'>1</span><img style='height: 12px;' src='/images/icons/ok.png'>";
									else echo '<span style="display:none">0</span>';
									echo '</td>';
									echo '<td>';
									if ($tmember->billet == 1) echo "<span style='display:none'>1</span><img style='height: 12px;' src='/images/icons/ok.png'>";
									else echo '<span style="display:none">0</span>';
									echo '</td>';
									echo '<td>';
									if ($tmember->balance == 1) echo "<span style='display:none'>1</span><img style='height: 12px;' src='/images/icons/ok.png'>";
									else echo '<span style="display:none">0</span>';
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

		<?php if (sizeof($list_stage_members) > 0) :?>
			<br>
			<!-- Affichage des stagiaires préinscrits -->
			<div class="small_block_list_title soften">Liste des stagiaires <span class="soften"><small>(<?php echo sizeof($list_stage_members); ?>)</small></span></div>
			<div id="stage_list">
				<table id="stage" class="tablesorter" cellspacing="0">
					<thead>
						 <tr>
							<th class="centerTD">&nbsp;</th>
							<th>Pseudo</th>
							<th>Age</th>
							<th>Cat1</th>
							<th>Instru1</th>
							<th>Instru2</th>
							<th>Email</th>
							<th>Prat.</th>
							<th>Grp.</th>
							<th style="display:none">Email Tut.</th>
							<th>Tel Tut.</th>
							<th class="centerTD" width="10"><span class="cheque"><img style='height: 16;' src='/images/icons/cheque.png' title='Chèque'></span></th></th>
							<th class="sorter-shortDate dateFormat-ddmmyyyy">Inscr</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th class="centerTD"><input id="select_all_stage_list" type="checkbox" onclick="select_all('stage_list')" /></th>
							<th>Pseudo</th>
							<th class="centerTD">Age</th>
							<th>Cat1</th>
							<th>Instru1</th>
							<th>Instru2</th>
							<th>Email</th>
							<th class="centerTD">Prat.</th>
							<th class="centerTD">Grp.</th>
							<th style="display:none">Email Tut.</th>
							<th>Tel Tut.</th>
							<th width="10"><span class="cheque"><img style='height: 16;' src='/images/icons/cheque.png' title='Chèque'></span></th></th>
							<th>Inscr</th>
						</tr>
					</tfoot>
					<tbody>
						<?php
							foreach ($list_stage_members as $tmember) {
								echo '<tr tmemberId="'.$tmember->id.'">';
									echo '<td rowspan="2" class="selector"><span style="display:none">0</span><input type="checkbox" /></td>';
									echo '<td><a href="#" class="toggle">'.$tmember->pseudo.'</a></td>';
									echo '<td>'.$tmember->age.'</td>';
									echo '<td>'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['name'].'</td>';
									echo '<td>'.$instru_list[$tmember->idInstru1 - 1]['name'].'</td>';
									echo '<td>'.$instru_list[$tmember->idInstru2 - 1]['name'].'</td>';
									echo '<td class="email_used">'.$tmember->email.'</td>';
									echo '<td>'.$tmember->nb_prat.'</td>';
									echo '<td>'.$tmember->nb_grp.'</td>';
									echo '<td class="email_used" style="display:none">'.$tmember->email_tuteur.'</td>';
									echo '<td>'.$tmember->mobile.'</td>';
									echo '<td name="chequeCell">';
									if ($tmember->cheque == 1) echo "<span style='display:none'>1</span><img style='height: 12px;' src='/images/icons/ok.png'>";
									else echo '<span style="display:none">0</span>';
									echo '</td>';
									echo '<td style="text-align:right">'.$tmember->date_inscr.'</td>';

								echo '</tr>';
								echo '<tr class="tablesorter-childRow">';
									echo '<td colspan="4">|| <b>'.$tmember->prenom.' '.$tmember->nom.'</b><br>';
									echo '|| Professeur : '.$tmember->prof.'<br>';
									echo '|| Ecole : '.$tmember->ecole.'<br>';
									echo '</td>';
									echo '<td colspan="2">|| <b>Tuteur</b><br>';
									echo '|| Tel : '.$tmember->tel_tuteur.'<br>';
									echo '|| Email : '.$tmember->email_tuteur.'<br>';
									echo '</td>';
									echo '<td colspan="5">|| <b>Remarque</b><br>';
									echo $tmember->remarque.'<br>';
									echo '</td>';
								echo "</tr>\n";
							}
						?>
					</tbody>
				</table>
			</div>
		<?php elseif (!empty($stage_item)) : ?>
			<div class="small_block_list_title soften">Il n'y a actuellement aucun stagiaire inscrit au stage de cette jam.</div>
		<?php endif ?>
				
	</div>
	</div>
</div>	
	
	<div id="mail_block">
		<form action="javascript:send_email()">
			<div class="block_head">
				<h3 id="mailing_title" class="block_title">Mailing</h3>
				<hr>
			</div>
			<div style="margin:10 20">
				<label for="mail_objet">Emails&nbsp;</label>
				<div style="vertical-align: text-top;" class="email_block"></div>
			</div>
			<div style="margin:10 20">
				<label for="email_subject">Sujet&nbsp;</label>
				<input id="email_subject" type="text" name="email_subject" size="104" value="" required />
			</div>
			<div style="margin:10 20">
				<textarea name="editor1" id="editor1" rows="10" cols="80">
				</textarea>
				<script>
					// Replace the <textarea id="editor1"> with a CKEditor
					// instance, using default configuration.
					CKEDITOR.replace( 'editor1' );
				</script>
			</div>
			<input style="margin-right:80" class="right button" type="submit" name="submit" value="Envoyer mail" />
		</form>
	</div>
						
</div>
	
	
	
