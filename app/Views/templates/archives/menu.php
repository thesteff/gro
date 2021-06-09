		<?php
		// On fixe les variables de sessions
		$data = array('url' => current_url());
		$this->session->set_userdata($data);
		?>

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


		<div id="menu">
			<ul>
				<!-- A faire, corriger le titre de page dans le header !-->
				<?php
					if (ucfirst($title) == 'Home') echo'<li class="active"><a href="/index.php/">Accueil</a></li>';
					else echo'<li><a href="/index.php/">Accueil</a></li>';
					
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
		
		<!-- on ouvre le cadre de contenu dans le menu et on le ferme dans le footer-->
		<?php
			if(isset($special_content)) echo "<div class='special_content'>";
			else echo "<div class='content'>";
		?>