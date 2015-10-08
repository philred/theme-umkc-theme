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
 * Override global preprocess html
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
 * Override global page preprocess function
 */
function umkc_theme_preprocess_page(&$variables) {

// Only if an islandora object
  if ($islandora_object = menu_get_object('islandora_object', 2)) {

    $temp_array = array(); 
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
      preg_match("/<dc:description>([^<>]*)<\/dc:description>/", $metadata, $description);

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

/**
 * Override Islandora Solr Metadata Display preprocess function
 */
function umkc_theme_preprocess_islandora_solr_metadata_display(array &$variables) {
  module_load_include('inc', 'islandora_solr_metadata', 'includes/db');
  module_load_include('inc', 'islandora', 'includes/utilities');
  drupal_add_js('misc/form.js');
  drupal_add_js('misc/collapse.js');

  $object = $variables['islandora_object'];
  $object_content_models = $object->relationships->get('info:fedora/fedora-system:def/model#', 'hasModel');
  $object_model = 'islandora:collectionCModel';

  $db_fields = array();
  $solr_fields =& $variables['solr_fields'];
  $associations = $variables['associations'];

  if ($object_content_models['0']['object']['value'] != $object_model) {
    $variables['model'] = $object_content_models['0']['object']['value'];

    foreach ($object_content_models as $model) {
      $variables['theme_hook_suggestions'][] = 'islandora_solr_metadata_display_' . str_replace(':', '_', $model['object']['value']);
    }
  
    foreach ($associations as $configuration_id) {
      $field = islandora_solr_metadata_get_fields($configuration_id['configuration_id']);
      $db_fields = array_merge($db_fields, $field);
    }
    foreach ($db_fields as $solr_field => $value) {
      if (isset($solr_fields[$solr_field])) {
        continue;
      }
      // Make an array for use later on.
      $solr_fields[$solr_field] = $value + array(
        'value' => array(),
      );
    }
    $variables['parent_collections'] = islandora_get_parents_from_rels_ext($object);
	} else {
		$variables['model'] = $object_model;
	}
}
