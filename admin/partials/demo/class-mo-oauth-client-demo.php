<?php
	
	class Mo_OAuth_Client_Admin_RFD {
	
		public static function requestfordemo() {
			self::demo_request();
		}

		public static function demo_request(){
			$democss = "width: 350px; height:35px;";
		?>
			<div class="mo_table_layout">
			    <h3> Demo Request Form : </h3>
			    <!-- <div class="mo_table_layout mo_modal-demo"> -->
			    	<form method="post" action="">
					<input type="hidden" name="option" value="mo_oauth_client_demo_request_form" />
					<?php wp_nonce_field('mo_oauth_client_demo_request_form', 'mo_oauth_client_demo_request_field'); ?>
			    	<table cellpadding="4" cellspacing="4">
                        <tr>
						  	<td><strong>Usecase : </strong></td>
							<td>
							<textarea type="text" minlength="15" name="mo_auto_create_demosite_usecase" style="resize: vertical; width:350px; height:100px;" rows="4" placeholder="Write us about your usecase" required value=""></textarea>
							</td>
						  </tr> 	
                        <tr>
							<td></td>
							<td style="width:350px;"><p style="color:grey;font-size:14px">Example. Login into wordpress using Cognito,<br> SSO into wordpress with my company credentials,<br> Restrict gmail.com accounts to my wordpress site etc.</p></td>
						</tr>
			    		<tr>
							<td><strong>Email id : </strong></td>
							<td><input required type="email" style="<?php echo $democss; ?>" name="mo_auto_create_demosite_email" placeholder="Email id" value="<?php echo get_option("mo_oauth_admin_email"); ?>" /></td>
						</tr>
						<tr>
							<td><strong>Request a demo for : </strong></td>
							<td>
								<select required style="<?php echo $democss; ?>" name="mo_auto_create_demosite_demo_plan" id="mo_oauth_client_demo_plan_id">
									<option disabled selected>------------------ Select ------------------</option>
									<option value="miniorange-oauth-client-standard-common@11.6.1">WP <?php echo MO_OAUTH_PLUGIN_NAME; ?> Standard Plugin</option>
									<option value="mo-oauth-client-premium@21.5.3">WP <?php echo MO_OAUTH_PLUGIN_NAME; ?> Premium Plugin</option>
									<option value="miniorange-oauth-client-enterprise@31.5.7">WP <?php echo MO_OAUTH_PLUGIN_NAME; ?> Enterprise Plugin</option>
									<option value="miniorange-oauth-client-allinclusive@48.3.0">WP <?php echo MO_OAUTH_PLUGIN_NAME; ?> All Inclusive Plugin</option>
									<option value="Not Sure">Not Sure</option>
								</select>
							</td>
					  	</tr>
<!--
					  	<tr id="demoDescription" style="display:none;">
						  	<td><strong>Description : </strong></td>
							<td>
							<textarea type="text" name="mo_oauth_client_demo_description" style="resize: vertical; width:350px; height:100px;" rows="4" placeholder="Need assistance? Write us about your requirement and we will suggest the relevant plan for you." value="<?php isset($mo_oauth_client_demo_email); ?>" /></textarea>
							</td>
					  	</tr>
-->
                        <tr>
                            <td></td>
                            <td>
                                <input type="submit" name="submit" value="Submit Demo Request" class="button button-primary button-large" />
                            </td>
                        </tr>
			    	</table>
			    <!-- </div> -->
			</form>
			</div>
<!--
			<script type="text/javascript">
				function moOauthClientAddDescriptionjs() {
					// alert("working");
				var x = document.getElementById("mo_oauth_client_demo_plan_id").selectedIndex;
				var otherOption = document.getElementById("mo_oauth_client_demo_plan_id").options;
				if (otherOption[x].index == 4){
				    demoDescription.style.display = "";
				} else {
				    demoDescription.style.display = "none";
				}
			}
			</script>
-->
		<?php
		}
	}
