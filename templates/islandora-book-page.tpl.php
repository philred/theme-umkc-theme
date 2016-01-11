<?php
/**
 * @file
 * Template file to style output.
 */
?>
<?php print $link_content['pages_view']; ?>
<?php print $link_content['download_link']; ?>
<?php print $link_content['print_link']; ?>
<?php print $link_content['persistent_url']; ?>
<?php print $link_content['persistent_url_popout']; ?>
<div class="clearfix"></div>
<?php if (isset($viewer)): ?>
  <div id="book-page-viewer">
    <?php print $viewer; ?>
  </div>
<?php elseif (isset($object['JPG']) && islandora_datastream_access(ISLANDORA_VIEW_OBJECTS, $object['JPG'])): ?>
  <div id="book-page-image">
    <?php
      $params = array(
        'path' => url("islandora/object/{$object->id}/datastream/JPG/view"),
        'attributes' => array(),
      );
      print theme('image', $params);
    ?>
  </div>
<?php endif; ?>
<!-- @todo Add table of metadata values -->
