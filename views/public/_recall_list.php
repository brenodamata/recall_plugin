<br><h2>Recalls in <?php echo $name; ?></h2><br>
<ul class="recalls-info">
<?php
  $seen = array();
  foreach($recalls as $recall) :
    if( array_search($recall['ident'], $seen) === false ) :
?>
  <li>
    <span class="aname"><a href="<?php echo $recall['permalink']; ?>"><?php echo $recall['name']; ?></a></span>
    <span class="aident"><?php echo $recall['ident']; ?></span>
  </li>
<?php
    endif;
    array_push($seen, $recall['ident']);
  endforeach;
?>
</ul>
