<?php

class RM_Model {

  protected $table = null;
  protected $perpage = 25;

  protected function paginate($object, $page, $perpage = 25) {
    if( $page == -1 || $perpage == -1 ) {
      return $object;
    }
    if(intval($page) > 0 ) {
      $offset = ($page - 1) * $perpage;
      return $object->offset($offset)->limit($perpage);
    } else {
      return $object->offset(0)->limit($perpage);
    }
  }

  public function orm() {
    return ORM::for_table($this->table);
  }

  protected function validate($postdata, $values) {
    $errors = array();
    foreach($values as $k=>$v) {
      if( !isset($postdata[$k]) || trim($postdata[$k]) == '' ) {
        array_push($errors, $v);
      }
    }
    return $errors;
  }

  protected function like($thing) {
    return "%$thing%";
  }


  protected function update_redirects($postdata, $field, $id, $permalink) {
    $pl = new RM_Permalink();
    // Locate redirect_ fields
    foreach($postdata as $k=>$v) {
      if($this->starts_with($k, 'redirect_')) {
        $ary = explode('_', $k);
        $link = $pl->find($ary[1]);
        if(trim($v) != '') {
          if($link !== false) {
            $link->redirect_from = trim($v);
            $link->save();
          }
        } else {
          $link->delete();
        }
      }
    }

    if(isset($postdata['new_redirect']) && strlen(trim($postdata['new_redirect'])) > 0) {
      $link = $pl->orm()->create();
      $link->permalink = $permalink;
      $link->$field = $id;
      $link->redirect_from = trim($postdata['new_redirect']);
      $link->save();
    }

  }

  function starts_with($haystack, $needle) {
      return $needle === "" || strpos($haystack, $needle) === 0;
  }

}
