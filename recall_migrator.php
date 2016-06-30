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
include __DIR__.'/models/RM_Permalink.class.php';

//Exit if accessed directly
if ( ! defined( 'ABSPATH') ) {
  exit;
}

if ( class_exists( 'RecallMigrator' ) )
  return;

define( 'RECALL_MIGRATOR_VERSION', '1.0' );
define( 'RECALL_MIGRATOR_CACHE_DURATION', 60*60*1 ); // 1 hour

// Hook triggered when plugin is installed
register_activation_hook( __FILE__, 'trk_jal_install' );
add_action('init', 'trk_recall_data');

// Create Recalls and Permalinks tables if they doesn't already exists
function trk_jal_install() {
  global $wpdb;

  $table_name = $wpdb->prefix . "recalls";
  $table_name_pm = $wpdb->prefix . "recall_permalinks";
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    name tinytext NOT NULL,
    date datetime,
    description text NOT NULL,
    url varchar(255) DEFAULT '' NOT NULL,
    title varchar(255) DEFAULT '' NOT NULL,
    consumer_contact text,
    last_publish_date datetime,
    number_of_units varchar(55) DEFAULT '',
    injuries text,
    hazards text,
    remedies text,
    retailers text,
    country varchar(255) DEFAULT '',
    recallable_id mediumint(9),
    recallable_type varchar(55) DEFAULT '',
    UNIQUE KEY id (id)
  ) $charset_collate;";

  $sql_pm = "CREATE TABLE IF NOT EXISTS $table_name_pm (
    recall_id mediumint(9) NOT NULL,
    permalink varchar(225) NOT NULL
  ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta( $sql );
  dbDelta( $sql_pm );
}


function trk_recall_data() {
  // credentials and sql file info
  $db_name = 'wp_dev';
  $db_user = 'root';
  $db_pass = '';
  $db_host = 'localhost';
  $filename = __DIR__.'/test.sql';
  $max_runtime = 8; // less then your max script execution limit


  $deadline = time() + $max_runtime;
  $progress_filename = $filename.'_filepointer'; // tmp file for progress
  $error_filename = $filename.'_error'; // tmp file for erro

  // Connect to MySQL server (first is depracated)
  // mysql_connect($db_host, $db_user, $db_pass) OR die('connecting to host: '.$db_host.' failed: '.mysql_error());
  // Select database
  // mysql_select_db($db_name) OR die('select db: '.$dbName.' failed: '.mysql_error());
  // New method to connect
  $connection = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

  ($fp = fopen($filename, 'r')) OR die('failed to open file:'.$filename);

  // check for previous error
  if( file_exists($error_filename) ){
    die('<pre> previous error: '.file_get_contents($error_filename));
  }

  // activate automatic reload in browser
  echo '<html><head> <meta http-equiv="refresh" content="'.($max_runtime+2).'"><pre>';

  // go to previous file position
  $file_position = 0;
  if( file_exists($progress_filename) ){
    $file_position = file_get_contents($progress_filename);
    fseek($fp, $file_position);
  }

  $query_count = 0;
  $query = '';
  while( $deadline>time() AND ($line=fgets($fp, 1024000)) ) {
    if(substr($line,0,2)=='--' OR trim($line)=='' ){
      continue;
    } // Skip if it's a commented line

    $query .= $line;
    if( substr(trim($query),-1)==';' ){
      // if( !mysql_query($query) ){
      if( !mysqli_query($connection, $query) ){
        $error = 'Error performing query \'<strong>' . $query . '\': ' . mysql_error();
        file_put_contents($error_filename, $error."\n");
        exit;
      }
      $query = '';
      file_put_contents($progress_filename, ftell($fp)); // save the current file position for
      $query_count++;
    }
  }

  if( feof($fp) ){
      echo 'dump successfully restored!';
  }else{
      echo ftell($fp).'/'.filesize($filename).' '.(round(ftell($fp)/filesize($filename), 2)*100).'%'."\n";
      echo $query_count.' queries processed! please reload or wait for automatic browser refresh!';
  }

}

class RecallMigrator extends RecallMigrator_Base {

  var $plugin_base = '';
  var $plugin_file = '';
  var $plugin_path = '';
  var $rm = null; // Recalls
  var $pm = null; // Permalinks

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
    }

    add_action('parse_request', array(&$this, 'recall_rewrite'));

    // MicroORMs:
    $this->rm = new RM_Recall();
    $this->pm = new RM_Permalink();

  }

  function recall_rewrite(){

    $ary = explode('?', $_SERVER['REQUEST_URI']);
    $uri = $ary[0];

    if( stripos($uri, '/recalls/') === 0 ) {
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
        if( $item['recall_id'] ) {
          /* ############################### RECALL ################################### */
          $recall = $this->rm->find($item['recall_id']);
          $vars = array('permalink' => $item, 'recall' => $recall);
          $view = '$recall';
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
      $opts = get_option('recall_migrator_options');
      $transient = $opts[$key];
      set_transient($key, $transient, RECALL_MIGRATOR_CACHE_DURATION);
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

  function search_for_permalink($uri) {
    global $wpdb;
    $where = 'permalink';
    $fields = 'permalink, recall_id';
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
