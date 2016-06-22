<?php

require_once __DIR__.'/RM_Model.class.php';
class AM_Permalink extends RM_Model {

  public function RM_Permalink() {
    $this->table = 'recall_permalinks';
  }

  public function find($id) {
    return $this->orm()->find_one($id);
  }
  // 
  // public function all_by_country($id, $page) {
  //   $o = $this->orm()->where('country_id', $id);
  //   return $o->find_many();
  // }
  //
  // public function all_by_airport($id, $page) {
  //   $o = $this->orm()->where('airport_id', $id);
  //   return $o->find_many();
  // }
  //
  // public function all_by_state($id, $page) {
  //   $o = $this->orm()->where('state_id', $id);
  //   return $o->find_many();
  // }
  //
  // public function all_by_muni($id, $page) {
  //   $o = $this->orm()->where('muni_id', $id);
  //   return $o->find_many();
  // }


  public function all($page = 0) {
    $o = $this->orm();
    return $o->find_many();
  }

}
