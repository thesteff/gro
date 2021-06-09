<?php
	// On fixe les variables de sessions
	$data = array('url' => current_url());
	$this->session->set_userdata($data);
?>



		<!-- NOT BOOTSTRAP Connect !-->
		<?php if ($this->config->item("bootstrap") == false): ?>
			<div id="connect">
			<?php
				if (!$this->session->userdata('login') && !$this->session->userdata('logged')) {
					echo anchor(site_url().'/members/login','se connecter').'  |  '.anchor(site_url().'/members/create','s\'inscrire');
				}
				else {
					echo '<span class="login">Bienvenue <b>'.$this->session->userdata('login').'</b>&nbsp;&nbsp;|</span>&nbsp;&nbsp;';
					echo anchor(site_url().'/members/logout','se d&eacute;connecter');
				}
			?>
			</div>
		<?php endif; ?>

		
		<!-- BOOTSTRAP 3.3.7 !-->
		<?php if ($this->config->item("bootstrap") == true): ?>


	<script type="text/javascript">

		$(function() {
			
			// On ferme les autres fenêtre au cas où elle seraient ouvertes
			$('#modal_login').on('show.bs.modal', function () {
				$("#modal_forgotten").modal('hide'); 
				$("#modal_msg").modal('hide'); 
			});
			// Gestion de l'autofocus sur les modal box
			$('#modal_login').on('shown.bs.modal', function () {
				$('#id_item').focus();
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
	
	
		/********* Login ***********/
		// Bootstrap s'occupe de la validation (email valide, pas de champs vide)
		function login() {
			
			// On change le curseur
			document.body.style.cursor = 'wait';
			
			// Requète ajax au serveur
			$.post("<?php echo site_url(); ?>/ajax/member_login",
			
				// On récupère les données nécessaires
				{'id_item':$('#id_item').val(),
				'pass':$('#pass').val()
				},
				
				// On traite la réponse du serveur			
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					// On change le curseur
					document.body.style.cursor = 'default';

					// Utilisateur loggé
					if ($obj['state'] == 1) {
						window.location.href = $obj['data'];
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
			
			// On teste si le id_item est un email. Si oui on la recopie pour le forgotten
			var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
			if($("#id_item").val().match(re)) {
				$("#email").val($("#id_item").val());
			}
			
			$("#modal_forgotten").modal('show');
		}
		
		
		/***** Mot de passe oublié *******/
		function forgotten() {
		
			// On change le curseur
			document.body.style.cursor = 'wait';
			
			// Requète ajax au serveur
			$.post("<?php echo site_url(); ?>/ajax/forgotten",
			
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
						<a id="brand_header" class="navbar-brand visible-xs <?php if($title == "Home") echo "active" ?>" href="/index.php/">Grenoble Reggae Orchestra</a>
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
								if ($title == 'Home') echo'<li id="home_link" class="active hidden-xs" style="display:none"><a href="/index.php/"><b>GRO</b></a></li>';
								else echo'<li id="home_link" class="hidden-xs" style="display:none"><a href="/index.php/"><b>GRO</b></a></li>';
								
								if ($title == 'Jam') {
									// permet un retour à la section jam avec la dernière jam selectionnée
									if ($this->uri->segment(1) == "jam" && $this->uri->segment(2) == "inscriptions") echo '<li class="active"><a href="/index.php/jam/'.$this->uri->segment(3).'">Jam</a></li>';
									else echo'<li class="active"><a href="/index.php/jam/">Jam</a></li>';
								}
								else echo'<li><a href="/index.php/jam/">Jam</a></li>';
							?>
							
							<!-- Dropdown A propos (About) !-->
							<li class="dropdown <?php if ($title == "About") echo "active" ?>">
								<a class="dropdown-toggle" data-toggle="dropdown" href="#">A propos
									<span class="caret"></span>
								</a>
								<ul class="dropdown-menu">
									<li class="<?php if (isset($sub_title) && $sub_title == "Infos") echo "active" ?>"><a href="/index.php/infos"><small><i class="glyphicon glyphicon-question-sign"></i></small>&nbsp;&nbsp;&nbsp;Infos</a></li>
									<li class="<?php if (isset($sub_title) && $sub_title == "Répertoire") echo "active" ?>"><a href="/index.php/repertoire"><small><i class="glyphicon glyphicon-folder-open"></i></small>&nbsp;&nbsp;&nbsp;Répertoire</a></li>
									<li class="<?php if (isset($sub_title) && $sub_title == "Wishlist") echo "active" ?>"><a href="/index.php/wishlist"><small><i class="glyphicon glyphicon-comment"></i></small>&nbsp;&nbsp;&nbsp;Wishlist</a></li>
								</ul>
							</li>
								
							<!-- Contact !-->
							<li class="<?php if ($title == "Contact") echo "active" ?>"><a href="/index.php/contact">Contact</a></li>
		
							
							<!-- /*** Rubriques visibles uniquement par le super admin ***/ !-->
							<?php if ($this->session->userdata('admin') == "1"): ?>
							
								<!-- Dropdown Admin !-->
								<li class="dropdown <?php if ($title == "Admin") echo "active" ?>">
									<a class="dropdown-toggle" data-toggle="dropdown" href="#"><small><i class="glyphicon glyphicon-cog"></i></small>&nbsp;&nbsp;Admin
										<span class="caret"></span>
									</a>
									<ul class="dropdown-menu">
										<li class="<?php if (isset($sub_title) && $sub_title == "News") echo "active" ?>"><a href="/index.php/news"><small><i class="glyphicon glyphicon-info-sign"></i></small>&nbsp;&nbsp;&nbsp;News</a></li>
										<li class="<?php if (isset($sub_title) && $sub_title == "Songs") echo "active" ?>"><a href="/index.php/songs"><small><i class="glyphicon glyphicon-music"></i></small>&nbsp;&nbsp;&nbsp;Songs</a></li>
										<li class="<?php if (isset($sub_title) && $sub_title == "Playlist") echo "active" ?>"><a href="/index.php/playlist"><small><i class="glyphicon glyphicon-th-list"></i></small>&nbsp;&nbsp;&nbsp;Playlist</a></li>
										<li class="<?php if (isset($sub_title) && $sub_title == "Membres") echo "active" ?>"><a href="/index.php/members"><small><i class="glyphicon glyphicon-list-alt"></i></small>&nbsp;&nbsp;&nbsp;Membres</a></li>
									</ul>
								</li>
							<?php endif; ?>
							
						</ul>
						
						
						<!-- Right Navbar => Connexion !-->
						<ul class="nav navbar-nav navbar-right">
						
							<!-- Utilisateur connecté !-->
							<?php if ($this->session->userdata('login') || $this->session->userdata('logged')) : ?>
								<li class="<?php if ($this->uri->segment(1) == "members" && $this->uri->segment(2) == $this->session->userdata('login')) echo "active" ?>"><a href="/index.php/members/<?php echo $this->session->userdata('login'); ?>"><?php echo $this->session->userdata('login'); ?></a></li>
								<li><a href="<?php echo site_url(); ?>/members/logout"><span class="glyphicon glyphicon-log-out"></span> Déconnexion</a></li>
							<?php else: ?>
							<!-- Utilisateur non connecté !-->							
								<li class="<?php if ($this->uri->segment(1) == "members" && $this->uri->segment(2) == "create") echo "active" ?>"><a href="<?php echo site_url(); ?>/members/create"><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;Inscription</a></li>
								<li><a id="login_link" data-toggle="modal" href="#modal_login"><span class="glyphicon glyphicon-log-in"></span>&nbsp;&nbsp;Connexion</a></li>
							<?php endif; ?>
						</ul>

					</div><!--/.nav-collapse -->

				</div>

			</nav>
		</div> <!-- on ferme le canevas !-->
		</div> <!-- on ferme le wrapper !-->
		
		
		
		<!-- // On gère l'affix et l'affichage de l'item home (GRP) en fonction de l'event affix de bootstrap !-->
		<script>
		$(document).ready(function(){
			$offset = $('#page_title').height() > 0 ? $('#page_title').height() : 200;
			$("#menubar").affix({offset: {top: $offset } });
			// On recalcule la taille de l'offset du header sur un resize
			$(window).on("resize",function() {
				$("#menubar").affix({offset: {top: $offset } });
			});
			$("#menubar").on('affix.bs.affix', function() {
				$('#home_link').css('display','block');
			});
			$("#menubar").on('affix-top.bs.affix', function() {
				$('#home_link').css('display','none');
			});

		});
		</script>
		
		
		<?php else: ?>
			<div id="menu">
				<ul>
					<!-- A faire, corriger le titre de page dans le header !-->
					<?php
						if (ucfirst($title) == 'Home') echo'<li class="active"><a href="/index.php/">Accueil</a></li>';
						else echo '<li><a href="/index.php/">Accueil</a></li>';
						
						if ($title == 'Jam') {
							// permet un retour à la section jam avec la dernière jam selectionnée
							if ($this->uri->segment(1) == "jam" && $this->uri->segment(2) == "inscriptions") echo '<li class="active"><a href="/index.php/jam/'.$this->uri->segment(3).'">Jam</a></li>';
							else echo'<li class="active"><a href="/index.php/jam/">Jam</a></li>';
						}
						else echo'<li><a href="/index.php/jam/">Jam</a></li>';
							
						/* Rubriques visibles si connecté */
						if ($this->session->userdata('login') || $this->session->userdata('logged')) {
							
							if ($title == 'Profil') echo'<li class="active"><a href="/index.php/members/'.$this->session->userdata('login').'">Profil</a></li>';
							else echo'<li><a href="/index.php/members/'.$this->session->userdata('login').'">Profil</a></li>';

							
							/* Rubriques visibles uniquement par le super admin */
							if ($this->session->userdata('admin') == "1") {
								if (stripos(uri_string(3),"news") === 0) echo'<li class="active"><a class="admin" href="/index.php/news">News</a></li>';
								else echo'<li><a class="admin" href="/index.php/news">News</a></li>';
								
								if ($title == 'Songs') echo'<li class="active"><a class="admin" href="/index.php/songs">Songs</a></li>';
								else echo'<li><a class="admin" href="/index.php/songs">Songs</a></li>';
								
								if ($title == 'Playlist') echo'<li class="active"><a class="admin" href="/index.php/playlist">Playlist</a></li>';
								else echo'<li><a class="admin" href="/index.php/playlist">Playlist</a></li>';
							}
						}
						
						if ($title == "About") echo'<li class="active"><a href="/index.php/infos">A propos</a></li>';
						else echo'<li><a href="/index.php/infos">A propos</a></li>';
						
						if ($title == "Contact") echo'<li class="active"><a href="/index.php/contact">Contact</a></li>';
						else echo'<li><a href="/index.php/contact">Contact</a></li>';
					?>
				</ul>
			</div>
		
		<?php endif; ?>
		
		
		
		<!-- on ouvre le cadre de contenu dans le menu et on le ferme dans le footer-->
		<?php
			
			if ($this->config->item("bootstrap") == true) {
				echo '<div id="content" class="container">';
			}
			
			else {
		
				if(isset($special_content)) echo "<div class='special_content'>";
				else echo "<div class='content'>";
			}
		?>
		
		
	
<?php if ($this->config->item("bootstrap") == true): ?>	

	<!-- Box de connexion !-->
	<div id="modal_login" class="modal fade" role="dialog">
		<div class="modal-dialog default modal-sm">
			<div class="modal-content">
				<div class="modal-header lead">Connexion</div>
				<div class="modal-body">
					
					<!-- Formulaire !-->
					<form method="post" action="javascript:login()" name="login_form">
					
						<!-- Nom !-->
						<div class="row">
							<input id="id_item" type="text" class="form-control form-group" required="true" name="email" placeholder="Pseudo ou Email">
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

	
<?php endif; ?>


