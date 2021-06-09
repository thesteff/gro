<?php

	use CodeIgniter\I18n\Time;

	// On récupère les variables de sessions
	$session = \Config\Services::session();
?>


		
<script type="text/javascript">

	$(function() {
		
		<!-- Utilisateur connecté !-->
		<?php if ($session->logged) : ?>
		
			// On charge l'image de l'AVATAR
			$.ajax({
				url:'<?php echo base_url("images/avatar"); ?>'+'/'+$("#memberLogin").attr("idKey")+'.png',
				type:'HEAD',
				success: function() {
					$("#miniAvatar").prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+$("#memberLogin").attr("idKey")+'.png').removeClass("hidden");
					$("#memberLogin").css("padding-bottom","11px");
					$("#memberLogin").css("padding-top","12px");
				}
			});
			
			
			<!-- ***** DIALOG ***** !-->
			<?php 
				// On recompte les unread message toutes les 10 minutes
				if ($session->nbUnreadMessage != null) {
					$time = Time::parse($session->lastCheckUnreadMessage);
					$diff = $time->difference(Time::now());
				}
				$reCount = isset($diff) ? ($diff->minutes < -10) : false;
			?>
			<?php if ($session->nbUnreadMessage === null || $reCount) : ?>
				// On récupère le nombre de MESSAGE non lus
				$.post("<?php echo site_url('ajax_members/get_nb_unread_message'); ?>",
				
					// On récupère les données nécessaires
					{
						'memberId': $('#memberLogin').attr("idKey"),
						//'previousAccess': '<?php echo $session->previousAccess; ?>'
					},
					
					// On actualise le badge			
					function (nbCount) {
						if (nbCount > 0) {
							$(".navbar-right #dialog").css("padding-right","2px");
							$(".navbar-right #dialog .badge").html(nbCount).removeClass("hidden");
						}
					}
				);
			<?php else : ?>
				nbCount = <?php echo $session->nbUnreadMessage ?>;
				if (nbCount > 0) {
					// On actualise le nbCount en fonction de la variable de session
					$(".navbar-right #dialog").css("padding-right","2px");
					$(".navbar-right #dialog .badge").html(nbCount).removeClass("hidden");
				}
				
			<?php endif; ?>
			
			
			<!-- *********   NOTIFICATION   ********** !-->
			<?php if ($session->list_notif != null && sizeof($session->list_notif) > 0) : ?>
			
				nbNotif = <?php echo sizeof($session->list_notif) ?>;
				if (nbNotif > 0) {
					// On actualise le nbCount en fonction de la variable de session
					$(".navbar-right #notif").css("padding-right","2px");
					$(".navbar-right #notif .badge").html(nbNotif).removeClass("hidden");
				}
			
			<?php endif; ?>


			$("#notifDD.dropdown").on("show.bs.dropdown", function(event) {
				get_notifications();
			}); 
		
		
		<?php endif; ?>
		
		
		/********* Gestion des MODALS de Connexion ***********/
		// On ferme les autres fenêtre au cas où elle seraient ouvertes
		$('#modal_login').on('show.bs.modal', function () {
			$("#modal_forgotten").modal('hide'); 
			$("#modal_msg").modal('hide'); 
		});
		// Gestion de l'autofocus sur les modal box
		$('#modal_login').on('shown.bs.modal', function () {
			$('#input').focus();
		});
		$('#modal_msg').on('shown.bs.modal', function () {
			$('#modal_close').focus();
		});
		$('#modal_msg').on('hidden.bs.modal', function () {
			$('#pass').focus();
		});
		$('#modal_forgotten').on('shown.bs.modal', function () {
			$('#email').focus();
		});
	});
	
	
	
	<?php if ($session->logged) : ?>
	/* ************ Notifications en AJAX (pas très utile pour l'instant mais au moins c'est fait)  ****************/
	function get_notifications() {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// On vide la liste de notif existante
		$("#notifDDMenu li:not(#invitNotifModele):not(.dropdown-header)").remove();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_members/get_notifications'); ?>",
		
			{
				'memberId': <?php echo $session->id ?>
			},
			
			function (return_data) {
				
				console.log("*** get_notifications : "+return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					
					$.each($obj['data'], function (index, value) {
						// On clone le modèle d'invit
						$tempInvit = $("#notifDD li#invitNotifModele").clone().attr("id",value["id"]);
						// On set le sender
						$tempInvit.find("#sender").html(value["pseudo"]);
						$tempInvit.find("#sender").attr("senderId",value["senderId"]);
						// On set la jam et le lien
						$tempInvit.find("#jam").html(value["title"]);
						$tempInvit.find("a").attr("href","<?php echo site_url('jam') ?>/"+value["jamSlug"]);
						
						// BUTTONS
						// Accept
						$tempInvit.find(".actionBtn #accept").on("click", function(event) {
							event.preventDefault();
							$jam = { "title" : value["title"], "slug" : value["jamSlug"], "date" : value["jamDate"] };
							invitation_answer($(this).parents("li").attr("id"), 1, $jam );	// 1 => accept
						});
						// Reject
						$tempInvit.find(".actionBtn #reject").on("click", function(event) {
							event.preventDefault();
							invitation_answer($(this).parents("li").attr("id"), 0);	// 0 => reject
						});
						
						// On place l'invit dans la liste
						$tempInvit.removeClass("hidden").appendTo($("#notifDDMenu"));
						
						// On récupère l'avatar
						$.ajax({
							url:'<?php echo base_url("images/avatar"); ?>'+'/'+value["senderId"]+".png",
							type:'HEAD',
							context: $("#notifDDMenu li[id="+value["id"]+"]"),
							success: function() {
								// On récupère l'id concerné par le ajax
								senderId = $(this).find("#sender").attr("senderId");
								// On actualise l'avatar dans la liste de notif
								$(this).find("img.img-circle").attr("src","<?php echo base_url('images/avatar/'); ?>/"+senderId+".png");
							},
							error: function() {
							}
						});
					});
					
				}
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
				}
			}
		);
	}
	
	
	// Gère la réponse à une invitation
	function invitation_answer($invitId, $state, $jam) {
		
		console.log("**** invitation_answer : "+$invitId+"  "+$state+"  "+$jam);
		
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax/update_invitation_state'); ?>",
		
			{	
				'invitId': $invitId,
				'state': $state
			},
		
			function (return_data) {
	
				console.log("update_invitation_state : "+return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// On retire l'invit des notifications
					$("#notifDD li#"+$invitId).remove();
					
					// On décrémente le compteur de notif
					nbNotif = parseInt($(".navbar-right #notif .badge").html());
					nbNotif--;
					if (nbNotif == 0) {
						$(".navbar-right #notif").css("padding-right","10px");
						$(".navbar-right #notif .badge").empty().addClass("hidden");
					}
					else $(".navbar-right #notif .badge").empty().html(nbNotif);
					
					
					// Accept invitation
					if ($state) {
						$msg = "En acceptant cette invitation, vous avez confirmé votre participation à la jam <b>"+$jam["title"]+"</b> du "+$jam["date"]+".";
						$confirm = "<div class='modal-footer'>";
							$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:document.location.href=\"<?php echo site_url('jam') ?>/"+$jam["slug"]+"\"'>Voir la jam</button>";
							$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Fermer</button>";
						$confirm += "</div>";
						
						$("#modal_msg .modal-dialog").removeClass("error success");
						$("#modal_msg .modal-dialog").addClass("default");
						$("#modal_msg").attr("data-backdrop","true");
						$("#modal_msg .modal-header").html("Invitation acceptée");
						$("#modal_msg .modal-body").html($msg);
						$("#modal_msg .modal-footer").html($confirm);
						$("#modal_msg").modal('show');
					}
				}
			}
		);
		
	}
	
	
	
	
	<?php endif; ?>
	
	

	/********* Login ***********/
	// Bootstrap s'occupe de la validation (email valide, pas de champs vide)
	function login() {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('members/login'); ?>",
		
			// On récupère les données nécessaires
			{
				'input':$('#input').val(),
				'pass':$('#pass').val()
			},
			
			// On traite la réponse du serveur			
			function (return_data) {
				
				console.log(return_data);
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';

				// Utilisateur loggé
				if ($obj['state'] == 1) {
					location.reload();
				}
				
				//Utilisateur non loggé
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');

					// On cache la modal de login et on vide ses input
					$("#pass").val("");
					$("#modal_msg").modal('show');
				}
			}
		);
	}
	
	
	
	/***** Modal box de Mot de passe oublié *******/
	function forgotten_box() {

		$("#modal_login").modal('hide');
		
		// On teste si le input est un email. Si oui on la recopie pour le forgotten
		var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
		if($("#input").val().match(re)) {
			$("#email").val($("#input").val());
		}
		
		$("#modal_forgotten").modal('show');
	}
	
	
	/***** Mot de passe oublié *******/
	function forgotten() {
	
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_members/forgotten'); ?>",
		
			// On récupère les données nécessaires
			{'email':$('#email').val()
			},
			
			// On traite la réponse du serveur			
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';

				// Mot de passe envoyé par email
				if ($obj['state'] == 1) {
					// Success
					$("#modal_msg .modal-dialog").removeClass("error");
					$("#modal_msg .modal-dialog").addClass("success");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Email envoyé");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
				}
				
				//Utilisateur non loggé
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
				}
				
				// On cache la modal de forgotten et on vide ses input
				$("#modal_forgotten").modal('hide');
				$("#email").val("");
				$("#modal_msg").modal('show');
			}
		);
	}
	
</script>
		
		
<!-- ***************************************************************** !-->

		
	<!-- <nav class="navbar navbar-inverse" data-spy="affix" data-offset-top="197"> !-->
	<nav id="menubar" class="navbar navbar-inverse bs-dark">

		<div class="row">

			<div class="navbar-header">
				<a id="brand_header" class="navbar-brand visible-xs <?php if($title == "Home") echo "active" ?>" href="<?php echo site_url() ?>">Grenoble Reggae Orchestra</a>
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>

			</div>

			<div id="navbar" class="collapse navbar-collapse">

				<ul class="nav navbar-nav">
					<!-- A faire, corriger le titre de page dans le header !-->
					<?php
						if ($title == 'Home') echo'<li id="home_link" class="active hidden-xs" style="display:none"><a href="'.site_url().'"><b>GRO</b></a></li>';
						else echo '<li id="home_link" class="hidden-xs" style="display:none"><a href="'.site_url().'"><b>GRO</b></a></li>';
					?>
					
					
					<!-- Jam !-->
					<!--<li class="<?php if ($title == "Jam") echo "active" ?>"><a href="<?php echo site_url('jam') ?>">Jam</a></li> !-->

					<!-- Dropdown Jam !-->
					<li class="dropdown <?php if ($title == "Jam") echo "active" ?>">
						<!-- si loggé, on liste les jam auxquelles le membre participe !-->
						<?php if ($session->logged && sizeof($session->list_event) > 0 ) : ?>
							<a class="dropdown-toggle disabled" data-toggle="dropdown" href="<?php echo site_url('jam') ?>">Jam
								<span class="caret"></span>
							</a>
							<ul class="dropdown-menu">
								<?php 
									foreach ($session->list_event as $event) {
										echo '<li class=""><a href="'.site_url('jam/').$event->slug.'">'.$event->title.'</a></li>';
									}
								?>
							</ul>
						<!-- pas loggé, item de jam !-->
						<?php else: ?>
							<a href="<?php echo site_url('jam') ?>">Jam</a>
						<?php endif; ?>
					</li>
					
					
					<!-- Dropdown A propos (About) !-->
					<li class="dropdown <?php if ($title == "About") echo "active" ?>">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">A propos
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li class="<?php if (isset($sub_title) && $sub_title == "Infos") echo "active" ?>"><a href="<?php echo site_url('infos') ?>"><small><i class="glyphicon glyphicon-question-sign"></i></small>&nbsp;&nbsp;&nbsp;Infos</a></li>
							<li class="<?php if (isset($sub_title) && $sub_title == "Répertoire") echo "active" ?>"><a href="<?php echo site_url('repertoire') ?>"><small><i class="glyphicon glyphicon-folder-open"></i></small>&nbsp;&nbsp;&nbsp;Répertoire</a></li>
							<li class="<?php if (isset($sub_title) && $sub_title == "Wishlist") echo "active" ?>"><a href="<?php echo site_url('wishlist') ?>"><small><i class="glyphicon glyphicon-comment"></i></small>&nbsp;&nbsp;&nbsp;Wishlist</a></li>
						</ul>
					</li>
						
					<!-- Contact !-->
					<li class="<?php if ($title == "Contact") echo "active" ?>"><a href="<?php echo site_url('contact') ?>">Contact</a></li>

					
					<!-- /*** Rubriques visibles uniquement par le super admin ***/ !-->
					<?php if ($session->superAdmin == "1" ):  ?>
					
						<!-- Dropdown Super Admin !-->
						<li class="dropdown <?php if ($title == "Admin") echo "active" ?>">
							<a class="dropdown-toggle" data-toggle="dropdown" href="#"><small><i class="glyphicon glyphicon-cog"></i></small>&nbsp;&nbsp;Admin
								<span class="caret"></span>
							</a>
							<ul class="dropdown-menu">
								<li class="<?php if (isset($sub_title) && $sub_title == "Médiathèque") echo "active" ?>"><a href="<?php echo site_url('morceau') ?>"><small><i class="glyphicon glyphicon-music"></i></small>&nbsp;&nbsp;&nbsp;Médiathèque</a></li>
								<li class="<?php if (isset($sub_title) && $sub_title == "Playlist") echo "active" ?>"><a href="<?php echo site_url('playlist') ?>"><small><i class="glyphicon glyphicon-th-list"></i></small>&nbsp;&nbsp;&nbsp;Playlist</a></li>
								<li class="<?php if (isset($sub_title) && $sub_title == "Membres") echo "active" ?>"><a href="<?php echo site_url('members') ?>"><small><i class="glyphicon glyphicon-list-alt"></i></small>&nbsp;&nbsp;&nbsp;Membres</a></li>
							</ul>
						</li>
					<?php endif; ?>
					
				</ul>
				
				<!-- RIGHT Navbar => Connexion !-->
				<ul class="nav navbar-nav navbar-right">
				
					<!-- Utilisateur connecté !-->
					<?php if ($session->logged) : ?>
					
						<!-- Avatar + pseudo !-->
						<li class="<?php if (strpos(uri_string(),"members") !== false && strpos(uri_string(),url_title($session->login)) !== false) echo "active"; ?>">
							<a id="memberLogin" idKey="<?php echo $session->id; ?>" value="<?php echo $session->login; ?>" href="<?php echo site_url('members'); ?>/<?php echo url_title($session->login); ?>">
								<!-- miniAvatar !-->
								<img id="miniAvatar" class='img-circle hidden' src='<?php echo base_url("images/icons/avatar2.png"); ?>' width="28" height="28">
								<?php echo $session->login; ?>
								<?php if (!$session->validMail) : ?>
									<sup><span class="badge badge-warning">!</span></sup>
								<?php endif; ?>
							</a>
						</li>
						
						<!-- Discussion Icon !-->
						<li class="<?php if ($title == "Messages") echo "active" ?>">
							<a id="dialog" href="<?php echo site_url('message'); ?>/<?php echo url_title($session->login); ?>">
								<i class="bi-chat"></i><span class="badge badge-warning hidden"></span>
							</a>
						</li>
						
						<!-- Notification Icon !-->
						<li id="notifDD" class="<?php if ($title == "Notifications") echo "active" ?> dropdown clickable">
							<a id="notif" href="#" class="dropdown-toggle" data-toggle="dropdown">
								<i class="bi-bell"></i><span class="badge badge-warning hidden"></span>
							</a>
							<ul id="notifDDMenu" class="dropdown-menu">
								<li class="dropdown-header">Notifications</li>
								<!-- Modèle d'invitation !-->
								<li id="invitNotifModele" class="hidden">
									<a href="">
										<img class="img-circle" src="<?php echo base_url("images/icons/avatar2.png"); ?>" width="50" height="50">
										<div>
											<b><span id="sender"></span></b> vous a envoyé une invitation pour la jam <b><span id="jam"></span></b>.
											<div class="actionBtn">
												<button id="accept" type="button" class="btn btn-highlight">Confirmer</button>
												<button id="reject" type="button" class="btn">Supprimer</button>
											</div>
										</div>
									</a>
								</li>
							</ul>
						</li>
						
						<!-- Logout !-->
						<li><a id="logout" href="<?php echo site_url('members/logout'); ?>"><span class="glyphicon glyphicon-log-out"></span> Déconnexion</a></li>
						
					<!-- Utilisateur non connecté !-->
					<?php else: ?>
						<li class="<?php if (strpos(uri_string(),"members") && strpos(uri_string(),"create")) echo "active" ?>"><a href="<?php echo site_url('members/create'); ?>"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;Inscription</a></li>
						<li><a id="login_link" data-toggle="modal" href="#modal_login"><span class="glyphicon glyphicon-log-in"></span>&nbsp;&nbsp;Connexion</a></li>
					<?php endif; ?>
				</ul>

			</div><!--/.nav-collapse -->

		</div>

	</nav>
</div> <!-- on ferme le canevas !-->
</div> <!-- on ferme le wrapper !-->

	

<!-- on ouvre le cadre de contenu dans le menu et on le ferme dans le footer-->
<div id="content" class="container">

	<!-- Box de connexion !-->
	<div id="modal_login" class="modal fade" role="dialog">
		<div class="modal-dialog default modal-sm">
			<div class="modal-content">
				<div class="modal-header lead">Connexion</div>
				<div class="modal-body">
					
					<!-- Formulaire !-->
					<form method="post" action="javascript:login()" name="login_form">
					
						<!-- Nom ou Email!-->
						<div class="row">
							<input id="input" type="text" class="form-control form-group" required="true" name="email" placeholder="Pseudo ou Email">
						</div>
						
						<!-- Pass !-->
						<div class="row">
							<input id="pass" type="password" class="form-control form-group" required="true" name="pass" placeholder="Mot de passe">
						</div>
						
						<!-- Connexion !-->
						<div class="row">
							<button type="submit" class="btn btn-default form-control">Connexion</button>
						</div>
						
					</form>
					
				</div>
				
				<!-- Mot de passe oublié !-->
				<div class="modal-footer">
					<div class="row text-center">
						<a href="javascript:forgotten_box()">Mot de passe oublié</a>
					</div>
				</div>
			</div>
		</div>
	</div>


	<!-- Box de mot de passe oublié !-->
	<div id="modal_forgotten" class="modal fade" role="dialog">
		<div class="modal-dialog default modal-sm">
			<div class="modal-content">
				<div class="modal-header lead">Mot de passe oublié</div>
				<div class="modal-body">
					
					<!-- Formulaire !-->
					<form method="post" action="javascript:forgotten()" name="login_form">
					
						<!-- email !-->
						<div class="row">
							<input id="email" type="email" class="form-control form-group" required="true" name="email" placeholder="Email">
						</div>

						<!-- Connexion !-->
						<div class="row">
							<button type="submit" class="btn btn-default form-control">Envoyer mot de passe</button>
						</div>
						
					</form>
					
				</div>
			</div>
		</div>
	</div>


	<!-- Dialogue box de resultat !-->
	<div id="modal_msg" class="modal fade" role="dialog" data-keyboard="true" data-backdrop="static">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header lead"></div>
				<div class="modal-body"></div>
				<div class="modal-footer"></div>
			</div>
		</div>
	</div>
