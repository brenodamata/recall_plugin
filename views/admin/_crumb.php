<?php
$crumbs = array();

if(isset($crumb_recall)) {
  array_push($crumbs, $crumb_recall->name);
}


array_push($crumbs, array($this->url_for('admin_dashboard'), 'Recall Migrator'));

?>
<div class="crumbs">
  <?php foreach(array_reverse($crumbs) as $crumb) :
    if( is_array($crumb) ) : ?>
      <a href="<?php echo $crumb[0]; ?>"><?php echo htmlentities($crumb[1]); ?></a> &gt;
    <?php else : ?>
      <?php echo $crumb; ?>
    <?php endif; ?>
  <?php endforeach; ?>
</div>
