<?php

// Don't run this file directly.
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

// Formating version
function wyp_version($v){
    $v = preg_replace('/[^0-9]/s', '', $v);
    if(strlen($v) == 2){
        return $v."0";
    }elseif(strlen($v) == 1){
        return $v."00";
    }else{
        return $v;
    }
}

// Defining plugin version
define("YP_FORMATTED_VERSION", wyp_version(YP_VERSION));


// Getting purchase code
function wyp_setting_purchase_code(){

    if(defined("YP_THEME_MODE")){
        define("YP_PURCHASE_CODE","YELLOW_PENCIL_THEME_LICENSE"); // Extended theme mode
    }else{
        define("YP_PURCHASE_CODE",get_option('yp_purchase_code')); // personal user information
    }

}
add_action("admin_init","wyp_setting_purchase_code");


// Basic update
function wyp_install_plugin($plugin){

    if(current_user_can("update_plugins")){

        // by this point, the $wp_filesystem global should be working, so let's use it to create a file
        global $wp_filesystem;

        // Initialize the WP filesystem, no more using 'file-put-contents' function
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . '/wp-admin/includes/file.php');
            WP_Filesystem();
        }

        // plugin array; name, download uri, install path
        $plugin = $plugin[0];

        // Plugins path
        $path = ABSPATH.'wp-content/plugins/';

        // Zip file path
        $zip = $path.$plugin['name'].'.zip';

        // The plugin folder
        $install = $plugin['install'];

        // delete zip file if exists
        if($wp_filesystem->exists($zip)){
            $wp_filesystem->delete($zip);
        }

        // trying to download zip file
        $response = wp_remote_get(
            $plugin['uri'],
            array(
                'sslverify' => false,
                'timeout'   => 300,
                'stream'    => true,
                'filename'  => $zip
            )
        );

        // Problem with download zip file
        if (is_wp_error($response)){

            // delete zip file
            if($wp_filesystem->exists($zip)){
                $wp_filesystem->delete($zip);
            }

            // Error message
            wp_die($response->get_error_message());

            // stop if cannot rename
            return false;

        }

        $oldPluginDir = $path . "waspthemes-yellow-pencil-old";
        $pluginDir = $path . "waspthemes-yellow-pencil";

        // Old is exists
        if($wp_filesystem->exists($oldPluginDir)){

            // delete the -old folder
            if(is_dir($oldPluginDir)){
                $wp_filesystem->chmod($oldPluginDir, FS_CHMOD_DIR);
                $wp_filesystem->delete($oldPluginDir, true, "d");
            }

        }

        // Change folder name
        if(!$wp_filesystem->move($pluginDir, $oldPluginDir)){

            // delete zip file
            if($wp_filesystem->exists($zip)){
                $wp_filesystem->delete($zip);
            }

            // change folder name error
            wp_die("Something went wrong. Please try again.");

            // stop if cannot rename
            return false;

        }

        // Unzip file
        $unzipStatus = unzip_file($zip, $path);

        // delete zip file
        if($wp_filesystem->exists($zip)){
            $wp_filesystem->delete($zip);
        }

        // If Unzip zip file
        if(!$unzipStatus){

            // rename -old as default and stop installing
            $wp_filesystem->move($oldPluginDir, $pluginDir);

            wp_die("There was an error installing the plugin.");

            return false;

        }

        // be sure, this unzipped (waspthemes-yellow-pencil/yellow-pencil.zip is available)
        if($wp_filesystem->exists($path . $install)){

            // Delete
            if(is_dir($oldPluginDir)){
                $wp_filesystem->chmod($oldPluginDir, FS_CHMOD_DIR);
                $wp_filesystem->delete($oldPluginDir, true, "d");
            }

            // Force active the plugin
            wyp_plugin_activate($install);

        }

        // show result
        return true;

    }

}


// Getting version
function wyp_getting_ver_from_changelog(){

    $version = 0;
    $pluginVersion = YP_FORMATTED_VERSION;

    // Changelog URL
    $url = "https://waspthemes.com/yellow-pencil/inc/changelog.txt?r=".rand(1, 1000);

    // Getting Changelog
    $response = wp_remote_get($url, array( 'sslverify' => false, 'timeout' => 300 ));

    // If page found.
    if(is_wp_error($response) == false && wp_remote_retrieve_response_code($response) == 200){

        // Get page
        $response = wp_remote_retrieve_body( $response );

        // If have data.
        if(!empty($response)){

            // Get First line.
            $last_update = substr($response, 0, 32);

            // Part of first line.
            $array = explode('(',$last_update);

            // Only version.
            $version = wyp_version($array['0']);

            if($version > $pluginVersion){

                // Add to datebase, because have a new update.
                if(get_option('yp_update_status') !== false ){
                    update_option( 'yp_update_status', 'true');
                    update_option( 'yp_last_check_version', $pluginVersion);
                    update_option( 'yp_available_version', $version);
                }else{
                    add_option( 'yp_update_status', 'true');
                    add_option( 'yp_last_check_version', $pluginVersion);
                    add_option( 'yp_available_version', $version);
                }

                    return true;

            }else{

                // Update database, because not have a new update.
                if(get_option('yp_update_status') !== false ){
                    update_option( 'yp_update_status', 'false');
                }else{
                    add_option( 'yp_update_status', 'false');
                }

                return false;

            }

        } // If has data.

    } // IF URL working.

}


// check changelog in background
function wyp_update_checker(){

    // Update available just for pro users.
    if(defined('WTFV') == true && current_user_can("update_plugins") == true && check_admin_referer("wyp_update_checker_nonce")){

        // download changelog and check if update available
        wyp_getting_ver_from_changelog();

    }

    // die
    die();

}

add_action('wp_ajax_wyp_update_checker', 'wyp_update_checker', 9999);



// Getting plugin download uri from Envato
function wyp_get_download_uri_by_purchase(){

    // Checks download uri
    $download_uri = 'https://waspthemes.com/yellow-pencil/auto-update/download.php?purchase_code='.YP_PURCHASE_CODE;

    // Getting plugin download url
    $data = wp_remote_get($download_uri, array( 'sslverify' => false, 'timeout' => 300 ));

    // Error
    if(is_wp_error($data)){
        die($data->get_error_message());
    }

    // Get data
    $data = wp_remote_retrieve_body( $data );

    // No empty
    if(empty($data)){
        die('Updating is failed. Please update the plugin manually.');
    }

    // Data is the download URL
    return $data;

}


// Active new version.
function wyp_plugin_activate($installer){

    if(current_user_can("activate_plugins")){

        $current = get_option('active_plugins');
        $plugin = plugin_basename(trim($installer));

        if(!in_array($plugin, $current)){
            $current[] = $plugin;
            sort($current);
            do_action('activate_plugin', trim($plugin));
            update_option('active_plugins', $current);
            do_action('activate_'.trim($plugin));
            do_action('activated_plugin', trim($plugin));
            return true;
        }else{
            return false;
        }

    }

}

// show update message.
function wyp_update_message(){

    // Update available just for pro users.
    if(defined('WTFV') && defined('YP_PURCHASE_CODE') && defined('YP_FORMATTED_VERSION')){

        // get screen
        $screen = get_current_screen();
        $base = $screen->base;

        $lastCheckVer = get_option('yp_last_check_version');
        $isUpdate = get_option('yp_update_status');
        $available = get_option('yp_available_version');

        if($isUpdate != 'true' && current_user_can("update_plugins") == true && YP_PURCHASE_CODE == '' && strstr($base,"yellow-pencil") == false){
            ?>
            <div class="updated notice yellowpencil-notice">
                <p>Would you like to receive automatic updates? Please <a style='box-shadow:none !important;-webkit-box-shadow:none !important;-moz-box-shadow:none !important;' href='<?php echo admin_url('admin.php?page=yellow-pencil-license'); ?>'>activate your copy</a> of YellowPencil.</p>
            </div>
        <?php
        }

        if($isUpdate == 'true' && $lastCheckVer == YP_FORMATTED_VERSION && $available > YP_FORMATTED_VERSION && current_user_can("update_plugins") == true && YP_PURCHASE_CODE != ''){

            $versionDots = str_split($available);
            $versionView = join('.', $versionDots);

            ?>
            <div class="updated notice yellowpencil-notice">
                <p><a target="_blank" href="https://yellowpencil.waspthemes.com/changelog/">YellowPencil <?php echo $versionView; ?></a> is available! <a style="box-shadow:none !important;-webkit-box-shadow:none !important;-moz-box-shadow:none !important;cursor:pointer;text-decoration:underline;" class="wyp_update_link">Please update now.</a></p>
            </div>
            <?php

        }elseif($isUpdate == 'true' && $lastCheckVer == YP_FORMATTED_VERSION && $available > YP_FORMATTED_VERSION && current_user_can("update_plugins") == true && strstr($base,"yellow-pencil") == false){

            ?>
            <div class="updated notice yellowpencil-notice">
                <p>New updates are available for YellowPencil! Please activate your copy to receive automatic updates. <a style="box-shadow:none !important;-webkit-box-shadow:none !important;-moz-box-shadow:none !important;" href="<?php echo admin_url('admin.php?page=yellow-pencil-license'); ?>">Activate now!</a></p>
            </div>
            <?php

        }

    }

}

// Begin to update for Pro version.
function wyp_update_now(){

    if(check_admin_referer("wyp_update_plugin_nonce")){

        $lastCheckVer = get_option('yp_last_check_version');
        $isUpdate = get_option('yp_update_status');
        $available = get_option('yp_available_version');

        if($isUpdate == 'true' && $lastCheckVer == YP_FORMATTED_VERSION && $available > YP_FORMATTED_VERSION && current_user_can("update_plugins") == true && YP_PURCHASE_CODE != ''){

            // Getting the download uri.
            $uri = wyp_get_download_uri_by_purchase();

            // Update.
            $re = wyp_install_plugin(array(
                array('name' => 'yellow_pencil_update_pack', 'uri' => $uri, 'install' => 'waspthemes-yellow-pencil/yellow-pencil.php'),
            ));

            if(!$re){
                wp_die("The server doesn't support automatic updates. Please update manually.");
            }

        }

        wp_die("Updated successfully.");

    }

}


// Update javascript
function wyp_update_javascript() { ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {

        <?php

        // Update available just for pro users.
        if(defined('WTFV') == true && current_user_can("update_plugins") == true){

            // Get time
            $timeStamp = current_time('timestamp', 1);

            // if already check time or this first run
            if(intval($timeStamp-intval(get_option('yp_checked_data'))) > 43200 || get_option('yp_checked_data') === false){

                // Action nonce
                $nonce = wp_create_nonce('wyp_update_checker_nonce');

                // Send in background
                echo "// Update Checker API\n\t\t";
                echo 'jQuery.post("'.admin_url('admin-ajax.php?action=wyp_update_checker&_wpnonce='.$nonce).'");';
                echo "\n";

                // Update time again
                if (!update_option( 'yp_checked_data', $timeStamp)){
                    add_option( 'yp_checked_data', $timeStamp);
                }

            }

        }

        ?>

        // Update API
        jQuery(".wyp_update_link").click(function(){

            var notice = jQuery(this).parent().parent(),
                p = jQuery(this).parent();

            // Updating.
            notice.addClass("wyp-updating");
            p.html(" Updating, please do not refresh the page.");

            var data = {
                'action': 'wyp_update_now',
                '_wpnonce': '<?php echo wp_create_nonce("wyp_update_plugin_nonce"); ?>'
            };

            jQuery.post(ajaxurl,data, function(response) {

                p.html(" " + response);
                notice.removeClass("wyp-updating");

                if(response == "Updated successfully."){
                    notice.addClass("wyp-updated");
                }else{
                    notice.addClass("wyp-failed-update");
                }

            });

        });


        // Disable activation btn
        jQuery(".wyp-product-activation").on("click",function(){
            jQuery(this).addClass("disabled");
        });

    });
    </script><?php
}

// Admin update script
add_action( 'admin_footer', 'wyp_update_javascript' );

// Alert update
add_action( 'admin_notices', 'wyp_update_message' );

// Ajax action.
add_action( 'wp_ajax_wyp_update_now', 'wyp_update_now' );
