<?php
  
class Mo_OAuth_Client_Admin_Addons {

  public static $all_addons = array(
        array(
          'tag' => 'page-restriction',
          'title' => 'Page & Post Restriction',
          'desc' => 'Allows to restrict access to WordPress pages/posts based on user roles and their login status, thereby preventing them from unauthorized access.',
          'img' => 'images/page-restriction.png',
          'link' => 'https://plugins.miniorange.com/wordpress-page-restriction',
        ),

        array(
          'tag' => 'fsso',
          'title' => 'Two Factor Authentication',
          'desc' => 'Supported 2FA methods:- Google Authenticator, OTP Over SMS, OTP Over Email, Email Verification, miniOrange methods. <br>Additional Features:- Unlimited Users & Multisite support,Website Security features',
          'img' => 'images/fsso.png',
          'link' => 'https://plugins.miniorange.com/2-factor-authentication-for-wordpress',
        ),

        array(
          'tag' => 'buddypress',
          'title' => 'BuddyPress Integrator',
          'desc' => 'Allows to integrate user information received from OAuth/OpenID Provider with the BuddyPress profile.',
          'img' => 'images/buddypress.png',
          'link' => 'https://plugins.miniorange.com/wordpress-buddypress-integrator',
        ),

        array(
          'tag' => 'login-form',
          'title' => 'Login Form Add-on',
          'desc' => 'Provides Login form for OAuth/OpenID login instead of a only a button. It relies on OAuth/OpenID plugin to have Password Grant configured. It can be customized using custom CSS and JS.',
          'img' => 'images/login-form.png',
        ),

        array(
          'tag' => 'member-login',
          'title' => 'Membership based Login',
          'desc' => "Allows to redirect users to custom pages based on users' membership levels. Checks for the user's membership level during every login, so any update on the membership level doesn't affect redirection.",
          'img' => 'images/member-login.png',
        ),

        array(
          'tag' => 'learndash',
          'title' => 'LearnDash Integration',
          'desc' => 'If you want to integrate LearnDash with your IDP then you can opt-in for this add-on. This add-on will map the users to LearnDash groups based on the attributes sent by your IDP.',
          'img' => 'images/learndash.png',
          'link' => 'https://plugins.miniorange.com/wordpress-learndash-integrator',
        ),

        array(
          'tag' => 'login-audit',
          'title' => 'SSO Login Audit',
          'desc' => 'SSO Login Audit captures all the SSO users and will generate the reports.',
          'img' => 'images/report.png',
          'link' => 'https://plugins.miniorange.com/wordpress-sso-login-audit',
        ),

        array(
          'tag' => 'attribute',
          'title' => 'Attribute Based Redirection',
          'desc' => 'ABR add-on helps you to redirect your users to different pages after they log into your site, based on the attributes sent by your Identity Provider.',
          'img' => 'images/attribute-icon.png',
          'link' => 'https://plugins.miniorange.com/wordpress-attribute-based-redirection-restriction',
        ),

        array(
          'tag' => 'scim',
          'title' => 'SCIM User Provisioning',
          'desc' => 'This plugin allows user provisioning with SCIM standard. System for Cross-domain Identity Management is a standard for automating the exchange of user identity information between identity domains, or IT systems.',
          'img' => 'images/scim.png',
          'link' => 'https://plugins.miniorange.com/wordpress-user-provisioning',
        ),

        array(
          'tag' => 'discord',
          'title' => 'Discord Role Mapping',
          'desc' => 'Discord Role Mapping add-on helps you to get roles from your discord server and maps it to WordPress user while SSO.',
          'img' => 'images/discord.png',
          'link' => 'https://plugins.miniorange.com/discord-wordpress-single-sign-on-integration',
        ),

        array(
          'tag' => 'session',
          'title' => 'SSO Session Management',
          'desc' => 'SSO session management add-on manages the login session time of your users based on their WordPress roles.',
          'img' => 'images/session.jpg',
          'link' => 'https://plugins.miniorange.com/sso-session-management',
        ),

        array(
          'tag' => 'media',
          'title' => 'Media Restriction',
          'desc' => 'miniOrange Media Restriction add-on restrict unauthorized users from accessing the media files on your WordPress site.',
          'img' => 'images/media.jpg',
          'link' => 'https://plugins.miniorange.com/wordpress-media-restriction',
        ),

        array(
          'tag' => 'profile_pic',
          'title' => 'Profile Picture Add-on',
          'desc' => 'Maps raw image data or URL received from your Identity Provider into Gravatar for the user.',
          'img' => 'images/profile_pic.png',
        ),

      );

  public static $RECOMMENDED_ADDONS_PATH = array(

        "learndash"     =>  "sfwd-lms/sfwd_lms.php",
        "buddypress"    =>  "buddypress/bp-loader.php",
        "memberpress"   =>  "memberpress/memberpress.php",
        "woocommerce"   =>  "woocommerce/woocommerce.php"
    );
      

  public static function addons() {
      self::addons_page();
  }
    
    public static function addons_page() {

      $addons_recommended = array();
      
?>

<style>
.outermost-div {
  color: #424242;
  font-family: Open Sans!important;
  font-size: 14px;
  line-height: 1.4;
  letter-spacing: 0.3px;
}

.column_container {
  position: relative;
  box-sizing: border-box;
  margin-top: 20px;
  border-color: 1px solid red;
  z-index: 1000;
}  

.column_container > .column_inner {
  
  box-sizing: border-box;
  padding-left: 15px;
  padding-right: 10px;
  width: 100%;
  margin-right: 1px;
  font-family: Verdana, Arial, Helvetica, sans-serif;
  border-radius: 15px;
} 

.benefits-outer-block{
  padding-left: 1em;
  padding-right: 3em;
  padding-top: 3px;
  width: 80%;
  margin: 0;
  padding-bottom: 1em;
  background:#fff;
  height:230px;
  overflow: hidden;
  box-shadow: 0 5px 10px rgba(0,0,0,.20);
  border-radius: 5px;
}

.benefits-outer-block:hover{
 margin-top: -10px;
 border-top: 5px solid #0063ae;
 transition:  .3s ease-in-out;
 transform: scale(1.02);
}

.benefits-icon {
  font-size: 25px;
  padding-top: 6px;
  padding-right: 8px;
  padding-left: 8px;
  border-radius: 3px;
  padding-bottom: 5px;
  background: #1779ab;
  color: #fff;
}

.mo_2fa_addon_button{
  margin-top: 3px !important;
}

.mo_float-container {
    border: 1px solid #fff;
    padding-bottom: 50px;
   padding-top: 10px;
   padding-left: 1px;
   padding-right: 2px;
   width: 246px;
}

.mo_float-child {
    width: 17%;
    float: left;
    padding: 1px;
    padding-right: 0px;
    padding-left: 0px;
    height: 50px;
}  

.mo_float-child2{

    width: 78%;
    float: left;
    padding-left: 0px;
    padding-top:0px;
    height: 50px;
    font-weight: 700;
}

h5 {
  font-weight: 700;
  font-size: 16px;
  line-height: 20px;
  text-transform: none;
  letter-spacing: 0.5px;
  color: #585858;
}

a {
  text-decoration: none;
  color: #585858;
}

@media (min-width: 768px) {
  .grid_view {
    width: 33%;
    float: left;
  }
  .row-view {
    width: 100%;
    position: relative;
    display: inline-block;
  }
}

/*Content Animation*/
@keyframes fadeInScale {
  0% {
    transform: scale(0.9);
    opacity: 0;
  }
  
  100% {
    transform: scale(1);
    opacity: 1;
  }
}
</style>
<input type="hidden" value="<?php echo mo_oauth_is_customer_registered();?>" id="mo_customer_registered_addon">

<a  id="mobacktoaccountsetup_addon" style="display:none;" href="<?php echo add_query_arg( array( 'tab' => 'account' ), htmlentities( $_SERVER['REQUEST_URI'] ) ); ?>">Back</a>

<form style="display:none;" id="loginform_addon"
              action="<?php echo get_option( 'host_name' ) . '/moas/login'; ?>"
              target="_blank" method="post">
            <input type="email" name="username" value="<?php echo get_option( 'mo_oauth_admin_email' ); ?>"/>
            <input type="text" name="redirectUrl"
                   value="<?php echo "http://plugins.miniorange.com/go/oauth-2fa-buy-now-payment"; ?>"/>
            <input type="text" name="requestOrigin" id="requestOrigin"/>
</form>
  
  <?php
  foreach (Mo_OAuth_Client_Admin_Addons::$RECOMMENDED_ADDONS_PATH as $key => $value) {
    if(is_plugin_active($value)){
      $addon = $key;
      $addons_recommended[$addon] = $addon;
    }
  }

  if(sizeof($addons_recommended)>0){ ?>
    <div class="mo_table_layout">
    <b><p style="padding-left: 15px;font-size: 20px;margin-top: 10px; margin-bottom: 10px;">Recommended Add-ons for you:</p></b>
    <div class="outermost-div" style="background-color:#f7f7f7;opacity:0.9; ">
    <div class="row-view">
    <?php
     foreach ($addons_recommended as $key => $value)
      Mo_OAuth_Client_Admin_Addons::get_single_addon_cardt($value);
  }

    ?>
  </div>
</div>
</div>

<div class="mo_table_layout">
  <b><p style="padding-left: 15px;font-size: 20px;margin-top: 10px; margin-bottom: 10px;">Check out our add-ons :</p></b>
<div class="outermost-div" style="background-color:#f7f7f7;opacity:0.9;">

  <?php

  $available_addons = array();
  foreach (Mo_OAuth_Client_Admin_Addons::$all_addons as $key => $value) {
    # code...
    if(!array_search($value['tag'], $addons_recommended))
      array_push($available_addons, $value['tag']);
  }

  $all_addons = Mo_OAuth_Client_Admin_Addons::$all_addons;
  $total_addons = sizeof($available_addons);
    
    for ($i=0; $i < $total_addons; $i++) { ?>
      <div class="row-view">
        <?php 
        Mo_OAuth_Client_Admin_Addons::get_single_addon_cardt($available_addons[$i]);
        if($i+1 >= $total_addons)
          break;
        Mo_OAuth_Client_Admin_Addons::get_single_addon_cardt($available_addons[$i+1]);
        $i++;
        if($i+1 >= $total_addons)
          break;
        Mo_OAuth_Client_Admin_Addons::get_single_addon_cardt($available_addons[$i+1]);
        $i++;
        ?>
      </div> 
    <?php 
  }
  ?>
</div></div>


<script type="text/javascript">
   function upgradeform(planType) {
                if(planType === "") {
                  
                    location.href = "https://wordpress.org/plugins/miniorange-login-with-eve-online-google-facebook/";
                    return;
                } else {
                    
                    jQuery('#requestOrigin').val(planType);
                    if(jQuery('#mo_customer_registered_addon').val()==1)
                      {
                        jQuery('#loginform_addon').submit();
                       
                    }
                    else{
                        location.href = jQuery('#mobacktoaccountsetup_addon').attr('href');
                    }
                }

            }
</script>
<?php
    }

    public static function get_single_addon_cardt($tag){
      foreach (Mo_OAuth_Client_Admin_Addons::$all_addons as $key => $value) {
        # code...
        if(array_search($tag, $value)){
          $addon = $value;
          break;
        }
      }
      if(isset($addon)){
    ?>
      <div class="grid_view column_container" style="border-radius: 5px;">
        <div class="column_inner" style="border-radius: 5px;">
        <div class="row benefits-outer-block">
        <div class="mo_float-container">
            <div class="mo_float-child" style="margin-left: 0px;padding-left: 0px;"> 
            <img src="<?php echo plugins_url($addon['img'], __FILE__) ?>" width="45px" height="48px">
            </div>
        <div class="mo_float-child2">
          <div><strong><p style="font-size: 20px;margin: 1px;padding-left: 7px;line-height: 120%;font-weight: 600;font-family: Verdana, Arial, Helvetica, sans-serif;" ><a href= "<?php echo isset($addon['link']) ? $addon['link'] : '';?>" target="_blank"><?php echo $addon['title'] ?></a></p></strong></div>
          </div>
        </div>
        <p style="text-align: center;font-family: Verdana, Arial, Helvetica, sans-serif;font-size: 12px;"><?php echo $addon['desc'] ?></p>
        </div>
        </div>
      </div>
        <?php
      }
    }
}
?>