<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
  <?php screen_icon('tools'); ?>
  <h2><?php _e ('Recall Migrator Dashboard', 'recall_migrator'); ?></h2>
  <ul class="subsubsub">
    <li>Recalls ( <?php echo $counts['recall']; ?> ) |</li>
    <li> | </li>
    <li>
      <strong>Add new: </strong>
      <a href="<?php echo $this->url_for('edit_recall');?>">Recall</a>
    </li>
  </ul><br class="clear">
  <ul class="subsubsub edit_template">
    <li>
      <strong>Edit content template: </strong>
      <a href="<?php echo $this->url_for('edit_template');?>&template_type=recall">Recall</a>
    </li>
  </ul>
  <?php $this->render_view('admin/_tiny_search'); ?>
  <br class="clear">
  <?php include('_notifications.php'); ?>

</div>
