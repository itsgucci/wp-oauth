<?php

function mo_oauth_client_show_default_apps() { ?>
	<input type="text" id="mo_oauth_client_default_apps_input" onkeyup="mo_oauth_client_default_apps_input_filter()" placeholder="Select application" title="Type in a Application Name">

	<h3>OAuth / OpenID Connect Providers</h3>
	<hr />
	<h4>Pre-Configured Applications&emsp;<div class="mo-oauth-tooltip">&#x1F6C8;<div class="mo-oauth-tooltip-text mo-tt-right">By selecting pre-configured applications, the configuration would already be half-done!</div> </div></h4>
	<ul id="mo_oauth_client_default_apps">
		<div id="mo_oauth_client_searchable_apps">
		<?php
			$defaultapps =  file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR .'defaultapps.json');
			$defaultappsjson =json_decode($defaultapps);
			$custom_apps = [];
			foreach($defaultappsjson as $appId => $application) {
				if ( 'other' === $appId || 'openidconnect' === $appId ) {
					$custom_apps[ $appId ] = $application;
					continue;
				}
				echo '<li data-appid="'.$appId.'"><a ' . ( 'cognito' === $appId ? 'id=vip-default-app' : '' ) . ' href="#"><img class="mo_oauth_client_default_app_icon" src="'. plugins_url( '../images/'.$application->image, __FILE__ ).'"><br>'.$application->label.'</a></li>';
			}
		?>
		</div>
		<div id="mo_oauth_client_search_res"></div>
		<hr>
		<h4>Custom Applications&emsp;<div class="mo-oauth-tooltip">&#x1F6C8;<div class="mo-oauth-tooltip-text mo-tt-right">Your provider is not in the list? You can select the type of your provider and configure it yourself!</div> </div></h4>
		<div id="mo_oauth_client_custom_apps">
			<?php
				foreach( $custom_apps as $appId => $application ) {
					echo '<li data-appid="'.$appId.'"><a href="#"><img class="mo_oauth_client_default_app_icon" src="'. plugins_url( '../images/'.$application->image, __FILE__ ).'"><br>'.$application->label.'</a></li>';
				}
			?>
		</div>
	</ul>
	<script>

		jQuery("#mo_oauth_client_default_apps li").click(function(){
			var appId = jQuery(this).data("appid");
				window.location.href += "&appId="+appId;
		});

	</script>

<?php }


function mo_oauth_client_get_app($currentAppId) {
	$defaultapps =  file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR .'defaultapps.json');
	$defaultappsjson =json_decode($defaultapps);
	foreach($defaultappsjson as $appId => $application) {
		if($appId == $currentAppId) {
			$application->appId = $appId;
			return $application;
		}
	}
	return false;
}



?>