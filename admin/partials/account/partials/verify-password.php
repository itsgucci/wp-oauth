<?php
	function mo_oauth_client_verify_password_ui() { ?>
		<form name="f" method="post" action="">
			<?php wp_nonce_field('mo_oauth_verify_password_form','mo_oauth_verify_password_form_field'); ?>
			<input type="hidden" name="option" value="mo_oauth_verify_customer" />
			<div class="mo_table_layout">
				<div id="toggle1" class="mo_panel_toggle">
					<h3>Login with miniOrange</h3>
				</div>
				<p><b>It seems you already have an account with miniOrange. Please enter your miniOrange email and password.<br/> <a href="#mo_oauth_forgot_password_link">Click here if you forgot your password?</a></b></p>

				<div id="panel1">
					</p>
					<table class="mo_settings_table">
						<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo_table_textbox" type="email" name="email"
								required placeholder="person@example.com"
								value="<?php echo get_option('mo_oauth_admin_email');?>" /></td>
						</tr>
						<td><b><font color="#FF0000">*</font>Password:</b></td>
						<td><input class="mo_table_textbox" required type="password"
							name="password" placeholder="Choose your password" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><input type="submit" name="submit" value="Login" class="button button-primary button-large" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</form>

								<input type="button" name="back-button" id="mo_oauth_back_button" onclick="document.getElementById('mo_oauth_change_email_form').submit();" value="Back" class="button button-primary button-large" />

								<form id="mo_oauth_change_email_form" method="post" action="">
									<?php wp_nonce_field('mo_oauth_change_email_form','mo_oauth_change_email_form_field'); ?>
									<input type="hidden" name="option" value="mo_oauth_change_email" />
								</form></td>
							</td>
						</tr>
					</table>
				</div>
			</div>

		<form name="f" method="post" action="" id="mo_oauth_forgotpassword_form">
			<?php wp_nonce_field('mo_oauth_forgotpassword_form','mo_oauth_forgotpassword_form_field'); ?>
			<input type="hidden" name="option" value="mo_oauth_forgot_password_form_option"/>
		</form>
		<script>
			jQuery("a[href=\"#mo_oauth_forgot_password_link\"]").click(function(){
				jQuery("#mo_oauth_forgotpassword_form").submit();
			});
		</script>

		<?php
	}