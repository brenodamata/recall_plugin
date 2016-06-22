<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
  <?php screen_icon('tools'); ?>
  <h2><?php _e ('Recall Migrator', 'recall_migrator'); ?>—Edit Template | <?php echo strtoupper($template_type);?></h2>
  <?php $this->render_view('admin/_tiny_search'); ?>
  <?php include('_notifications.php'); ?>
  <br class="clear">
  <form action="<?php echo $this->url_for('save_template');?>" method="post" name="post" id="post">
    <input type="hidden" name="template_type" value="<?php echo $template_type;?>">
    <div id="poststuff">
      <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
          <div class="titlediv">
            <div class="titlewrap">
              <span>Title Template (&lt;title&gt;)</span>
              <input type="text" name="title" class="title" value="<?php echo htmlentities($t_title); ?>">
            </div>
          </div>
          <div class="titlediv">
            <div class="titlewrap">
              <span>Header Template (&lt;h1&gt;)</span>
              <input type="text" name="header" class="title" value="<?php echo htmlentities($t_header); ?>">
            </div>
          </div>
          <div id="postdivrich" class="postarea edit-form-section">
            <?php wp_editor($t_body, 'body'); ?>
          </div>
        </div>
        <div id="postbox-container-1" class="postbox-container">
          <div class="postbox">
            <h3 class="hndle"><span>Details</span></h3>
            <div class="inside">
              <div class="submitbox">
                <div id="major-publishing-actions">
                  <div id="publishing-action">
                    <span class="spinner"></span>
                    <input name="save" type="submit" class="button button-primary button-large" id="publish" accesskey="p" value="Update">
                  </div>
                  <div class="clear"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <br class="clear"><br>


</div>
