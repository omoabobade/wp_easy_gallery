<?php
/*
  Plugin Name: Easy Gallery For Wordpress
  Plugin URI: http://omoabobade.info/
  Description: A wordpress plugin to add and manage gallerific gallery
  Author: Abobade kolawole
  Author URI:http://omoabobade.info/
  Version: 0.1
  License: GPLv2
 */

/*
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; version 2 of the License.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/*
  GENERAL NOTES

 * PHP short tags ( e.g. <?= ?> ) are not used as per the advice from PHP.net
 * No database implementation
 * IMPORTANT: Menu is visible to anyone who has 'read' capability, so that means subscribers
  See: http://codex.wordpress.org/Roles_and_Capabilities for information on appropriate settings for different users

 */

// Make sure that no info is exposed if file is called directly -- Idea taken from Akismet plugin
if (!function_exists('add_action')) {
    echo "This page cannot be called directly.";
    exit;
}

// Define some useful constants that can be used by functions
if (!defined('WP_CONTENT_URL')) {
    if (!defined('WP_SITEURL'))
        define('WP_SITEURL', get_option("siteurl"));
    define('WP_CONTENT_URL', WP_SITEURL . '/wp-content');
}
if (!defined('WP_SITEURL'))
    define('WP_SITEURL', get_option("siteurl"));
if (!defined('WP_CONTENT_DIR'))
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if (!defined('WP_PLUGIN_URL'))
    define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
if (!defined('WP_PLUGIN_DIR'))
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

if (basename(dirname(__FILE__)) == 'plugins')
    define("BLANK_DIR", '');
else
    define("BLANK_DIR", basename(dirname(__FILE__)) . '/');
define("BLANK_PATH", WP_PLUGIN_URL . "/" . BLANK_DIR);

/* Add new menu */
add_action('admin_menu', 'blank_add_pages');

// http://codex.wordpress.org/Function_Reference/add_action

/*

 * ******* BEGIN PLUGIN FUNCTIONS ********

 */


// function for: 
function blank_add_pages() {

    // anyone can see the menu for the Blank Plugin
    add_menu_page('Easy Gallery for Wordpress', 'Easy Gallery for Wordpress Plugin', 'read', 'blank_overview', 'blank_overview', BLANK_PATH . 'images/b_status.png');
    // http://codex.wordpress.org/Function_Reference/add_menu_page
    // this is just a brief introduction
    add_submenu_page('blank_overview', 'Easy Gallery Overview', 'Easy Gallery Overview', 'read', 'blank_overview', 'blank_intro');
    // http://codex.wordpress.org/Function_Reference/add_submenu_page

    add_submenu_page('blank_overview', 'Add Gallery', 'Add Gallery', 'read', 'add_gallery', 'add_gallery');
    add_submenu_page('', 'View Gallery', 'View Gallery', 'read', 'view_gallery', 'view_gallery');
}

register_activation_hook(__FILE__, 'wp_easy_gallery_install');

function wp_easy_gallery_install() {
    global $wpdb;
    $table_name_gallery = $wpdb->prefix . 'wp_easy_galleries';
    $table_name_gallery_images = $wpdb->prefix . 'wp_easy_gallery_images';
    $charset_collate = '';
    if (!empty($wpdb->charset)) {
        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
    }
    if (!empty($wpdb->collate)) {
        $charset_collate .= " COLLATE {$wpdb->collate}";
    }

    $sql_1 = "CREATE TABLE IF NOT EXISTS $table_name_gallery (
         id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(150) NULL,
        created DATETIME NULL,
        active TINYINT(1) NULL DEFAULT 1,
        UNIQUE KEY id (id))
        ENGINE = MyISAM";

    $wpdb->query($sql_1);

    $sql_2 = "CREATE TABLE IF NOT EXISTS $table_name_gallery_images (
         id INT UNSIGNED NOT NULL AUTO_INCREMENT,
         image_id INT NULL,
         gallery_id INT UNSIGNED NOT NULL,
         UNIQUE KEY id (id))
         ENGINE = MyISAM";
    $wpdb->query($sql_2);
}

function wp_easy_gallery_uninstall() {
    global $wpdb;
    $table_name_gallery = $wpdb->prefix . 'wp_easy_galleries';
    $table_name_gallery_images = $wpdb->prefix . 'wp_easy_gallery_images';

    $wpdb->query("DROP TABLE {$table_name_gallery}");
    $wpdb->query("DROP TABLE {$table_name_gallery_images}");
}

register_deactivation_hook(__FILE__, 'wp_easy_gallery_uninstall');

function blank_overview() {
    ?>
    <div class="wrap"><h2>Easy Gallery  Overview</h2>
        <a href="" class="button action">Create a new gallery</a>
    </div>
    <?php
    fetch_gallery_list();
    exit;
}

function fetch_gallery_list(){
    global $wpdb;
    $table_name_gallery = $wpdb->prefix . 'wp_easy_galleries';
    $gallery_list = $wpdb->get_results( "SELECT * FROM $table_name_gallery ORDER BY created DESC");
 
    ?>
                           <table class="widefat fixed" cellspacing="0">
                    <thead>
                        <tr> 
                            <th id="cb" class="manage-column column-cb check-column" scope="col"><input id="cb-select-2" type="checkbox" ></th>
                            <th id="columnname" class="manage-column column-columnname" scope="col">Gallery</th>
                            <th id="columnname" class="manage-column column-columnname num" scope="col">Created</th>
                            <th id="columnname" class="manage-column column-columnname num" scope="col">Active</th>
                            <th id="columnname" class="manage-column column-columnname num" scope="col">Short Code</th>
                            <th id="columnname" class="manage-column column-columnname num" scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
    if ($gallery_list) {
        $i = 1;
        foreach ($gallery_list as $gallery) {
            setup_postdata($gallery);
            ?>
                        <tr class="alternate">
                            <th class="check-column" scope="row"><input id="cb-select-2" type="checkbox" name="post[]" value="<?php echo $gallery->id ?>"></th>
                            <td class="column-columnname">            			
                                <a href="admin.php?page=view_gallery&gallery_id=<?php echo $gallery->id ?> " rel="bookmark" title="Permalink: ">
                                                                    <?php echo $gallery->title ?>
            			</a>
                            </td> 
                            <td class="manage-column column-columnname num"><?php echo $gallery->created?></td>
                            <td class="manage-column column-columnname num"><?php echo $gallery->active?></td>
                            <td class="manage-column column-columnname num"><code>[easy_gallery id=<?php echo $gallery->id?>]</code></td>
                            <td class="manage-column column-columnname num">
                                <a href="admin.php?page=view_gallery&gallery_id=<?php echo $gallery->id ?>">view</a> |
                                <a href="#">click to remove</a><input type="hidden"  name="image_id[]" value="<?php echo $gallery->id ?>"/>                           
                            </td>
                        </tr>        
            <?php
        }
    } else {
        ?>
        	<h2>Not Found</h2>
        <?php
    }
    ?>
          </tbody>       
                    <tfoot>
                        <tr> 
                            <th id="cb" class="manage-column column-cb check-column" scope="col"><input id="cb-select-2" type="checkbox" ></th>
                            <th id="columnname" class="manage-column column-columnname" scope="col">Gallery</th>
                            <th id="columnname" class="manage-column column-columnname num" scope="col">Created</th>
                            <th id="columnname" class="manage-column column-columnname num" scope="col">Active</th>
                            <th id="columnname" class="manage-column column-columnname num" scope="col">Action</th>
                        </tr>
                    </tfoot>
                </table>
                <div class="tablenav bottom"><div class="alignleft actions bulkactions"></div><div class="alignleft actions"></div><br class="clear"></div>         
  <?php
}

function add_gallery() {
    if($_POST){
         global $wpdb;
        // var_dump($_POST);
        // 
            $table_name_gallery = $wpdb->prefix . 'wp_easy_galleries';
            $table_name_gallery_images = $wpdb->prefix . 'wp_easy_gallery_images';

            $wpdb->insert(
                $table_name_gallery, array(
                'title' =>$_POST['gallery_title'] ,
                'created' => current_time('mysql')
                )
            );
            
              $d_id = $wpdb->insert_id;
             $image_id =  $_POST['image_id'];
            foreach ($image_id as $key=>$value) {
               $wpdb->insert(
                        $table_name_gallery_images, array(
                            'image_id' => $value,
                            'gallery_id' =>  $d_id
                        )
                );
        }
        wp_redirect('admin.php?page=blank_overview');
    }else{
     load_plugin_textdomain('easy-gallery', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    assets();
    metabox();       
    }

}

function metabox() {

    echo '<div class="wrap">
            <h2>Create New Gallery</h2>
            <form id="post" action="" method="post">
           <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
            <div id="titlediv">
            <div id="titlewrap">
                            <label class="" id="title-prompt-text" for="title">Enter title here</label>
                    <input type="text" name="gallery_title" size="30" value="" id="title" autocomplete="off">
            </div>
            </div>
            </div>
                <div id="postbox-container-2" class="postbox-container">
<div id="normal-sortables" class="meta-box-sortables ui-sortable">
	<div id="tgm-new-media-plugin" class="postbox ">
		<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle"><span>Upload Gallery Images</span></h3>
			<div class="inside">
       . <div id="tgm-new-media-settings">';
    echo '<p><strong>' . __('Click on the button below to open up the media modal!', 'easy-gallery') . '</strong></p>';
    echo '<p><a href="#" class="tgm-open-media button button-primary" title="' . esc_attr__('Click Here to Open the Media Manager', 'easy-gallery') . '">' . __('Click Here to Open the Media Manager', 'easy-gallery') . '</a></p>';
    echo '<div id="my_media"></div>';
    echo '</div></div>
		</div>
	</div>
</div>
   <input type="submit" name="" id="doaction2" class="button action" value="Save Gallery">
            </div>
            </div>
        
         
            </form>
            . </div>';
}

function my_gallery_scripts() {
                    wp_enqueue_style( 'main', plugins_url('/lib/assets/css/app/main.css', __FILE__) );
	wp_enqueue_style( 'magic.min', plugins_url('/lib/assets/css/vendor/magic/magic.min.css', __FILE__) );
                  wp_enqueue_style( 'animate.min', plugins_url('/lib/assets/css/vendor/animate/animate.min.css', __FILE__) );
                  wp_enqueue_style( 'jquery.desoslide', plugins_url('/lib/dist/css/jquery.desoslide.min.css', __FILE__) );
	wp_enqueue_script( 'bootstrap.min', plugins_url('/lib/assets/js/vendor/bootstrap/bootstrap.min.js', __FILE__) );
                  wp_enqueue_script( 'highlight.pack', plugins_url('/lib/assets/js/vendor/highlight/highlight.pack.js', __FILE__) );
                  wp_enqueue_script( 'jquery.desoslide', plugins_url('/lib/dist/js/jquery.desoslide.js', __FILE__), array('jquery'), '1.0.0', true );
                  wp_enqueue_script( 'initiate_gallery', plugins_url('/js/initiate_gallery.js', __FILE__) );
}

add_shortcode('easy_gallery', 'show_gallery');

function show_gallery($atts){
    
       my_gallery_scripts();
       
        extract(shortcode_atts(array(
        'id' => 0,
                    ), $atts));
        
        $gallery = fetch_gallery($id);
        ob_start();
    ?>
                <style>
                    #slideshow_thumbs li a .img-responsive {
                        width:75px !important;
                        height:75px !important;
                    }
                </style>
        
                        <h4><?php echo $gallery[0]->title ?></h4>
                        <div class="col-lg-12 row">
                             <div class="desoslide-overlay"><div class="desoslide-controls-wrapper"><a class="desoslide-controls prev" href="#prev"></a><a class="desoslide-controls pause" href="#pause" style="display: none;"></a><a class="desoslide-controls play" href="#play"></a><a class="desoslide-controls next" href="#next"></a></div></div>
                           
                        </div>
                        <div class="row">
                            <div id="slideshow" class="col-lg-12"></div>
                        </div>

                        <div class="row">
                            <article class="slide-article">
                                <ul id="slideshow_thumbs" class="desoslide-thumbs-horizontal list-inline text-center">

                                               <?php                                                                                  
                                                         foreach ($gallery as $key => $value) {
                                                         $image_thumbnail = wp_get_attachment_image_src($value->image_id, 'thumbnail');
                                                         $image_large = wp_get_attachment_image_src($value->image_id, 'large');
                                                         $attachment = get_post($value->image_id);
                                                          
                                                  ?>      
                                                                               <li>
                                                                                            <a href="<?php echo $image_large[0]; ?>">
                                                                                                <img src="<?php echo $image_thumbnail[0] ; ?> " class="img-responsive" width="75" height="75"
                                                                                                     alt="<?php echo trim(strip_tags( get_post_meta($value->image_id, '_wp_attachment_image_alt', true) )) ?>"
                                                                                                     data-desoslide-caption-title="<?php echo  trim(strip_tags( $attachment->post_title )) ?>">
                                                                                            </a>
                                                                                </li>
                                               <?php
                                                           }
                                               ?>
                                </ul>
                            </article>
                        </div>
                
    
        <?php
        return ob_get_clean();
}

function view_gallery(){
             global $wpdb;
    if($_POST){

         $table_name_gallery_images = $wpdb->prefix . 'wp_easy_gallery_images';
            $d_id = $_POST['gallery_id'];
             $image_id =  $_POST['image_id'];
            foreach ($image_id as $key=>$value) {
               $wpdb->insert(
                        $table_name_gallery_images, array(
                            'image_id' => $value,
                            'gallery_id' =>  $d_id
                        )
                );
        }
        wp_redirect('admin.php?page=view_gallery&gallery_id='.$d_id);
    }
        assets();
        $gallery = fetch_gallery($_GET['gallery_id']);
        //var_dump($gallery);
        ?>
                <div class="wrap">
                        <h2>Gallery : <?php echo $gallery[0]->title ?></h2>
                        <form id="post" action="" method="post">
                            <div id="poststuff">
                                <div id="post-body" class="metabox-holder columns-2">
                                    
                                    <div id="postbox-container-2" class="postbox-container">
                                        <div id="normal-sortables" class="meta-box-sortables ui-sortable">
                                                <div id="tgm-new-media-plugin" class="postbox ">
                                                        <h3 class="hndle"><span>Gallery Images</span></h3>
                                                                <div class="inside">
                                               .                <div id="tgm-new-media-settings"><p><strong>Click on the button below to open up the media modal!</strong></p><p><a href="#" class="tgm-open-media button button-primary" title="Click Here to Open the Media Manager">Click Here to Open the Media Manager</a></p><div id="my_media"><table class="widefat fixed" cellspacing="0">
                                                                        <thead>
                                                                            <tr> 
                                                                                <th id="cb" class="manage-column column-cb check-column" scope="col"><input id="cb-select-2" type="checkbox"></th>
                                                                                <th id="columnname" class="manage-column column-columnname" scope="col">Title</th>
                                                                                <th id="columnname" class="manage-column column-columnname" scope="col">Image</th>
                                                                                <th id="columnname" class="manage-column column-columnname num" scope="col">Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="tbody">
                                                                            <?php                                                                                  
                                                                            foreach ($gallery as $key => $value) {
                                                                                $attachment = get_post($value->image_id);
                                                                                  ?>      
                                                                            <tr class="alternate">
                                                                                        <th class="check-column" scope="row"><input id="cb-select-2" type="checkbox" name="post[]" value="0"></th>
                                                                                        <td class="column-columnname"><?php echo $attachment->post_title; ?></td> 
                                                                                        <td class="column-columnname"><?php echo wp_get_attachment_image( $value->image_id , 'medium' ); ?></td>
                                                                                        <td class="manage-column column-columnname num"><a href="#">click to remove</a></td>
                                                                                </tr>
                                                                                <?php
                                                                            }
                                                                                ?>
                                                                        </tbody>       
                                                                        <tfoot>
                                                                            <tr> 
                                                                                <th id="cb" class="manage-column column-cb check-column" scope="col"><input id="cb-select-2" type="checkbox"></th>
                                                                                <th id="columnname" class="manage-column column-columnname" scope="col">Title</th>
                                                                                <th id="columnname" class="manage-column column-columnname" scope="col">Image</th>
                                                                                <th id="columnname" class="manage-column column-columnname num" scope="col">Action</th>
                                                                            </tr>
                                                                        </tfoot>
                                        </table>
                                                       <div class="tablenav bottom"><div class="alignleft actions bulkactions"></div><div class="alignleft actions"></div><br class="clear"></div>
                                                   </div>
                                               </div>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                    <input type="hidden" id="is_edit" value="1" />
                                    <input type="hidden" name="gallery_id" value="<?php echo $gallery[0]->gallery_id ?>" />
                                    <input type="submit" name="" id="doaction2" class="button action" value="Save Gallery">
                                </div>
                            </div>
                            
                        </form>
                        . </div>    
 <?php
}

function fetch_gallery($id){
         global $wpdb;
        $table_name_gallery = $wpdb->prefix . 'wp_easy_galleries';
        $table_name_gallery_images = $wpdb->prefix . 'wp_easy_gallery_images';
        return $wpdb->get_results( $wpdb->prepare("SELECT * FROM  $table_name_gallery key1 JOIN  $table_name_gallery_images key2
	            ON key2.gallery_id = key1.id  WHERE  key1.id = %s ", $id));
}


/**
 * Loads any plugin assets we may have.
 *
 * @since 1.0.0
 *
 * @return null Return early if not on a page add/edit screen
 */
function assets() {

    // This function loads in the required media files for the media manager.
    wp_enqueue_media();

    // Register, localize and enqueue our custom JS.
    wp_register_script('easy-gallery-media', plugins_url('/js/media.js', __FILE__), array('jquery'), '1.0.0', true);
    wp_localize_script('easy-gallery-media', 'tgm_nmp_media', array(
        'title' => __('Upload or Choose Your Custom Image File', 'easy-gallery'), // This will be used as the default title
        'button' => __('Insert Image into Input Field', 'easy-gallery')            // This will be used as the default button text
            )
    );
    wp_enqueue_script('easy-gallery-media');
}
?>