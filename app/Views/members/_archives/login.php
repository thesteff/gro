<?php if ($this->config->item("bootstrap") == true): ?>




<?php else: ?>

	<div id="members_login" class="content">

		<div class="block_left">
			<h3 class="block_title">Connexion</h3>
			
			<div class="form_block">
				
				<?php if(isset($error)): ?>
				<div class="error_message"><?php echo $error; ?></div>
				<?php endif;?>
				
				<?php echo form_open('members/login') ?>

				<label for="pseudo">Pseudo ou email</label>
				<?php echo form_error('pseudo'); ?>
				<input class="right" type="text" name="pseudo" value="<?php echo set_value('pseudo');?>" autofocus />
				<br />
				
				<label for="pass">Mot de passe</label>
				<?php echo form_error('pass'); ?>
				<input class="right" type="password" name="pass" value="<?php echo set_value('pass');?>" autocomplete="off" />
				<br />
				<div class="right note" style="margin-top:-15;"><small><a href="<?php echo site_url().'/members/forgotten'; ?>">j'ai oublié mon mot de passe</a></small></div>

				<input class="right button" type="submit" name="submit" value="Connexion" />
				<br>
				
				<?php echo form_close() ?>
				
			</div>
		</div>

<?php endif; ?>