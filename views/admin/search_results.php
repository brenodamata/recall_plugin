<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
  <?php screen_icon('tools'); ?>
  <h2><?php _e ('Recall Migrator', 'recall_migrator'); ?>—Search Results | <?php echo htmlentities($query);?></h2>
  <?php $this->render_view('admin/_tiny_search'); ?>
  <br class="clear">
  <?php $this->render_view('admin/_paginate', array('action' => 'search', 'query'=>'rm_query='.$query.'&rm_type='.$type, 'total'=>$total, 'perpage'=>$perpage, 'page' => $page)); ?>
  <table class="wp-list-table widefat fixed search_table">
  <tr>
    <th width="30%">Match</th>
    <th>Permalink</th>
    <th width="15%">Actions</th>
  </tr>
  <?php foreach( $rows as $item ) : $pl = $orm->find_permalink($item->id()); ?>
  <tr>
    <td><?php echo htmlentities($item->name);?></td>
    <td><strong><?php echo htmlentities($pl);?></strong></td>
    <td>
      <a href="<?php echo $pl;?>" target="_blank">View</a> |
      <a href="<?php echo $this->url_for('edit_'.$type);?>&<?php echo $type;?>_id=<?php echo $item->id();?>">Edit</a>
    </td>
  </tr>
  <?php endforeach; ?>
  </table>
  <?php $this->render_view('admin/_paginate', array('action' => 'search', 'query'=>'rm_query='.$query.'&rm_type='.$type, 'total'=>$total, 'perpage'=>$perpage, 'page' => $page)); ?>
  <br class="clear"><br>
</div>
