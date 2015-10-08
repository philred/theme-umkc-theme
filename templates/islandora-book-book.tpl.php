<?php
/**
 * @file
 * Template file to style output.
 */
?>
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
