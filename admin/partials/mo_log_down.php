<?php
	
	include('../../../../../wp-config.php');

	$mo_filepath=getcwd().'/../../'.get_option('mo_oauth_debug').'.log';

	if (!is_file($mo_filepath)) 
		{
			 echo("404 File not found!"); // file not found to download
			 exit();
	    }
		
		   
		$mo_len = filesize($mo_filepath); // get size of file
		$mo_filename = basename($mo_filepath); // get name of file only
		$mo_file_extension = strtolower(pathinfo($mo_filename,PATHINFO_EXTENSION));
		//Set the Content-Type to the appropriate setting for the file
		$mo_ctype="application/force-download";
		ob_clean();
		//Begin writing headers
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public"); 
		header("Content-Description: File Transfer");
		header("Content-Type: $mo_ctype");
		//Force the download
		$mo_header="Content-Disposition: attachment; filename=".$mo_filename.";";
		header($mo_header );
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$mo_len);
		@readfile($mo_filepath);
		exit;