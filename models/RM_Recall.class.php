<?php

require_once __DIR__.'/AM_Model.class.php';
class AM_Airport extends AM_Model {

  public function AM_Airport() {
    $this->table = 'airportsinfo';
  }

  public function find($id) {
    return $this->orm()->find_one($id);
  }

  public function find_permalink($id) {
    $o = ORM::for_table('recall_permalinks')->select('permalink')->where('recall_id', $id)->limit(1)->find_one();
    if($o) {
      return $o->permalink;
    } else {
      return '';
    }
  }

  public function redirect_list($id) {
    return ORM::for_table('recall_permalinks')->select('id')->select('redirect_from')->where_not_equal('redirect_from','')->where('recall_id', $id)->find_many();
  }

  public function search($query, $page = 1) {
    $q = $this->like($query);
    $where = 'ident like ? or name like ? or gps_code like ? or local_code like ? or home_link like ?';
    $perpage = 25;

    $search = $this->orm()->select('id')->select('name')->where_raw($where,
      array($q,$q,$q,$q,$q) )->order_by_asc('name');
    $this->paginate($search, $page, $perpage);

    $total = $this->orm()->select('id')->select('name')->where_raw($where,
      array($q,$q,$q,$q,$q) )->count();

    return array('rows' => $search->find_many(), 'total' => $total, 'perpage' => $perpage);
  }

  public function save($postdata) {

    $errors = array();
    $create = false;

    if(isset($postdata['recall_id']) && intval($postdata['recall_id'] > 0)) {
      $recall = $this->find($postdata['recall_id']);
      if($recall === false) {
        return array('errors'=>array('Unable to locate recall'));
      }
    } else { // new record
      $recall = $this->orm()->create();
      $create = true;
    }

    $errors = $this->validate($postdata,
      array(
        // 'name' => 'Name cannot be blank',
        // 'iso_region' => 'Region code cannot be blank',
        // 'iso_country' => 'You must select a country',
        // 'latitude_deg' => 'Latitude is required',
        // 'longitude_deg' => 'Longitude is required',
        // 'type' => 'Airport type is required',
        // 'ident' => 'Airport IDENT code required',
        // 'local_code' => 'Airport LOCAL code required',
        // 'iata_code' => 'Airport IATA code required',
        // 'continent' => 'Continent cannot be blank'
        )
      );

    if(count($errors) > 0) {
      return array('errors'=>$errors);
    }

    $recall->title = $postdata['title'];
    // $recall->content = $postdata['content'];
    $recall->description = $postdata['description'];
    // $recall->header = $postdata['header'];
    // $recall->name = $postdata['name'];
    // $recall->us_state_id = $postdata['us_state_id'];
    // $recall->municipality_id = $postdata['municipality_id'];
    // $recall->iso_country = $postdata['iso_country'];
    // $recall->iso_region = $postdata['iso_region'];
    $recall->url = $postdata['url'];

    // $recall->ident = $postdata['ident'];
    // $recall->type = $postdata['type'];
    // $recall->latitude_deg = $postdata['latitude_deg'];
    // $recall->longitude_deg = $postdata['longitude_deg'];
    // $recall->elevation_ft = $postdata['elevation_ft'];
    // $recall->continent = $postdata['continent'];
    // $recall->scheduled_service = $postdata['scheduled_service'];
    // $recall->gps_code = $postdata['gps_code'];
    // $recall->iata_code = $postdata['iata_code'];
    // $recall->local_code = $postdata['local_code'];
    // $recall->home_link = $postdata['home_link'];

    $permalink = trim(strtolower($_POST['permalink']));

    if( strlen($permalink) == 0 ) {
      var_dump('perma');
      $permalink = strtolower($recall->name);
      $permalink = preg_replace('/ +/', '-', $permalink);
      $permalink = preg_replace('/[^a-z0-9\-]/', '', $permalink);
      //
      // $mm = new AM_City();
      // if( intval($recall->municipality_id) > 0 ) {
      //   $city = $mm->find($recall->municipality_id);
      //   $root = $mm->find_permalink($city->id());
      // } else {
      //   $root = 'CITY_NOT_YET_SELECTED';
      // }
      // $permalink = $root . '/' . $permalink;
    } else {
      $permalink = $_POST['permalink'];
    }


    $pl = ORM::for_table('recall_permalinks')->where('permalink', $permalink);
    if(!$create) {
      $pl->where_not_equal('recall_id', $recall->id()); // don't match own links
    }
    if( count($pl->find_many()) > 0 ) {
      return array('errors'=>array('Permalink conflict, please change the permalink.'));
    }

    $recall->save();

    if( $create ) {
      $pl = ORM::for_table('recall_permalinks')->create();
      $pl->permalink = $permalink;
      $pl->recall_id = $recall->id();
      $pl->save();
    } else {
      $permalinks = ORM::for_table('recall_permalinks')->where('recall_id', $recall->id())->find_many();
      foreach($permalinks as $pl) {
        $pl->permalink = $permalink;
        $pl->save();
      }
    }

    $this->update_redirects($postdata, 'recall_id', $recall->id(), $permalink);

    return array('id'=>$recall->id(), 'message'=>'Saved recall.');

  }

  //
  // public function all_by_city($city_id, $page = 1) {
  //   $count = $this->orm()->where('municipality_id', $city_id)->count();
  //   $o = $this->orm()->where('municipality_id', $city_id);
  //   $this->paginate($o, $page, $this->perpage);
  //   return array('rows' => $o->find_many(), 'total' => $count, 'page' => $page, 'perpage' => $this->perpage);
  // }

  public function all() {
    return $this->orm()->order_by_asc('name')->find_many();
  }

}
