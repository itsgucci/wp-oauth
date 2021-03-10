<?php

	function mo_oauth_client_update_app_page($appname){

	$appslist = get_option('mo_oauth_apps_list');
	$currentappname = $appname;
	$currentapp = null;
	foreach($appslist as $key => $app){
		if($appname == $key){
			$currentapp = $app;
			break;
		}
	}
	if(!$currentapp)
		return;
	$is_other_app = true;

        $currentAppId = $currentapp['appId'];
        $refapp = mo_oauth_client_get_app($currentAppId);
        $valid_discovery = get_option( 'mo_discovery_validation') ? get_option( 'mo_discovery_validation') : "valid";
        $is_invalid = "<i class=\"fa fa-thumbs-down\" style=\"color:#ff000085; font-size: 30px;\"></i>";
        $is_valid = "<i class=\"fa fa-thumbs-up\" style=\"color:#0080007d; font-size: 30px;\"></i>";
		
	?>
		<div id="toggle2" class="mo_panel_toggle">
			<h3>Configure OAuth Provider</h3>
		</div>
		<div id="mo_oauth_update_app">
			
		<form id="form-common" name="form-common" method="post" action="admin.php?page=mo_oauth_settings&tab=config&action=update&app=<?php echo $currentappname; ?>">
		<?php wp_nonce_field('mo_oauth_add_app_form','mo_oauth_add_app_form_field'); ?>
		<input class="mo_table_textbox" required="" type="hidden" name="mo_oauth_app_name" value="<?php echo isset($currentapp['appId']) ? $currentapp['appId'] : "other";?>">
		<input type="hidden" name="option" value="mo_oauth_add_app" />
		<input type="hidden" id="mo_oauth_app_nameid" value="<?php echo $currentappname;?>">
		<input type="hidden" name="mo_oauth_app_type" value="<?php echo $currentapp['apptype'];?>">
		<input class="mo_table_textbox" required="" type="hidden" name="mo_oauth_custom_app_name" value="<?php echo $currentappname;?>">

		<table class="mo_settings_table">
			<tr id="mo_oauth_display_app_name_div">
				<td><strong>Display App Name:</strong><br>&emsp;<font color="#FF0000"><small><a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer">[STANDARD]</a></small></font></td>
				<td><input disabled class="mo_table_textbox" type="text" value="Login with <?php echo $currentappname;?>" ></td>
			</tr>
			<tr><td><strong>Redirect / Callback URL: </strong><br>&emsp;<font><small>Editable in <a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer">[STANDARD]</a></small></font></td>
			<td><input class="mo_table_textbox" id="callbackurl" readonly="true" type="text" name="mo_oauth_callback_url" value='<?php echo $currentapp['redirecturi'];?>'>
			&nbsp;&nbsp;
			<div class="tooltip" style="display: inline;"><span class="tooltiptext" id="moTooltip">Copy to clipboard</span><i class="fa fa-clipboard fa-border" style="font-size:20px; align-items: center;vertical-align: middle;" aria-hidden="true" onclick="copyUrl()" onmouseout="outFunc()"></i></div>
			</td>
			</tr>
			<tr>
				<td><strong><font color="#FF0000">*</font>Client ID:</strong></td>
				<td><input class="mo_table_textbox" required="" type="text" name="mo_oauth_client_id" value="<?php echo $currentapp['clientid'];?>"></td>
			</tr>
			<tr>
				<td><strong><font color="#FF0000">*</font>Client Secret:</strong></td>
				<td>
					<input id="mo_oauth_client_secret" class="mo_table_textbox" required="" type="password"  name="mo_oauth_client_secret" value="<?php echo $currentapp['clientsecret'];?>">
					
					<i class="fa fa-eye" onclick="showClientSecret()" id="show_button" style="margin-left:-30px; cursor:pointer;"></i>
				</td>
			</tr>
			<tr>
				<td><strong>Scope:</strong></td>
				<td><input class="mo_table_textbox" type="text" name="mo_oauth_scope" value="<?php echo $currentapp['scope'];?>"></td>
			</tr>
            <?php if(isset($refapp->discovery) && $refapp->discovery !="" && get_option('mo_existing_app_flow') == true ) { ?>
                <tr>
                    <td><input type="hidden" id="mo_oauth_discovery" name="mo_oauth_discovery" value="<?php echo $refapp->discovery; ?>"></td>
                </tr>
                <?php
                if(isset($currentapp['domain'])) { ?>
                <tr>
                    <td><strong><font color="#FF0000"></font><?php echo $currentappname; ?> Domain:</strong></td>
                    <td><input <?php if($valid_discovery == 'invalid') echo 'style="border-color:red;"'?> class="mo_table_textbox" type="text" name="mo_oauth_provider_domain"
                               value="<?php echo $currentapp['domain']; ?>">&nbsp;&nbsp;&nbsp;<?php if($valid_discovery == 'valid'){echo $is_valid; } else echo $is_invalid; ?></td>
                </tr>
            <?php } elseif (isset($currentapp['tenant'])) { ?>
                    <tr>
                        <td><strong><font color="#FF0000"></font><?php echo $currentappname; ?> Tenant:</strong></td>
                        <td><input <?php if($valid_discovery == 'invalid') echo 'style="border-color:red;"'?> class="mo_table_textbox" type="text" name="mo_oauth_provider_tenant"
                                   value="<?php echo $currentapp['tenant']; ?>">&nbsp;&nbsp;&nbsp;<?php if($valid_discovery == 'valid'){echo $is_valid; } else echo $is_invalid; ?></td>
                    </tr>
                    <?php } if(isset($currentapp['policy'])) { ?>
                <tr>
                    <td><strong><font color="#FF0000"></font><?php echo $currentappname; ?> Policy:</strong></td>
                    <td><input <?php if($valid_discovery == 'invalid') echo 'style="border-color:red;"'?> class="mo_table_textbox" type="text" name="mo_oauth_provider_policy" value="<?php echo $currentapp['policy']; ?>">&nbsp;&nbsp;&nbsp;<?php if($valid_discovery == 'valid'){echo $is_valid; } else echo $is_invalid; ?></td>
                </tr>
            <?php } elseif(isset($currentapp['realm'])) { ?>
                    <tr>
                        <td><strong><font color="#FF0000"></font><?php echo $currentappname; ?> Realm:</strong>
                        </td>
                        <td><input <?php if($valid_discovery == 'invalid') echo 'style="border-color:red;"'?> class="mo_table_textbox" type="text" name="mo_oauth_provider_realm" value="<?php echo $currentapp['realm']; ?>">&nbsp;&nbsp;&nbsp;<?php if($valid_discovery == 'valid'){echo $is_valid; } else echo $is_invalid; ?></td>
                    </tr>
                    <?php
                }
            }


            if($is_other_app && $currentapp['appId']!='twitter'){
			    if(!isset($refapp->discovery) || $refapp->discovery =="" || !get_option('mo_existing_app_flow')) { ?>
				<tr  id="mo_oauth_authorizeurl_div">
					<td><strong><font color="#FF0000">*</font>Authorize Endpoint:</strong></td>
					<td><input class="mo_table_textbox" required="" type="text" id="mo_oauth_authorizeurl" name="mo_oauth_authorizeurl" value="<?php echo htmlentities($currentapp['authorizeurl']);?>"></td>
				</tr>
				<tr id="mo_oauth_accesstokenurl_div">
					<td><strong><font color="#FF0000">*</font>Access Token Endpoint:</strong></td>
					<td><input class="mo_table_textbox" required="" type="text" id="mo_oauth_accesstokenurl" name="mo_oauth_accesstokenurl" value="<?php echo $currentapp['accesstokenurl'];?>"></td>
				</tr>
				<?php if( isset($currentapp['apptype']) && $currentapp['apptype'] != 'openidconnect') {
						$oidc = false;
					} else {
						$oidc = true;
					}
					?>
				<tr id="mo_oauth_resourceownerdetailsurl_div">
					<td><strong><?php if($oidc === false) { echo '<font color="#FF0000">*</font>'; } ?>Get User Info Endpoint:</strong></td>
					<td><input class="mo_table_textbox" type="text" id="mo_oauth_resourceownerdetailsurl" name="mo_oauth_resourceownerdetailsurl" <?php if($oidc === false) { echo 'required';} ?> value="<?php if(isset($currentapp['resourceownerdetailsurl'])) { echo $currentapp['resourceownerdetailsurl']; } ?>"></td>
				</tr>
                <?php } ?>

                <tr>
                    <td><strong>Send client credentials in:</strong></td>
                    <td><div style="padding:5px;"></div><input type="checkbox" class="mo_table_textbox" name="mo_oauth_authorization_header" <?php if(isset($currentapp['send_headers'])){if($currentapp['send_headers'] == 1){ echo 'checked';}}else {echo 'checked';}?> value="1"> Header<span style="padding:0px 0px 0px 8px;"></span><input type="checkbox" class="mo_table_textbox" name="mo_oauth_body"<?php if(isset($currentapp['send_body'])){if($currentapp['send_body'] == 1){ echo 'checked';}}else {echo 'checked';}?> value="1"> Body<div style="padding:5px;"></div></td>
                </tr>
                <tr>
				<td><strong>State Parameter :</strong></td>
				<td><div style="padding:5px;"></div><input type="checkbox" name="mo_oauth_state" value ="1" <?php if(isset($currentapp['send_state'])){if($currentapp['send_state'] == 1){ echo 'checked';}}else {echo 'checked';} ?>/>Send state parameter</td>
				<td><br></td>
			</tr>
			<tr>
				<td><strong>Group User Info Endpoint:</strong><br>&emsp;<font color="#FF0000"><small><a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer">[PREMIUM]</a></small></font></td>
				<td><input class="mo_table_textbox" type="text" value="" disabled></td>
			</tr>
			<tr>
				<td><strong>JWKS URL:</strong><br>&emsp;<font color="#FF0000"><small><a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer">[PREMIUM]</a></small></font></td>
				<td><input class="mo_table_textbox" type="text" value="" disabled></td>
			</tr>
			
			<tr>
				<td><br></td>
				<td><br></td>
			</tr>

			<?php } ?>
			<tr>
				<tr>
				<td><strong>Login Button:</strong></td>
				<td><div style="padding:5px;"></div><input type="checkbox" name="mo_oauth_show_on_login_page" value ="1" <?php if(isset($currentapp['show_on_login_page'])) { if($currentapp['show_on_login_page'] === 1 ) echo 'checked'; } ; ?>/>Show on login page</td>
			</tr>
				<td>&nbsp;</td>
				<td>
					<input type="submit" name="submit" value="Save settings" class="button button-primary button-large" />
					<!-- <?php if($is_other_app){?> -->
						<input id="mo_oauth_test_configuration" type="button" name="button" value="Test Configuration" class="button button-primary button-large" onclick="testConfiguration()" />
					<!-- <?php } ?> -->
				</td>
			</tr>
			<tr>
				<td><strong>Note:</strong></td>
				<td colspan="2">
					<b>Please configure <a id="mo_oauth_attr_map" href='<?php echo admin_url( "admin.php?page=mo_oauth_settings&tab=attributemapping" ); ?>'>Attribute Mapping</a> before trying Single Sign-On.</b>
				</td>
			</tr>
		</table>
		</form>
		</div>
		</div>
		<?php if($is_other_app){ ?>
		<script>
		function proceedToAttributeMapping() {
			var link = jQuery("#mo_oauth_attr_map").attr("href");
			window.location.href = link;
		}

		function testConfiguration(){
			var mo_oauth_app_name = jQuery("#mo_oauth_app_nameid").val();
			var myWindow = window.open('<?php echo site_url(); ?>' + '/?option=testattrmappingconfig&app='+mo_oauth_app_name, "Test Attribute Configuration", "width=600, height=600");
			try {
				while(1) {
					if(myWindow.closed()) {
						$(document).trigger("config_tested");
						break;
					} else {continue;}
				}
			} catch(err) {
				console.error(err);
			}
		}

		function showClientSecret(){
			var field = document.getElementById("mo_oauth_client_secret");
			var show_button = document.getElementById("show_button");
			if(field.type == "password"){
				field.type = "text";
				show_button.className = "fa fa-eye-slash";
			}
			else{
				field.type = "password";
				show_button.className = "fa fa-eye";
			}
		}
		</script>
		<?php }
		mo_oauth_client_grant_type_settings();
}
