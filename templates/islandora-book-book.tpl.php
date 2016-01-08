<?php
/**
 * @file
 * Template file to style output.
 */
?>
<?php print $content['download_link']; ?>
<?php print $content['print_link']; ?>
<?php print $content['persistent_url']; ?>
<?php print $content['persistent_url_popout']; ?>
<?php print $content['pages_view']; ?>
<div class="clearfix"></div>
<?php if(isset($viewer)): ?>
  <div id="book-viewer">
    <?php print $viewer; ?>
  </div>
<?php endif; ?>
<?php if($display_metadata === 1): ?>
  <div class="islandora-book-metadata">
    <?php print $description; ?>
  <?php print $metadata; ?>
  </div>
<?php endif; ?>
