<div id="members_login" class="content">

	<div class="block_left">
		<h3 class="block_title">Mot de passe oublié</h3>
		
		<div class="form_block">
			
			<?php if(isset($error)): ?>
			<div class="error_message"><?php echo $error; ?></div>
			<?php endif;?>
			
			<?php echo form_open('members/forgotten') ?>

			<label for="email">Email</label>
			<?php echo form_error('email'); ?>
			<input class="right" type="text" name="email" value="<?php echo set_value('email');?>" autofocus />
			<br />
			
			<input class="right button" type="submit" name="submit" value="Envoyer le mot de passe" />
			<br>
			
			<?php echo form_close() ?>
			
		</div>
	</div>

