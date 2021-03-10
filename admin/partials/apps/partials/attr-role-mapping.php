<?php


function mo_oauth_client_attribite_role_mapping_ui(){
	$appslist = get_option('mo_oauth_apps_list');
	$attr_name_list = get_option('mo_oauth_attr_name_list');
	
	if ( false !== $attr_name_list ) {
		$temp = [];
		$attr_name_list = mo_oauth_client_dropdownattrmapping('', $attr_name_list, $temp );
	}
	$currentapp = null;
	$currentappname = null;
	if ( is_array( $appslist ) ) {
		foreach( $appslist as $key => $value ) {
			$currentapp = $value;
			$currentappname = $key;
			break;
		}
	}
	//Attribute mapping
	?>
	<div class="mo_table_layout" id="attribute-mapping">
		<form id="form-common" name="form-common" method="post" action="admin.php?page=mo_oauth_settings&tab=attributemapping">
			<?php wp_nonce_field('mo_oauth_attr_role_mapping_form','mo_oauth_attr_role_mapping_form_field'); ?>
		<h3>Attribute Mapping <small>[required for SSO & ACCOUNT LINKING </small>]</h3> 
		<p style="font-size:13px;color:#dc2424">Do <b>Test Configuration</b> to get configuration for attribute mapping.<br></p>
		<input type="hidden" name="option" value="mo_oauth_attribute_mapping" />
		<input class="mo_table_textbox" required="" type="hidden" id="mo_oauth_app_name" name="mo_oauth_app_name" value="<?php echo $currentappname;?>">
		<input class="mo_table_textbox" required="" type="hidden" name="mo_oauth_custom_app_name" value="<?php echo $currentappname;?>">
		<table class="mo_settings_table">
			<tr id="mo_oauth_email_attr_div">
				<td><strong><font color="#FF0000">*</font>Username:</strong></td>
				<td>
					<?php
					if( is_array( $attr_name_list ) ) {  ?>
						<select class="mo_table_textbox" <?php if (get_option('mo_attr_option') === "manual" ) echo 'style="display:none"'; ?> id="mo_oauth_username_attr_select" <?php if ( get_option('mo_attr_option') === false || get_option('mo_attr_option') === "automatic" ) echo 'name="mo_oauth_username_attr"'; ?> >
						<option value="">----------- Select an Attribute -----------</option>
							<?php 
							foreach ( $attr_name_list as $key => $value ) {
								echo "<option value='".$value."'";
								if( ( isset( $currentapp['username_attr'] ) && $currentapp['username_attr'] === $value ) ||  ( isset( $currentapp['email_attr'] ) && $currentapp['email_attr'] === $value ) ) {
									echo " selected";
								}
								else echo "";
								echo " >".$value."</option>";
							}
							?>
						</select>
						<script>
						function changeFormField(){
							var select_box = document.getElementById('mo_oauth_username_attr_select');
							var input_tag = document.getElementById('mo_oauth_username_attr_input');
							if (select_box.style.display != "none") {
								select_box.name = "";
								select_box.style.display = "none";
								input_tag.name = "mo_oauth_username_attr";
								input_tag.style.display = "block";
								document.getElementById('mo_username_attr_change_p').innerHTML = "Change to automatic mode";
								document.getElementById('mo_attr_option').value = "manual";
							} else {
								select_box.name = "mo_oauth_username_attr";
								select_box.style.display = "block";
								input_tag.name = "";
								input_tag.style.display = "none";
								document.getElementById('mo_username_attr_change_p').innerHTML = "Change to manual mode";
								document.getElementById('mo_attr_option').value = "automatic";
							}
						}
						</script>
						<input type="hidden" id="mo_attr_option" name="mo_attr_option" value="<?php if(get_option('mo_attr_option')){ echo get_option('mo_attr_option'); } else { echo "automatic"; } ?>">
						<input <?php if (get_option('mo_attr_option') === "manual" ) echo 'name="mo_oauth_username_attr"'; ?> class="mo_table_textbox" <?php if (get_option('mo_attr_option') === "automatic" || get_option('mo_attr_option') === false ) echo 'style="display:none"'; ?> placeholder="Enter attribute name for Username" type="text" id="mo_oauth_username_attr_input" value="<?php if( isset( $currentapp['username_attr'] ) )echo $currentapp['username_attr']; else if( isset( $currentapp['email_attr'] ) )echo $currentapp['email_attr'];?>">
						</td>
						<?php $text_attr = get_option('mo_attr_option') ? get_option('mo_attr_option') === "manual" ? "Change to automatic mode" : "Change to manual mode" : "Change to manual mode"; ?>
						<td>
							<a href="#" id="mo_username_attr_change_p" onclick="changeFormField()"><?php echo $text_attr; ?></a>
						</td>
						<?php
					} else { ?>
						<input class="mo_table_textbox" required="" placeholder="Enter attribute name for Username" type="text" id="mo_oauth_username_attr_input" name="mo_oauth_username_attr" value="<?php if( isset( $currentapp['username_attr'] ) )echo $currentapp['username_attr']; else if( isset( $currentapp['email_attr'] ) )echo $currentapp['email_attr'];?>">
						</td>
						<td>
						</td>
						<?php
					}
					?>
			</tr>
			
			
			
		<?php
		echo '<tr>
			<td></td><td>
            <b><p style="margin-left:2px" class=" mop_table">
			Advanced attribute mapping is available in <a href="admin.php?page=mo_oauth_settings&amp;tab=licensing">premium</a> version.</b><br>
			<a href="https://developers.miniorange.com/docs/oauth/wordpress/client/attribute-role-mapping" target="_blank">[How to map Attributes?]</a>
            </p>
			</td>
		</tr>
        <tr id="mo_oauth_name_attr_div">
				<td><strong>First Name:</strong></td>
				<td><input class="mo_table_textbox" required="" placeholder="Enter attribute name for First Name" disabled  type="text" value=""></td>
			</tr>
		<tr>
			<td><strong>Last Name:</strong></td>
			<td>
				<input type="text" class="mo_table_textbox" placeholder="Enter attribute name for Last Name"  disabled /></td>
		</tr>
		<tr>
			<td><strong>Email:</strong></td>
			<td><input type="text" class="mo_table_textbox" placeholder="Enter attribute name for Email"  value="" disabled /></td>
		</tr>
		<tr>
			<td><strong>Group/Role:</strong></td>
			<td><input type="text" class="mo_table_textbox" placeholder="Enter attribute name for Group/Role" value="" disabled /></td>
		</tr>
		<tr>
			<td><strong>Display Name:</strong></td>
			<td>
				<select disabled  class="mo_table_textbox" style="background-color: #eee;">
					<option>Username</option>
				</select>
			</td></tr>
			<tr><td>&nbsp;</td></tr>
			<tr><td colspan="3"><hr></td></tr>
			<tr>
				<td colspan="2">
					<strong>
						<input disabled type="checkbox"> Keep Existing User Attributes
					</strong><small> <a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer">[PREMIUM]</a></small>
				</td>
			</tr>
			<tr>
				<td colspan="2"><p style="color:grey;margin-left:10px;font-size:14px"><small>If unchecked, each time existing WordPress User profile will get updated with above mapping. <br/> <b>Note :</b> User profile will get updated based on existing Usernames.</small></p>
				</td>
			</tr>
			<tr>
			  	<td colspan="2">
				    <table>
						<tr>
						  	<td>
						  		<strong><input disabled type="checkbox"> Keep Existing Email Attribute</strong><small> <a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer">[PREMIUM]</a></small>
						  	</td>
						</tr>
						<tr><td colspan="2"><p style="color:grey;margin-left:10px;font-size:14px"><small>Uncheck only if you want existing user email to get updated each time after SSO. </small></p></td></tr>
				    </table>
				</td>
			</tr>
			<tr><td colspan="3"><hr></td></tr>
			<tr><td colspan="2">
			<h3>Map Custom Attributes <a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer" style="font-size: x-small">[PREMIUM]</a></h3>
			<span style="font-size:small;">[ <a href="https://developers.miniorange.com/docs/oauth/wordpress/client/custom-attribute-mapping" target="_blank">How to map Custom Attributes?</a> ]</span>
            <p>Map extra OAuth Provider attributes which you wish to be included in the user profile below</p>
			</td><td><input disabled type="button" value="+" class="button button-primary"  /></td>
			<td><input disabled type="button" value="-" class="button button-primary"   /></td></tr>
			<tr class="rows"><td><input disabled type="text" placeholder="Enter field meta name" /></td>
			<td><input disabled type="text" placeholder="Enter attribute name from OAuth Provider"  class="mo_table_textbox" /></td>
			</tr>';
			?>
			</table>
			<br>
			<input type="submit" name="submit" value="Save settings"
					class="button button-primary button-large" />
		</form>
		</div>

		<div class="mo_table_layout" id="role-mapping">
		<h3>Role Mapping <a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer" style="font-size: x-small;">[PREMIUM]</a></small></h3>
		<span style="font-size:small;">[ <a href="https://faq.miniorange.com/knowledgebase/map-roles-usergroup/" target="_blank">How to map Roles?</a> ]</span><br><br>
		<b>NOTE: </b>Role will be assigned only to non-admin users (user that do NOT have Administrator privileges). You will have to manually change the role of Administrator users.<br>
		<form id="role_mapping_form" name="f" method="post" action="">
		<input disabled class="mo_table_textbox" required="" type="hidden"  name="mo_oauth_app_name" value="<?php echo $currentappname;?>">
		<input disabled  type="hidden" name="option" value="mo_oauth_client_save_role_mapping" />
		
		<p><input disabled type="checkbox"/><strong> Keep existing user roles</strong><br><small>Role mapping won't apply to existing wordpress users.</small></p>
		<p><input disabled type="checkbox" > <strong> Do Not allow login if roles are not mapped here </strong></p><small>We won't allow users to login if we don't find users role/group mapped below.</small></p>

		<div id="panel1">
			<table class="mo_oauth_client_mapping_table" id="mo_oauth_client_role_mapping_table" style="width:90%">
					<tr><td>&nbsp;</td></tr>
					<tr>
					<td><font style="font-size:13px;font-weight:bold;">Default Role </font>
					</td>
					<td>
						<select disabled style="width:100%">
						   <option>Subscriber</option>
						</select>
						
					</td>
				</tr>
				<tr>
					<td colspan=2><i> Default role will be assigned to all users for which mapping is not specified.</i></td>
				</tr>
				<tr><td>&nbsp;</td></tr>
				<tr>
					<td style="width:50%"><b>Group Attribute Value</b></td>
					<td style="width:50%"><b>WordPress Role</b></td>
				</tr>
				
				<tr>
					<td><input disabled class="mo_oauth_client_table_textbox" type="text" placeholder="group name" />
					</td>
					<td>
						<select disabled style="width:100%"  >
							<option>Subscriber</option>
						</select>
					</td>
				</tr>
				</table>
				<table class="mo_oauth_client_mapping_table" style="width:90%;">
					<tr><td><a style="cursor:pointer">Add More Mapping</a><br><br></td><td>&nbsp;</td></tr>
					<tr>
						<td><input disabled type="submit" class="button button-primary button-large" value="Save Mapping" /></td>
						<td>&nbsp;</td>
					</tr>
				</table>
				</div>
			</form>
		</div>
<?php
}

function mo_oauth_client_dropdownattrmapping( $nestedprefix, $resource_owner_details, $temp ) {
	foreach ( $resource_owner_details as $key => $resource ) {
		if ( is_array( $resource ) ) {
			if ( ! empty( $nestedprefix ) ) {
				$nestedprefix .= '.';
			}
			$temp = mo_oauth_client_dropdownattrmapping( $nestedprefix . $key, $resource, $temp );
			$nestedprefix = rtrim($nestedprefix,".");
		} else {
			if ( ! empty( $nestedprefix ) ) {
				array_push($temp, $nestedprefix.".".$key);
			}
			else{
				array_push($temp, $key);
			}
		}
	}
	return $temp;
}
