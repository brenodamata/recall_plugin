<div class="main">
  <div class="crumbs">
    <a href="/">Home</a> &gt;
    <!-- <a href="/recalls/">Recalls</a> &gt; -->
    <?php foreach(array_reverse($crumbs) as $crumb) :
      if( is_array($crumb) ) : ?>
        <a href="<?php echo $crumb[1]; ?>"><?php echo $crumb[0]; ?></a> &gt;
      <?php else : ?>
        <?php echo $crumb; ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
  <div class="main-right">
    <div class="entry">
      <h1><?php echo $header ?></h1>
      <br>
      <?php
        $content = (strpos($content, '<p>') === false ) ? wpautop($content) : $content;
        $content = preg_replace('/<h1>(.*?)<\/h1>/s', '', $content);
       echo $content;  ?>
      <br><br>
      <?php
        if(isset($recalls)) {
          $this->render_view('public/_recall_list', array('airports'=> $recalls, 'name' => $name));
        }
      ?>
    </div>
  </div>
  <div class="main-left">
    <?php get_sidebar('left'); ?>
  </div>
</div>
<div id="sidebar">
  <?php get_sidebar('right'); ?>
</div>
