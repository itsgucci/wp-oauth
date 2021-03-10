<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) 
    exit();

delete_option('host_name');
delete_option('mo_oauth_admin_email');
delete_option('mo_oauth_client_admin_phone');
delete_option('mo_oauth_client_verify_customer');
delete_option('mo_oauth_client_admin_customer_key');
delete_option('mo_oauth_client_admin_api_key');
delete_option('mo_oauth_client_customer_token');
delete_option('mo_oauth_client_new_customer');
delete_option('message');
delete_option('mo_oauth_client_new_registration');
delete_option('mo_oauth_client_registration_status');
delete_option('mo_oauth_client_show_mo_server_message');
delete_option('mo_existing_app_flow');

?>