<br>
<table class="wp-list-table widefat fixed search_results">
<thead>
  <tr>
    <th>Redirect List</th>
  </tr>
</thead>
<tbody>
<?php foreach($redirects as $redirect) : ?>
<tr>
  <td><input type="text" style="width: 100%;" name="redirect_<?php echo $redirect->id();?>" value="<?php echo htmlentities($redirect->redirect_from);?>"></td>
</tr>
<?php endforeach; ?>
<?php if(count($redirects) == 0) :?><tr><td>No active redirects</td></tr><?php endif; ?>
<tr>
  <td><h3>Add New Redirect</h3><input type="text" style="width: 100%;" name="new_redirect" value=""></td>
</tr>
</tbody>
</table>
<br class="clear">