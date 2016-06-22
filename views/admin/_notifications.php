  <?php if(isset($message) && $message != '') : ?>
  <div class="updated"><ul><li><?php echo htmlentities($message); ?></li></ul></div>
  <?php endif; ?>
  <?php if(isset($errors) && is_array($errors) && count($errors) > 0) : ?>
  <div class="error"><ul>
    <?php foreach($errors as $error) : ?>
    <li><?php echo htmlentities($error); ?></li>
    <?php endforeach; ?>
  </ul></div>
  <?php endif; ?>
