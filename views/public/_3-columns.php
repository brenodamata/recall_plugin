<br class="clear">
<?php 
$total = count($bucket);
$offset = ($total % 3 == 0) ? '0' : '0.5';
$max = round( $total/3.0 + $offset );
$b1 = array();
$b2 = array();
$b3 = array();
foreach($bucket as $drop) {
  if(count($b1) < $max) {
    array_push($b1, $drop);
  } else if(count($b2) < $max) {
    array_push($b2, $drop);
  } else {
    array_push($b3, $drop);
  }
}
?>
<ul class="list-column">
  <?php echo implode('', $b1); ?>
</ul>
<ul class="list-column">
  <?php echo implode('', $b2); ?>
</ul>
<ul class="list-column">
  <?php echo implode('', $b3); ?>
</ul>