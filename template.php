<?php
/**
 * @file
 * Contains the theme's functions to manipulate Drupal's default markup.
 *
 * Complete documentation for this file is available online.
 * @see https://drupal.org/node/1728096
 */

/**
 * Simple Search Advanced Link //
 */
function umkc_theme_form_islandora_solr_simple_search_form_alter(&$form, &$form_state, $form_id) {
  $link = array(
    '#markup' => l(t("Advanced Search"), "islandora-search", array('attributes' => array('class' => array('adv_search')))),
  );
  $form['simple']['advanced_link'] = $link;
}

/**
 *
 */
function umkc_theme_islandora_solr_facet_wrapper($variables) {
  $output = '<div class="islandora-solr-facet-wrapper">';
  $output .= '<h3 class="' . strtolower($variables['title']) . '">' . $variables['title'] . '</h3>';
  $output .= $variables['content'];
  $output .= '</div>';
  return $output;
}

/**
 * Add attribute to acknowledgements
 */
function umkc_theme_preprocess_html(&$variables, $hook) {
  if (request_path() == 'acknowledgments') {
    $keywords = array(
      '#tag' => 'meta',
      '#attributes' => array(
        'name' => 'keywords',
        'content' => 'National Endowment for the Humanities, NEH',
      ),
    );

    drupal_add_html_head($keywords, 'keywords');
  }
}

/**
 * Override page theme for specific objects
 */
function umkc_theme_preprocess_page(&$variables) {

// Only if an islandora object
  if ($islandora_object = menu_get_object('islandora_object', 2)) {

// Variables
    $temp_array = array(); 
//    $pid = 'umkc:kmbc';
    $pid = $islandora_object->id;
    $object_url = '/islandora/object/' . $pid;
    $thumbnail_img = '<img src="' . $GLOBALS['base_url'] . $object_url . '/datastream/TN/view"' . '/>';
    $object_model = 'islandora:collectionCModel';

    $object_content_models = $islandora_object->relationships->get('info:fedora/fedora-system:def/model#', 'hasModel');

	  foreach ($object_content_models as $model) {
	    $variables['theme_hook_suggestions'][] = 'page__islandora__object__' . str_replace(':', '_', $model['object']['value']);
	  }

// Only if a collection model
    if ($object_content_models['0']['object']['value'] == $object_model) {

      $metadata = $islandora_object['DC']->content;
      preg_match("/<dc:description>(.*)<\/dc:description>/", $metadata, $description);

      $temp_array['pid'] = $pid;
      $temp_array['description'] = $description;
      $temp_array['path'] = $object_url;
      $temp_array['thumbnail'] = $thumbnail_img;
      $temp_array['thumb_link'] = l($thumbnail_img, $object_url);

      $variables['islandora_object'] = $temp_array;

//      dsm($islandora_object, 'islandora object');
//      dsm($metadata, 'metadata object');
//      dsm($temp_array, 'custom array');
//      dsm($variables, 'variables array');
    }
  }

}
