<?php

function mo_oauth_client_grant_type_settings() {
	?>
	</div>
	<div class="mo_table_layout" id="mo_grant_settings" style="position: relative;">
		<table class="mo_settings_table">
			<tr>
				<td style="padding: 15px 0px 5px;"><h3 style="display: inline;">Grant Settings&emsp;<code><small><a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer">[PREMIUM]</a></small></code></h3><span style="float: right;">[ <a href="https://developers.miniorange.com/docs/oauth/wordpress/client/multiple-grant-support" target="_blank">Click here</a> to know how this is useful. ]</span></td>
				<!-- <td align="right"><a href="#" target="_blank" id='mo_oauth_grant_guide' style="display:inline;background-color:#0085ba;color:#fff;padding:4px 8px;border-radius:4px;">What is this?</a></td> -->
			</tr>
		</table>
		<div class="grant_types">
			<h4>Select Grant Type:</h4>
			<input checked disabled type="checkbox">&emsp;<strong>Authorization Code Grant</strong>&emsp;<code><small>[DEFAULT]</small></code>
			<blockquote>
				The Authorization Code grant type is used by web and mobile apps.<br/>
				It requires the client to exchange authorization code with access token from the server.
				<br/><small>(If you have doubt on which settings to use, you can leave this checked and disable all others.)</small>
			</blockquote>
			<input disabled type="checkbox">&emsp;<strong>Implicit Grant</strong>
			<blockquote>
				The Implicit grant type is a simplified version of the Authorization Code Grant flow.<br/>
				OAuth providers directly offer access token when using this grant type.
			</blockquote>
			<input disabled type="checkbox">&emsp;<strong>Password Grant</strong>
			<blockquote>
				Password grant is used by application to exchange user's credentials for access token.<br/>
				This, generally, should be used by internal applications.
			</blockquote>
			<input disabled type="checkbox">&emsp;<strong>Refresh Token Grant</strong>
			<blockquote>
				The Refresh Token grant type is used by clients.<br/>
				This can help in keeping user session persistent.
			</blockquote>
		</div>
		<hr>
		<div style="padding:15px 0px 15px;"><h3 style="display: inline;">JWT Validation & PKCE&emsp;</h3><span style="float: right;">[
		<a href="https://developers.miniorange.com/docs/oauth/wordpress/client/json-web-token-support" target="_blank">Click here</a> to know how this is useful. ]</span></div>
				<div>
					<table class="mo_settings_table">
						<tr>
							<td><strong>Enable JWT Verification:</strong></td>
							<td><input type="checkbox" value="" disabled/></td>
						</tr>
						<tr>
							<td><strong>JWT Signing Algorithm:</strong></td>
							<td><select disabled>
									<option>HSA</option>
									<option>RSA</option>
								</select>
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td><strong>PKCE (Proof Key for Code Exchange):</strong></td>
							<td><input id="pkce_flow" type="checkbox" name="pkce_flow" value="0" disabled/></td>
						</tr>
					</table>
					<p style="font-size:12px"><strong>*Note: </strong>PKCE can be used with Authorization Code Grant and users aren't required to provide a client_secret.</p>
				</div>
			<br><br>
		<div class="notes">
			<hr />
			Grant Type Settings and JWT Validation & PKCE are configurable in <a href="admin.php?page=mo_oauth_settings&tab=licensing" target="_blank" rel="noopener noreferrer">premium and enterprise</a> versions of the plugin.
		</div>
	</div>
	<div>
	<?php
}