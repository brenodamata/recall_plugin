<?php
/*
Plugin Name: Recall Migrator
Plugin URI: http://github.com/terakeet/recall_plugin
Description: Retrieves recall data information
Version: 1.02
Author: Breno da Mata
Author URI: http://github.com/brenodamata
License: MIT

Please note, the purpose of reusing parts of the airport plugin are simply a method
to pull data from non-wordpress tables (avoiding WP_Query, and its ilk).

I would *highly* suggest starting a new plugin, and pulling in only the little bits you need at this point:
The hook for parse_request in airport_manager.php (this is where the front-end magic really happens)
Idorm (The micro orm wrapper for making "models" based on tables)
The public view rendering function, which renders a front-end view and wraps itself in the current wordpress theme's shell.
The search component (very basic)
The bulk of the plugin is related to managing the database, and will just be cruft you need to dig through.
*/

include __DIR__.'/lib/RecallMigrator_Base.class.php';
include __DIR__.'/lib/Idorm.php';
include __DIR__.'/models/RM_Recall.class.php';

//Exit if accessed directly
if ( ! defined( 'ABSPATH') ) {
  exit;
}

// include __DIR__.'/lib/Encoding.php';
// include __DIR__.'/lib/Idorm.php';
// include __DIR__.'/models/AM_Country.class.php';
// include __DIR__.'/models/AM_State.class.php';
// include __DIR__.'/models/AM_City.class.php';
// include __DIR__.'/models/AM_Airport.class.php';
// include __DIR__.'/models/AM_Permalink.class.php';


if ( class_exists( 'RecallMigrator' ) )
  return;

define( 'RECALL_MIGRATOR_VERSION', '1.0' );
define( 'RECALL_MIGRATOR_CACHE_DURATION', 60*60*1 ); // 1 hour

class RecallMigrator extends RecallMigrator_Base {

  var $plugin_base = '';
  var $plugin_file = '';
  var $plugin_path = '';
  // var $cm = null; // Countries
  // var $mm = null; // Municipalities (Cities)
  // var $sm = null; // States
  // var $am = null; // Airports
  // var $pm = null; // Permalinks

  function RecallMigrator() {
    $host_string = sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME);
    ORM::configure($host_string);
    // ORM::configure('id_column_overrides', array(
      // 'countriesinfo' => 'countryid',
      // 'us_states' => 'stateid'
      // ));
    ORM::configure('username', DB_USER);
    ORM::configure('password', DB_PASSWORD);
    $this->plugin_base = rtrim( dirname( __FILE__ ), '/' );
    $this->plugin_file = basename(__FILE__);
    $this->plugin_path = plugin_dir_path(__FILE__);
    if(is_admin()) {
      add_action( 'admin_menu', array(&$this, 'admin_menu') );
      register_activation_hook(__FILE__, array(&$this, 'activate'));
      register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
      // wp_enqueue_style( 'airport_manager', plugin_dir_url(__FILE__).'css/airport_manager.css', $this->version() );
    }

    //add_action( 'init', array(&$this, 'manager') ); // magic within.
    add_action('parse_request', array(&$this, 'recall_migrate'));

    // MicroORMs:
    $this->rm = new RM_Recall();
    // $this->cm = new AM_Country();
    // $this->mm = new AM_City();
    // $this->sm = new AM_State();
    // $this->am = new AM_Airport();
    // $this->pm = new AM_Permalink();

  }

  function recall_migrate(){

    $ary = explode('?', $_SERVER['REQUEST_URI']);
    $uri = $ary[0];

    if( stripos($uri, '/airports') === 0 || stripos($uri, '/private-jet/') === 0 || stripos($uri, '/airport-data/') === 0 ) {
      $row = $this->search_for_permalink($uri, true);
      if($row) {
        wp_redirect($row['permalink'], 301);
        exit;
      }
    } else if( stripos(rtrim($uri, '/'), '/private-jet-charter/') === 0 ) {
      // See if we have anything inside /private-jet-charter/ that needs 301 fun
      $row = $this->search_for_permalink($uri, true);
      if($row) {
        wp_redirect($row['permalink'], 301);
        exit;
      }

      $row = $this->search_for_permalink(rtrim(strtolower($uri),'/'));
      if($row) {
        $item = $row;
        if( $item['airport_id'] ) {
          /* ############################### AIRPORT ################################### */
          $airport = $this->am->find($item['airport_id']);
          $country = null;
          $state = null;
          $muni = null;
          $muni = $this->mm->find($airport['municipality_id']);
          $country = $this->cm->find_by_code($airport['iso_country']);
          $state = $this->sm->find($airport['us_state_id']);
          $muni_url = $this->mm->find_permalink($airport['municipality_id']);
          $vars = array('permalink' => $item, 'airport' => $airport, 'country' => $country, 'state' => $state, 'muni' => $muni, 'muni_url' => $muni_url);
          $view = 'airport';
        } else if ( $item['country_id'] ) {
          /* ############################### COUNTRY ################################### */
          $country = $this->cm->find($item['country_id']); //search_for_country($item['country_id'], 'countryid');
          $states = null;
          $munis = null;
          if($country->code == 'US') {
            $states = $this->get_us_states();
          } else {
            $munis = $this->search_for_objects($country->code, 'municipalities', 'iso_country', 'id', 'muni_id', 'municipalities.name');
          }
          $vars = array('permalink' => $item, 'country' => $country, 'states' => $states, 'munis' => $munis);
          $view = 'country';
        } else if ( $item['state_id'] ) {
          /* ############################### STATE ################################### */
          $state = $this->search_for_state($item['state_id']);
          $country = null;
          $country = $this->search_for_country('US');
          $munis = $this->search_for_objects($state['stateid'], 'municipalities', 'us_state_id', 'id', 'muni_id', 'municipalities.name');
          $vars = array('permalink' => $item, 'state' => $state, 'country' => $country, 'munis' => $munis);
          $view = 'state';
        } else if ( $item['muni_id'] ) {
          /* ############################### MUNI ################################### */
          $muni = $this->search_for_muni($item['muni_id']);
          $state = null;
          $country = null;
          $airports = null;
          if($muni->us_state_id) {
            $state = $this->search_for_state($muni->us_state_id);
          }
          $country = $this->search_for_country($muni->iso_country);
          $airports = $this->search_for_objects($muni->id, 'airportsinfo', 'municipality_id', 'id', 'airport_id', 'airportsinfo.name');
          $vars = array('permalink' => $item, 'muni' => $muni, 'country' => $country, 'state' => $state, 'airports' => $airports);
          $view = 'muni';
        }

        $title_template = $this->get_or_cache_transient($view . '_title_template');
        $header_template = $this->get_or_cache_transient($view . '_header_template');
        $body_template = $this->get_or_cache_transient($view . '_body_template');

        $vars['generated_title'] = $this->replace_tokens($view, $vars, $title_template);
        $vars['generated_header'] = $this->replace_tokens($view, $vars, $header_template);
        $vars['generated_content'] = $this->replace_tokens($view, $vars, $body_template);

        $this->render_view("public/$view", $vars);
        exit; // Halt WordPress execution.
      }
    }
  }

  // Cached to avoid constant lookups
  function get_or_cache_transient($key) {
    if( false === ($transient = get_transient($key) ) ) {
      $opts = get_option('airport_manager_options');
      $transient = $opts[$key];
      set_transient($key, $transient, AIRPORT_MANAGER_CACHE_DURATION);
    }
    return $transient;
  }

  function search_for_object($id, $table, $id_field = 'id') {
    global $wpdb;
    $query = "SELECT * FROM $table WHERE $id_field=%s";
    $sql = $wpdb->prepare( $query, $id );
    return $wpdb->get_row( $sql, ARRAY_A );
  }

  function search_for_objects($value, $table, $search_field, $pl_k, $pl_fk, $order_field = null) {
    global $wpdb;
    if( $order_field ) {
      $order_by = "order by $order_field";
    } else {
      $order_by = null;
    }
    $query = "SELECT * FROM $table inner join recall_permalinks on $table.$pl_k=recall_permalinks.$pl_fk WHERE $search_field=%s $order_by";

    $sql = $wpdb->prepare( $query, $value );
    return $wpdb->get_results( $sql, ARRAY_A );
  }

  function search_for_permalink($uri, $for_redirect=false) {
    global $wpdb;
    $where = ($for_redirect) ? 'redirect_from' : 'permalink';
    $fields = ($for_redirect) ? 'permalink' : 'permalink, recall_id';
    $trimmed = rtrim($uri, '/');
    $query = "SELECT $fields FROM recall_permalinks WHERE $where=%s or $where=%s";
    $sql = $wpdb->prepare( $query, $uri, $trimmed );
    return $wpdb->get_row( $sql, ARRAY_A );
  }

  function process_header($title, $description, $keywords = '') {
    ob_start();
    get_header();
    $header = ob_get_clean();

    $output = '<title>%s</title><meta name="description" content="%s">';
    $inject = sprintf($output, htmlentities($title), htmlentities($description), htmlentities($keywords));
    return str_replace('<title></title>', $inject, $header);
  }


  function admin_menu() {
    add_menu_page( __( "Recall Migrator", 'recall_migrator' ), __( "Recall Migrator", 'recall_migrator' ), "administrator", basename( __FILE__ ), array( &$this, "route_request" ), null, 25.2112 );
  }

  // TODO Loose coupling.
  function route_request() {
    $action = isset( $_GET['recall_migrator_action'] ) ? $_GET['recall_migrator_action'] : '';
    $options = $this->get_options();
    $page = max(intval($_GET['paginate']), 1); // page is reserved.
    $_POST = stripslashes_deep($_POST);
    switch($action) {
      case 'edit_template':
        $type = $_GET['template_type'];
        if(strlen($type) == 0) {
          return $this->render_view('admin/error', array( 'errors' => array('No template type specified.') ));
        }
        return $this->admin_edit_template($type);
        break;
      case 'save_template':
        $type = $_POST['template_type'];
        if(strlen($type) == 0) {
          return $this->render_view('admin/error', array( 'errors' => array('No template type specified.') ));
        }
        return $this->admin_save_template($_POST);
        break;
      case 'edit_recall':
        $airport_id = $_GET['recall_id'];
        return $this->admin_edit_recall($recall_id, $page);
        break;
      case 'search':
        $query = (isset($_POST['rm_query'])) ? $_POST['rm_query'] : $_GET['am_qurm_queryery'];
        $type = (isset($_POST['rm_type'])) ? $_POST['rm_type'] : $_GET['rm_type'];
        return $this->admin_search($query, $type, $page);
        break;
      case 'delete_recall':
        $thing = 'Recall';
        $id_field = strtolower($thing).'_id';
        $id_value = $_GET[$id_field];
        $item = $this->am->find($id_value);
        if($item !== false) {
          ORM::for_table('recall_permalinks')->where($id_field, $id_value)->delete_many();
          $item->delete();
          $this->count_all_items(true);
          return $this->admin_dashboard("$thing deleted.");
        } else {
          return $this->admin_dashboard('', array('errors'=>array("$thing not found.")));
        }
        break;
      case 'save_recall':
        $result = $this->rm->save($_POST);
        if(!isset($result['errors']) ) {
          $this->count_all_items(true);
          return $this->admin_edit_recall($result['id'], 1, $result['message']);
        } else {
          return $this->render_view('admin/error', array('errors'=>$result['errors']));
        }
        break;
      default:
        return $this->admin_dashboard();
        break;
    }

  }

  function render_view($view, $vars = array()) {
    foreach ( $vars AS $key => $val ) {
      $$key = $val;
    }

    if ( file_exists( "{$this->plugin_base}/views/$view.php" ) ) {
      include "{$this->plugin_base}/views/$view.php";
    } else {
      echo "<p>Rendering view {$this->plugin_base}/views/$view.php failed</p>";
    }
  }


}

$recall_migrator = new RecallMigrator();



/*
function trk_register_post_type() {

  $singular = 'Recall';
  $plural = 'Recalls';

  $labels = array(
    'name'               => $plural,
    'singular_name'      => $singular,
    'add_name'           => 'Add New',
    'add_new_item'       => 'Add New' . $singular,
    'edit'               => 'Edit',
    'edit_item'          => 'Edit ' . $singular,
    'new_item'           => 'New ' . $singular,
    'view'               => 'View ' . $singular,
    'view_item'          => 'View ' . $singular,
    'search_term'        => 'Search ' . $plural,
    'parent'             => 'Parent ' . $singular,
    'not_found'          => 'No ' . $plural . ' found',
    'not_found_in_trash' => 'No ' . $plural . ' in Trash'
  );

  $args = array(
    'labels'              => $labels,
    'plubic'              => true,
    'plubic_queryable'    => true,
    'exclude_from_search' => false,
    'show_in_nav_menus'   => true,
    'show_ui'             => true,
    'show_in_menu'        => true,
    'show_in_admin_bar'   => true,
    'menu_position'       => 6,
    'menu_icon'        => 'dashicons-image-rotate',
    'can_export'          => true,
    'delete_with_user'    => false,
    'hierarchical'        => false,
    'has_archive'         => true,
    'query_var'           => true,
    'capability_type'     => 'page',
    'map_meta_cap'        => true,
    'reqrite' => array(
      'slug'        => 'recalls',
      'with_front'  => true,
      'pages'       => true,
      'feeds'       => true
    ),
    'supports' => array(
      'title',
      'editor',
      'author',
      'custom_fields'
    )

  );

  register_post_type( 'recall', $args);
}
add_action( 'init', 'trk_register_post_type');
*/
