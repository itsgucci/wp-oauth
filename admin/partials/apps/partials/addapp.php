<?php

	require('defaultapps.php');
	require('grant-settings.php');

	function mo_oauth_client_add_app_page(){
		$appslist = get_option('mo_oauth_apps_list');
		if(is_array($appslist) && sizeof($appslist)>0) {
			echo "<p style='color:#a94442;background-color:#f2dede;border-color:#ebccd1;border-radius:5px;padding:12px'>You can only add 1 application with free version. Upgrade to <a href='admin.php?page=mo_oauth_settings&tab=licensing'><b>enterprise</b></a> to add more.</p>";
			exit;
		}
	?>
	<div id="toggle2" class="mo_panel_toggle">
		<table class="mo_settings_table">
			<tr>
				<td><h3>Add Application</h3></td>
				<?php
					if(isset($_GET['appId'])) {
						$currentAppId = $_GET['appId'];
						if(isset($_GET['action']) && ($_GET['action'] == 'instructions')) {
							echo "
							<td align=\"right\"><a href=\"admin.php?page=mo_oauth_settings&tab=config&appId=".$currentAppId."\"><div id='mo_oauth_config_guide' style=\"display:inline;background-color:#0085ba;color:#fff;padding:4px 8px;border-radius:4px\">Hide instructions ^</div></a></td> ";
						} else {
							echo "
							<td align=\"right\"><a href=\"admin.php?page=mo_oauth_settings&tab=config&action=instructions&appId=".$currentAppId."\"><div id='mo_oauth_config_guide' style=\"display:inline;background-color:#0085ba;color:#fff;padding:4px 8px;border-radius:4px\">How to Configure?</div></a></td>
							";
						}
					} else { ?>
						<td align="right"><span style="position: relative; float: right;padding-left: 13px;padding-right:13px;background-color:white;border-radius:4px;">
							&nbsp;
						</span></td> <?php
					}

				?>
			</tr>
		</table>
		<form name="f" method="post" id="show_pointers">
			<?php wp_nonce_field('mo_oauth_clear_pointers_form','mo_oauth_clear_pointers_form_field'); ?>
        	<input type="hidden" name="option" value="clear_pointers"/>
		</form>
	</div>

	<?php
		// Select from default apps
		if(!isset($_GET['appId'])) {
			mo_oauth_client_show_default_apps();
		} else {

			$currentAppId = $_GET['appId'];
			$currentapp = mo_oauth_client_get_app($currentAppId);
            $refAppId = array("other", "openidconnect");
            $tempappname = !in_array($currentapp->appId, $refAppId) ? $currentapp->appId : "customApp";

            ?>
		<div id="mo_oauth_add_app">
		<form id="form-common" name="form-common" method="post" action="admin.php?page=mo_oauth_settings&tab=config&action=update&app=<?php echo $tempappname;?>" >
		<?php wp_nonce_field('mo_oauth_add_app_form','mo_oauth_add_app_form_field'); ?>
		<input type="hidden" name="option" value="mo_oauth_add_app" />
		<table class="mo_settings_table">
			<tr>
			<td><strong><font color="#FF0000">*</font>Application:<br><br></strong></td>
			<td>
				<input type="hidden" name="mo_oauth_app_name" value="<?php echo $currentAppId;?>">
				<input type="hidden" name="mo_oauth_app_type" value="<?php echo $currentapp->type;?>">
				<?php echo $currentapp->label;?> &nbsp;&nbsp;&nbsp;&nbsp; <a style="text-decoration:none" href ="admin.php?page=mo_oauth_settings"><div style="display:inline;background-color:#0085ba;color:#fff;padding:4px 8px;border-radius:4px">Change Application</div></a><br><br>
			</td>
			</tr>
			<tr><td><strong>Redirect / Callback URL: </strong><br>&emsp;<font><small>Editable in <a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer">[STANDARD]</a></small></font></td>
			<td><input class="mo_table_textbox" id="callbackurl"  type="text" readonly="true" name="mo_oauth_callback_url" value='<?php echo site_url()."";?>'>
			&nbsp;&nbsp;
			<div class="tooltip" style="display: inline;"><span class="tooltiptext" id="moTooltip">Copy to clipboard</span><i class="fa fa-clipboard fa-border" style="font-size:20px; align-items: center;vertical-align: middle;" aria-hidden="true" onclick="copyUrl()" onmouseout="outFunc()"></i></div>
			</td>
			</tr>
			
			<tr id="mo_oauth_custom_app_name_div">
				<td><strong><font color="#FF0000">*</font>App Name (<?php echo $currentapp->type;?>):</strong></td>
				<td><input class="mo_table_textbox" onkeyup="updateFormAction()" type="text" id="mo_oauth_custom_app_name" name="mo_oauth_custom_app_name" value="<?php echo $tempappname; ?>" pattern="^[a-zA-Z0-9]+( [a-zA-Z0-9\s]+)*$" required title="Please do not add any special characters." placeholder="Do not add any special characters" maxlength="14"></td>
			</tr>
<!--			<tr id="mo_oauth_display_app_name_div">-->
<!--				<td><strong>Display App Name:</strong><br>&emsp;<font color="#FF0000"><small><a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer">[STANDARD]</a></small></font></td>-->
<!--				<td><input class="mo_table_textbox" type="text" id="mo_oauth_display_app_name" name="mo_oauth_display_app_name" value="Login with <App Name>" pattern="[a-zA-Z0-9\s]+" disabled title="Please do not add any special characters."></td>-->
<!--			</tr>-->
		</table>
		
		<table class="mo_settings_table" id="mo_oauth_client_creds">
			<tr>
				<td><strong><font color="#FF0000">*</font>Client ID:</strong></td>
				<td><input id="mo_oauth_client_id" class="mo_table_textbox" required="" type="text" name="mo_oauth_client_id" value=""></td>
			</tr>
			<tr>
				<td><strong><font color="#FF0000">*</font>Client Secret:</strong></td>
				<td>
					<input id="mo_oauth_client_secret" class="mo_table_textbox" required="" type="password"  name="mo_oauth_client_secret" value="">
					
					<i class="fa fa-eye" onclick="showClientSecret()" id="show_button" style="margin-left:-30px; cursor:pointer;"></i>
				</td>

			</tr>
		</table>
            <?php if(isset($currentapp->discovery) && $currentapp->discovery !="") {?>
		<table class="mo_settings_table">
                <tr>
                    <td><input type="hidden" id="mo_oauth_discovery" name="mo_oauth_discovery" value="<?php if(isset($currentapp->discovery)) echo $currentapp->discovery;?>"></td>
                </tr>
           <?php if(isset($currentapp->domain)) { ?>
            <tr>
                <td><strong><font color="#FF0000">*</font><?php echo $currentapp->label; ?> Domain:</strong></td>
                <td><input class="mo_table_textbox" <?php if(isset($currentapp->domain)) echo 'required';?> type="text" id="mo_oauth_provider_domain" name="mo_oauth_provider_domain" placeholder= "<?php if(isset($currentapp->domain)) echo "Ex. ". $currentapp->domain;?>" value=""></td>
            </tr>

                <?php } elseif(isset($currentapp->tenant)) { ?>
                    <tr>
                        <td><strong><font color="#FF0000">*</font><?php echo $currentapp->label; ?> Tenant:</strong></td>
                        <td><input class="mo_table_textbox" <?php if(isset($currentapp->tenant)) echo 'required';?> type="text" id="mo_oauth_provider_tenant" name="mo_oauth_provider_tenant" placeholder= "<?php if(isset($currentapp->tenant)); echo $currentapp->tenant; ?>" value=""></td>
                    </tr>
            <?php } if(isset($currentapp->policy)) { ?>
            <tr>
                <td><strong><font color="#FF0000">*</font><?php echo $currentapp->label; ?> Policy:</strong></td>
                <td><input class="mo_table_textbox" <?php if(isset($currentapp->policy)) echo 'required';?> type="text" id="mo_oauth_provider_policy" name="mo_oauth_provider_policy" placeholder= "<?php if(isset($currentapp->policy)) echo "Ex. ". $currentapp->policy;?>" value=""></td>
            </tr>
            <?php } elseif(isset($currentapp->realmname)) { ?>
                    <tr>
                        <td><strong><font color="#FF0000">*</font><?php echo $currentapp->label; ?> Realm:</strong></td>
                        <td><input class="mo_table_textbox" <?php if(isset($currentapp->realmname)) echo 'required';?> type="text" id="mo_oauth_provider_realm" name="mo_oauth_provider_realm" placeholder= "Add a name of a realm" value=""></td>
                    </tr>
		</table>
                <?php }
            } ?>
		<table class="mo_settings_table" id="mo_oauth_client_endpoints">
            <?php if(!isset($currentapp->discovery) || $currentapp->discovery =="") {?>
                <tr>
                    <td><strong>Scope:</strong></td>
                    <td><input class="mo_table_textbox" type="text" name="mo_oauth_scope" value="<?php if(isset($currentapp->scope)) echo $currentapp->scope;?>"></td>
                </tr>
                <?php if($currentapp->label !='twitter'){?>
			<tr id="mo_oauth_authorizeurl_div">
				<td><strong><font color="#FF0000">*</font>Authorize Endpoint:</strong></td>
				<td><input class="mo_table_textbox" <?php if(!isset($currentapp->discovery) || $currentapp->discovery=="") echo 'required';?> type="text" id="mo_oauth_authorizeurl" name="mo_oauth_authorizeurl" value="<?php if(isset($currentapp->authorize)) echo htmlentities($currentapp->authorize);?>"></td>
			</tr>
			<tr id="mo_oauth_accesstokenurl_div">
				<td><strong><font color="#FF0000">*</font>Access Token Endpoint:</strong></td>
				<td><input class="mo_table_textbox" <?php if(!isset($currentapp->discovery) || $currentapp->discovery=="") echo 'required';?> type="text" id="mo_oauth_accesstokenurl" name="mo_oauth_accesstokenurl" value="<?php if(isset($currentapp->token)) echo $currentapp->token;?>"></td>
			</tr>
			<?php if(!isset($currentapp->type) || $currentapp->type=='oauth') {?>
				<tr id="mo_oauth_resourceownerdetailsurl_div">
					<td><strong><font color="#FF0000">*</font>Get User Info Endpoint:</strong></td>
					<td><input class="mo_table_textbox" <?php if(!isset($currentapp->type) || $currentapp->type=='oauth' || !isset($currentapp->discovery) || $currentapp->discovery=="" ) echo 'required';?> type="text" id="mo_oauth_resourceownerdetailsurl" name="mo_oauth_resourceownerdetailsurl" value="<?php if(isset($currentapp->userinfo)) echo $currentapp->userinfo;?>"></td>
				</tr>
			<?php } ?>
            <?php } ?>
        	<?php } ?>
            <tr>
                <td><strong>Send client credentials in:</strong></td>
                <td><div style="padding:5px;"></div><input type="checkbox" name="mo_oauth_authorization_header" value ="1" checked /> Header<span style="padding:0px 0px 0px 8px;"></span><input type="checkbox" name="mo_oauth_body" value ="0"/> Body<div style="padding:5px;"></div></td>
            </tr>

        	<tr>
				<td><strong>State Parameter :</strong></td>
				<td><div style="padding:5px;"></div><input type="checkbox" name="mo_oauth_state" value ="1" <?php if(isset($currentapp->send_state)) { if($currentapp->send_state === 1 ){ echo 'checked';}else{$currentapp->send_state=1;echo 'checked';} } ; ?> checked/>Send state parameter</td>
				<td><br></td>
			</tr>
			<tr>
				<td><strong>login button:</strong></td>
				<td><div style="padding:5px;"></div><input type="checkbox" name="mo_oauth_show_on_login_page" value ="1" checked/>Show on login page</td>
			</tr>
			<tr>
				<td><br></td>
				<td><br></td>
			</tr>
			</table>
			<table class="mo_settings_table">
				<tr>
					<td>&nbsp;</td>
					<td><input id="mo_save_app" type="submit" name="submit_save_app" value="Save settings"
						class="button button-primary button-large" /></td>
				</tr>
			</table>
		</form>

		<div id="instructions">

		</div>
		</div>

		
		<?php
		mo_oauth_client_grant_type_settings();
	}


}
