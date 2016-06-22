<?php
  $pages = round(($total / $perpage) + 0.5);
  $html = '';
  for($count = 1; $count <= $pages; $count++) {
    $pages_html .= sprintf('<li %s><a href="%s">%d</a></li>', ($count == $page) ? 'class="current"' : '', $this->url_for($action, "paginate=$count&$query"), $count);
  }
?>
<div class="rm_pagination">
  <?php if($total > 0) : ?>
    <span>Page <?php echo "$page of $pages"; ?></span>
    <?php if($pages > 1) : ?>
    <ul class="rm_pagination"><?php echo $pages_html; ?></ul>
    <?php endif; ?>
  <?php else: ?>
   <span>No records to display</span>
  <?php endif; ?>
</div>
<br class="clear">
