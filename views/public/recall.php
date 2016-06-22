<?php

  $use_title = (strlen(trim($recall->title)) == 0) ? $generated_title : $recall->title;
  $use_header = (strlen(trim($recall->header)) == 0) ? $generated_header : $recall->header;
  $use_content = (strlen(trim($recall->content)) == 0) ? $generated_content : $recall->content;
  $has_unique = !(strlen(trim($recall->content)) == 0);

  echo $this->process_header( $use_title, $recall->description, $recall->keywords );

  $crumbs = array();
  array_push($crumbs, $recall->name);
  array_push($crumbs, array($muni->name, $this->mm->find_permalink($muni->id)));

  $data = array('crumbs' => $crumbs, 'recall' => $recall, 'content' => $use_content, 'title' => $use_title, 'header' => $use_header);
  $this->render_view('public/_recall', $data);
  // if (!$has_unique) {
  //   header(sprintf('Link: <%s%s>; rel="canonical"', get_site_url(), $muni_url));
  // }
  get_footer();
?>
