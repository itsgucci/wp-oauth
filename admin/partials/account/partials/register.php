<?php

function mo_oauth_client_register_ui() {
	update_option ( 'mo_oauth_client_new_registration', 'true' );
	$current_user = wp_get_current_user();
	?>
			<!--Register with miniOrange-->
		<form name="f" method="post" action="">
			<?php wp_nonce_field('mo_oauth_register_form','mo_oauth_register_form_field'); ?>
			<input type="hidden" name="option" value="mo_oauth_register_customer" />
			<div class="mo_table_layout">
				<div id="toggle1" class="">
					<h3>Register with miniOrange <small style="font-size: x-small;">[OPTIONAL]</small></h3> 
				</div>
				<div id="panel1">
					<!--<p><b>Register with miniOrange</b></p>-->
					<!-- <p>Please enter a valid Email ID that you have access to. You will be able to move forward after verifying an OTP that we will be sending to this email.
					</p> -->
					<p style="font-size:14px;"><b>Why should I register? </b></p>
	                    <div id="help_register_desc" style="background: aliceblue; padding: 10px 10px 10px 10px; border-radius: 10px;">
	                        You should register so that in case you need help, we can help you with step by step instructions.
	                        <b>You will also need a miniOrange account to upgrade to the premium version of the plugins.</b> We do not store any information except the email that you will use to register with us.
	                    </div>
                    </p>
					<table class="mo_settings_table">
						<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo_table_textbox" type="email" name="email"
								required placeholder="person@example.com"
								value="<?php echo get_option('mo_oauth_admin_email');?>" />
							</td>
						</tr>
						<tr class="hidden">
							<td><b><font color="#FF0000">*</font>Website/Company Name:</b></td>
							<td><input class="mo_table_textbox" type="text" name="company"
							required placeholder="Enter website or company name"
							value="<?php echo $_SERVER['SERVER_NAME']; ?>"/></td>
						</tr>
						<tr  class="hidden">
							<td><b>&nbsp;&nbsp;First Name:</b></td>
							<td><input class="mo_openid_table_textbox" type="text" name="fname"
							placeholder="Enter first name" value="<?php echo $current_user->user_firstname;?>" /></td>
						</tr>
						<tr class="hidden">
							<td><b>&nbsp;&nbsp;Last Name:</b></td>
							<td><input class="mo_openid_table_textbox" type="text" name="lname"
							placeholder="Enter last name" value="<?php echo $current_user->user_lastname;?>" /></td>
						</tr>

						<tr  class="hidden">
							<td><b>&nbsp;&nbsp;Phone number :</b></td>
							 <td><input class="mo_table_textbox" type="text" name="phone" pattern="[\+]?([0-9]{1,4})?\s?([0-9]{7,12})?" id="phone" title="Phone with country code eg. +1xxxxxxxxxx" placeholder="Phone with country code eg. +1xxxxxxxxxx" value="<?php echo get_option('mo_oauth_client_admin_phone');?>" />
							 This is an optional field. We will contact you only if you need support.</td>
							</tr>
						</tr>
						<tr  class="hidden">
							<td></td>
							<td>We will call only if you need support.</td>
						</tr>
						<tr>
							<td><b><font color="#FF0000">*</font>Password:</b></td>
							<td><input class="mo_table_textbox" required type="password"
								name="password" placeholder="Choose your password (Min. length 8)" /></td>
						</tr>
						<tr>
							<td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
							<td><input class="mo_table_textbox" required type="password"
								name="confirmPassword" placeholder="Confirm your password" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<!-- <td><br /><input type="submit" name="submit" value="Save" style="width:100px;"
								class="button button-primary button-large" /></td> -->
							<td><br><input type="submit" name="submit" value="Register" class="button button-primary button-large"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="button" name="mo_oauth_client_goto_login" id="mo_oauth_client_goto_login" value="Already have an account?" class="button button-primary button-large"/>&nbsp;&nbsp;</td>
						</tr>
					</table>
				</div>
			</div>
		</form>
		<form name="f1" method="post" action="" id="mo_oauth_client_goto_login_form">
            <?php wp_nonce_field('mo_oauth_goto_login_form','mo_oauth_goto_login_form_field'); ?>
            <input type="hidden" name="option" value="mo_oauth_client_goto_login"/>
        </form>
        <script>
           	jQuery("#phone").intlTelInput();
            jQuery('#mo_oauth_client_goto_login').click(function () {
                jQuery('#mo_oauth_client_goto_login_form').submit();
            } );
        </script>
		<!-- <script>
			jQuery("#phone").intlTelInput();
		</script> -->
		<?php
}

function mo_oauth_client_show_customer_info() {
	?>
	<div class="mo_table_layout" >
		<h2>Thank you for registering with miniOrange.</h2>

		<table border="1"
		   style="background-color:#FFFFFF; border:1px solid #CCCCCC; border-collapse: collapse; padding:0px 0px 0px 10px; margin:2px; width:85%">
		<tr>
			<td style="width:45%; padding: 10px;">miniOrange Account Email</td>
			<td style="width:55%; padding: 10px;"><?php echo get_option( 'mo_oauth_admin_email' ); ?></td>
		</tr>
		<tr>
			<td style="width:45%; padding: 10px;">Customer ID</td>
			<td style="width:55%; padding: 10px;"><?php echo get_option( 'mo_oauth_client_admin_customer_key' ) ?></td>
		</tr>
		</table>
		<br /><br />

	<table>
	<tr>
	<td>
	<form name="f1" method="post" action="" id="mo_oauth_goto_login_form">
		<?php wp_nonce_field('mo_oauth_goto_login_form','mo_oauth_goto_login_form_field'); ?>
		<input type="hidden" value="change_miniorange" name="option"/>
		<input type="submit" value="Change Email Address" class="button button-primary button-large"/>
	</form>
	</td><td>
	<a href="<?php echo add_query_arg( array( 'tab' => 'licensing' ), htmlentities( $_SERVER['REQUEST_URI'] ) ); ?>"><input type="button" class="button button-primary button-large" value="Check Licensing Plans"/></a>
	</td>
	</tr>
	</table>

				<br />
	</div>

	<?php
}