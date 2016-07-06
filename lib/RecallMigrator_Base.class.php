<?php

if ( !function_exists( 'add_action' ) ) {
  echo 'Direct access denied.';
  exit;
}

class RecallMigrator_Base {

  public $per_page = 25;

  function manager() {
    if( !is_admin() && !is_user_logged_in() ) {
    }
  }

  function url_for($action, $query_string = '', $page = 0) {
    $url = sprintf('admin.php?page=%s&recall_migrator_action=%s', $this->plugin_file, $action);
    if( $query_string != '' ) {
      $url .= "&$query_string";
    }
    $page = intval($page);
    if( $page > 0 ) {
      $url .= "&page=$page";
    }
    return $url;
  }

  function admin_dashboard($message = '', $errors = array()) {
    $vars = array();
    $vars['counts'] = $this->count_all_items();
    $vars['message'] = $message;
    if(count($errors) > 0) {
      $vars['errors'] = $errors;
    }
    return $this->render_view('admin/dashboard', $vars);
  }

  function admin_search($query, $type, $page = 1) {
    $vars = array('query'=>$query, 'type'=>$type, 'page' => $page);
    switch($type) {
      case 'recall':
        $results = $this->rm->search($query, $page);
        $vars['orm'] = $this->rm;
        break;
      // case 'city':
      //   $results = $this->mm->search($query, $page);
      //   $vars['orm'] = $this->mm;
      //   break;
    }

    $vars = array_merge($results, $vars);
    return $this->render_view('admin/search_results', $vars);
  }

  function admin_edit_recall($recall_id, $page = 1, $message = '') {
    $vars = array();
    if( $recall_id ) {
      $recall = $this->rm->find($recall_id);
      $vars['redirects'] = $this->rm->redirect_list($recall_id);
    } else {
      $recall = $this->rm->orm()->create();
      $recall->name = 'New Recall';
    }
    $vars['recall'] = $recall;
    $vars['message'] = $message;
    return $this->render_view('admin/edit_recall', $vars);
  }

  function count_all_items($force = false) {
    $counts = null;
    $t_name = 'item_counts';
    if($force) { delete_transient($t_name); }
    if( false === ($counts = get_transient($t_name) ) ) {
      global $wpdb;
      $counts = array();
      $recall = $wpdb->get_row('SELECT COUNT(*) as count FROM wp_recalls USE INDEX (PRIMARY)', ARRAY_A);
      $counts['recall'] = $recall['count'];
      set_transient($t_name, $counts, 60*60*1);
    }
    return $counts;
  }

  function activate() {
    //global $wpdb;
    $this->get_options();
  }

  function deactivate() {
    global $wpdb;
    // $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}recall_migrator_items;" );
    delete_option( 'recall_migrator_options' );
  }

  function get_options() {
    $options = get_option( 'recall_migrator_options' );
    if ( $options === false )
      $options = array();

    $defaults = array(
      // 'country_title_template' => 'Private Jet Charters to {country} | Priority One Jets',
      // 'state_title_template' => 'Private Jet Charters to {state} | Priority One Jets',
      // 'muni_title_template' => 'Private Jet Charters to {city} | Priority One Jets',
      'recall_title_template' => 'Recall Test Title Template',

      // 'country_header_template' => 'Private Jet Charters to {country}',
      // 'state_header_template' => 'Private Jet Charters to {state}',
      // 'muni_header_template' => 'Private Jet Charters to {city}',
      'recall_header_template' => 'Recall Test Header Template',

      // 'country_body_template' => '<p>Priority One Jets offers {country} Private Jet Charter and Air Charter Service. With access to over 5,000 aircraft worldwide, no flight is too small or too large to handle.  With just few hours-notice Priority One Jets is able to arrange any type of aircraft from {country} or to {country} regardless of the number of passengers.</p><p><strong>Book a Jet in {country}</strong><br>Priority One Jets has access to all jet types. Choose from any Citation, Hawker, Lear, Falcon, Challenger, Gulfstream, Global Express, Boeing Business Jet and even Jumbo Jets. Priority One Jets can arrange all your jet charter flights with just few hours. Feel free to call our account executives to book your next jet charter flight in {country}.</p><strong>Rent a Private Jet from {country} or to {country}</strong><br>Rent a jet and be ready to depart in just few hours. Priority One Jets can arrange your jet charter and air charter in {country}.<p><strong>On Demand Jet Charter in {country}</strong><br>Our on demand jet charter service in {country} allows you to depart when you want and on the jet that you want. Arrange your flights from {country} based on your own schedule. Avoid connecting stops and unnecessary layovers.</p><p><strong>Call now to book your next:</strong><br><br><strong>Charter Flight<br>Air Charter<br>Jet Charter<br>Private Jet {country}</strong></p><p><strong>Air Charter Service in {country}</strong><br>Let Priority One Jets handle all your air charter services from {country}. Our experts have numerous years in the industry and will make sure you travel on the right aircraft for your particular flight.</p><p><strong>Corporate Jet Charter in {country}</strong><br>Need to arrange your travel based on your own schedule? Corporate Jet Charter lets you travel when you want and avoid connecting flights as well as unnecessary layovers. Priority One Jets offers Corporate Jet Charter services in {country}, with access to over 5,000 aircraft worldwide you can be sure to travel on the right jet every time.</p>',
      // 'state_body_template' => '<p>Priority One Jets offers {state} Private Jet Charter and Air Charter Service. With access to over 5,000 aircraft worldwide, no flight is too small or too large to handle.  With just few hours-notice Priority One Jets is able to arrange any type of aircraft from {state} or to {state} regardless of the number of passengers.</p><p><strong>Book a Jet in {state}</strong><br>Priority One Jets has access to all jet types. Choose from any Citation, Hawker, Lear, Falcon, Challenger, Gulfstream, Global Express, Boeing Business Jet and even Jumbo Jets. Priority One Jets can arrange all your jet charter flights with just few hours. Feel free to call our account executives to book your next jet charter flight in {state}.</p><strong>Rent a Private Jet from {state} or to {state}</strong><br>Rent a jet and be ready to depart in just few hours. Priority One Jets can arrange your jet charter and air charter in {state}.<p><strong>On Demand Jet Charter in {state}</strong><br>Our on demand jet charter service in {state} allows you to depart when you want and on the jet that you want. Arrange your flights from {state} based on your own schedule. Avoid connecting stops and unnecessary layovers.</p><p><strong>Call now to book your next:</strong><br><br><strong>Charter Flight<br>Air Charter<br>Jet Charter<br>Private Jet {state}</strong></p><p><strong>Air Charter Service in {state}</strong><br>Let Priority One Jets handle all your air charter services from {state}. Our experts have numerous years in the industry and will make sure you travel on the right aircraft for your particular flight.</p><p><strong>Corporate Jet Charter in {state}</strong><br>Need to arrange your travel based on your own schedule? Corporate Jet Charter lets you travel when you want and avoid connecting flights as well as unnecessary layovers. Priority One Jets offers Corporate Jet Charter services in {state}, with access to over 5,000 aircraft worldwide you can be sure to travel on the right jet every time.</p>',
      // 'muni_body_template' => '<p>Priority One Jets offers {city} Private Jet Charter and Air Charter Service. With access to over 5,000 aircraft worldwide, no flight is too small or too large to handle.  With just few hours-notice Priority One Jets is able to arrange any type of aircraft from {city} or to {city} regardless of the number of passengers.</p><p><strong>Book a Jet in {city}</strong><br>Priority One Jets has access to all jet types. Choose from any Citation, Hawker, Lear, Falcon, Challenger, Gulfstream, Global Express, Boeing Business Jet and even Jumbo Jets. Priority One Jets can arrange all your jet charter flights with just few hours. Feel free to call our account executives to book your next jet charter flight in {city}.</p><strong>Rent a Private Jet from {city} or to {city}</strong><br>Rent a jet and be ready to depart in just few hours. Priority One Jets can arrange your jet charter and air charter in {city}.<p><strong>On Demand Jet Charter in {city}</strong><br>Our on demand jet charter service in {city} allows you to depart when you want and on the jet that you want. Arrange your flights from {city} based on your own schedule. Avoid connecting stops and unnecessary layovers.</p><p><strong>Call now to book your next:</strong><br><br><strong>Charter Flight<br>Air Charter<br>Jet Charter<br>Private Jet {city}</strong></p><p><strong>Air Charter Service in {city}</strong><br>Let Priority One Jets handle all your air charter services from {city}. Our experts have numerous years in the industry and will make sure you travel on the right aircraft for your particular flight.</p><p><strong>Corporate Jet Charter in {city}</strong><br>Need to arrange your travel based on your own schedule? Corporate Jet Charter lets you travel when you want and avoid connecting flights as well as unnecessary layovers. Priority One Jets offers Corporate Jet Charter services in {city}, with access to over 5,000 aircraft worldwide you can be sure to travel on the right jet every time.</p>',
      'recall_body_template' => '<h3>Recall Test Body Template</h3><p>test</p>'
    );

    $defaults_set = false;
    foreach ( $defaults AS $key => $value ) {
      if ( !isset( $options[$key] ) ) {
        $options[$key] = $value;
        $defaults_set = true;
      }
    }

    if( $defaults_set )
      update_option( 'recall_migrator_options', $options );

    return $options;
  }

  function admin_edit_template($type) {

    $vars = array();
    $options = get_option('recall_migrator_options');
    $vars['t_header'] = $options[$type.'_header_template'];
    $vars['t_title'] = $options[$type.'_title_template'];
    $vars['t_body'] = $options[$type.'_body_template'];
    $vars['template_type'] = $type;

    return $this->render_view('admin/edit_template', $vars);

  }

  function admin_save_template($postdata) {
    $type = $postdata['template_type'];

    $options = get_option('recall_migrator_options');
    $options[$type.'_header_template'] = $postdata['header'];
    $options[$type.'_title_template'] = $postdata['title'];
    $options[$type.'_body_template'] = $postdata['body'];

    update_option('recall_migrator_options', $options);

    delete_transient($type.'_header_template');
    delete_transient($type.'_title_template');
    delete_transient($type.'_body_template');

    return $this->admin_dashboard('Template saved.');

  }

  function version() {
    return RECALL_MIGRATOR_VERSION;
  }

  function pr($what) {
    echo "<pre>";
    print_r($what);
    echo "</pre>";
  }

}
