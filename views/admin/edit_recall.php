<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>
<div class="wrap">
  <?php screen_icon('tools'); ?>
  <h2><?php _e ('Recall Migrator', 'recall_migrator'); ?>—Edit | <?php echo htmlentities($recall->name);?></h2>
  <?php $this->render_view('admin/_tiny_search'); ?>
  <?php $this->render_view('admin/_crumb', array('crumb_recall'=>$recall));?>
  <?php include('_notifications.php'); ?>
  <br class="clear">
  <form action="<?php echo $this->url_for('save_recall');?>" method="post" name="post" id="post">
    <input type="hidden" name="recall_id" value="<?php echo $recall->id;?>">
    <div id="poststuff">
      <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
          <div class="titlediv">
            <div class="titlewrap">
              <span>Name</span>
              <input type="text" name="name" class="title" value="<?php echo htmlentities($recall->name); ?>">
            </div>
          </div>
          <div class="titlediv">
            <div class="titlewrap">
              <span>Page Title (&lt;title&gt;)</span>
              <input type="text" name="title" class="title" value="<?php echo htmlentities($recall->title); ?>" placeholder="Leave blank to use automatic title">
            </div>
          </div>
          <div class="titlediv">
            <div class="titlewrap">
              <span>Header (&lt;h1&gt;)</span>
              <input type="text" name="header" class="title" value="<?php echo htmlentities($recall->header); ?>" placeholder="Leave blank to use automatic header">
            </div>
          </div>
          <div id="edit-slug-box" class="hide-if-no-js">
            <strong>Permalink:</strong>
              <span id="sample-permalink" tabindex="-1"><?php echo get_site_url(); ?>
                <span class="editable-post-name"><input name="permalink" placeholder="Leave blank to auto generate slug" type="text" size="100" id="new-post-slug" value="<?php echo $this->rm->find_permalink($recall->id());?>">/</span>
              </span>
          ‎</div>
          <div id="postdivrich" class="postarea edit-form-section">
            <?php wp_editor($recall->content, 'content'); ?>
          </div>
          <?php include('_redirects.php');?>
        </div>
        <div id="postbox-container-1" class="postbox-container">
          <div class="postbox">
            <h3 class="hndle"><span>Meta Description</span></h3>
            <div class="inside">
              <textarea name="description" id="description" placeholder="Leave blank to use automatic meta description"><?php echo $recall->description; ?></textarea>
            </div>
          </div>
          <div class="postbox">
            <h3 class="hndle"><span>Database Fields</span></h3>
            <div class="inside">
              <strong>URL</strong>
              <input type="text" name="url" value="<?php echo htmlentities($recall->url); ?>" size="20">
              <br>
            </div>
          </div>
          <div class="postbox">
            <h3 class="hndle"><span>Details</span></h3>
            <div class="inside">
              <div class="misc-pub-section"><label for="post_status">Last Updated:</label>
              <span id="post-status-display">
              <?php echo $recall->last_updated; ?></span>
              </div>
              <?php if($recall->error_status != '') : ?>
              <div class="misc-pub-section">
              This record was marked as having the following problem:<br>
              <span id="post-status-display" class="am_error">
                <?php echo $recall->error_status; ?><br>
              </span>
              </div>
              <?php endif; ?>
              <div class="submitbox">
                <div id="major-publishing-actions">
                  <div id="delete-action">
                    <a class="submitdelete deletion" href="<?php echo $this->url_for('delete_recall', 'recall_id='.$recall->id);?>" onclick="return confirm('This action is permanent, are you *sure*?');">Delete</a>
                  </div>
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
