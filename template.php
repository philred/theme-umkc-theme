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
function umkc_theme_preprocess_page($variables) {

// Only if an islandora object
  if ($object = menu_get_object('islandora_object', 2)) {

    $object_model = 'islandora:collectionCModel';
    $object_content_models = $object->relationships->get('info:fedora/fedora-system:def/model#', 'hasModel');
  
//    dsm($object_content_models, 'models');
  
// Only if a collection model
    if ($object_content_models['0']['object']['value'] == $object_model) {
//      dsm($object, 'object');

      dsm($object['DC']->content, 'metadata');
  
//      $objects_dc_array = array(); 
  
      if (isset($object['DC']))
      {
        try {
          $dc = $object['DC']->content;
          $object_dc = simplexml_load_string($dc);
 //         $objects_dc_array[$pid]['dc_array'] = isset($object_dc) ? DC::as_formatted_array($object_dc) : array();
        } catch (Exception $e) {
          drupal_set_message(t('Error retrieving object %s %t', array('%s' => $islandora_object->id, '%t' => $e->getMessage())), 'error', FALSE);
        }
      }
    }
  }
}
