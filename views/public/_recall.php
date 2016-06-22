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
      <h1><?php echo $header; ?></h1>
      <ul class="id-list">
        <li><span class="col">Ident</span><span class="co2"><?php echo $recall->ident; ?></span></li>
        <li><span class="col">Type</span><span class="co2"><?php echo ucwords(str_replace('_', ' ', $recall->type)); ?></span></li>
        <li><span class="col">Name</span><span class="co2"><?php echo $recall->name; ?> </span></li>
      </ul>

      <div class="airport-description">
        <?php echo $content; ?>
      </div>
      <iframe width="450" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=<?php echo urlencode($recall->name); ?>&amp;aq=0&amp;ie=UTF8&amp;hq=&amp;hnear=<?php echo urlencode($recall->name); ?>&amp;t=k&amp;ll=<?php echo $recall->latitude_deg; ?>,<?php echo $recall->longitude_deg; ?>&amp;z=12&amp;iwloc=&amp;spn=0.08,0.08&amp;output=embed"></iframe>

    </div>
  </div>
  <div class="main-left">
    <?php get_sidebar('left'); ?>
  </div>
</div>
<div id="sidebar">
  <?php get_sidebar('right'); ?>
</div>
