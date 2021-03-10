<?php
$pointers = array();

if (isset($_GET['action']) && $_GET['action'] === 'update') {
    $pointers['miniorange-configure-test-config-pointer'] = array(
        'title'     => sprintf( '<h3>%s</h3>', esc_html__( 'Test Your Application Configuration' ) ),
        'content'   => sprintf( '<p>%s</p>', esc_html__( 'Click here to see the list of attributes provided by your OAuth Provider. If you are getting any error, please refer the FAQ tab.' ) ),
        'anchor_id' => '#mo_oauth_test_configuration',
        'edge'      => 'left',
        'align'     => 'right',
        'where'     => array( 'toplevel_page_mo_oauth_settings' ) // <-- Please note this
    );
    
}
else if(isset($_GET['appId'])) {
        $pointers['miniorange-configure-addapp-pointer'] = array(
        'title'     => sprintf( '<h3>%s</h3>', esc_html__( 'Configure Your Application' ) ),
        'content'   => sprintf( '<p>%s</p>', esc_html__( 'Need help with configuration? Click on How to Configure?' ) ),
        'anchor_id' => '#mo_oauth_config_guide',
        'edge'      => 'left',
        'align'     => 'right',
        'where'     => array( 'toplevel_page_mo_oauth_settings' ) // <-- Please note this
    );
}

if(isset($_GET['tab']) && $_GET['tab'] === 'config') {
    $pointers['miniorange-support-pointer'] = array(
        'title'     => sprintf( '<h3>%s</h3>', esc_html__( 'We are here!!' ) ),
        'content'   => sprintf( '<p>%s</p>', esc_html__( 'Get in touch with us and we will help you setup the plugin in no time.' ) ),
        'anchor_id' => '#mo_support_layout',
        'edge'      => 'right',
        'align'     => 'left',
        'where'     => array( 'toplevel_page_mo_oauth_settings' ) // <-- Please note this
    );

    if(!isset($_GET['appId']) && !isset($_GET['app'])) {
        if(is_array(get_option('mo_oauth_apps_list')) && sizeof(get_option('mo_oauth_apps_list'))>0) {
            $pointers['miniorange-app-list-pointer'] = array(
                'title'     => sprintf( '<h3>%s</h3>', esc_html__( 'App List' ) ),
                'content'   => sprintf( '<p>%s</p>', esc_html__( 'Click here to Update or Delete the application.' ) ),
                'anchor_id' => '#mo_oauth_app_list',
                'edge'      => 'left',
                'align'     => 'right',
                'where'     => array( 'toplevel_page_mo_oauth_settings' ) // <-- Please note this
            );
        } else {
            $pointers['miniorange-select-your-idp'] = array(
                'title'     => sprintf( '<h3>%s</h3>', esc_html__( 'Select your OAuth Provider' ) ),
                'content'   => sprintf( '<p>%s</p>', esc_html__( 'Choose your OAuth Provider from the list of OAuth Providers' ) ),
                'anchor_id' => '#mo_oauth_client_default_apps',
                'edge'      => 'left',
                'align'     => 'left',
                'where'     => array( 'toplevel_page_mo_oauth_settings' ) // <-- Please note this
            );
        }
    }
}

if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'attributemapping' ) {
    $pointers['miniorange-configure-atribute-mapping-pointer'] = array(
        'title'     => sprintf( '<h3>%s</h3>', esc_html__( 'Mapping Attributes' ) ),
        'content'   => sprintf( '<p>%s</p>', esc_html__( 'Enter the appropriate values(attribute names) from the Test Configuration table.' ) ),
        'anchor_id' => '#attribute-mapping',
        'edge'      => 'left',
        'align'     => 'right',
        'where'     => array( 'toplevel_page_mo_oauth_settings' ) // <-- Please note this
    );
}

return $pointers;
