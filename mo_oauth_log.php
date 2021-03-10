<?php

class MO_Oauth_Debug{

	public static function mo_oauth_log($mo_message)
	{
		$mo_pluginlog=plugin_dir_path(__FILE__).get_option('mo_oauth_debug').'.log';
		$mo_time = time();
		$mo_log='['.date("Y-m-d H:i:s", $mo_time).' UTC] : '.$mo_message.PHP_EOL;
		error_log($mo_log, 3,$mo_pluginlog);
	}

}