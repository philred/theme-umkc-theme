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
  if ($fc_object = menu_get_object('islandora_object', 2)) {
    $pid = 'umkc:kmbc';
    $object_model = 'islandora:collectionCModel';
    $object_content_models = $fc_object->relationships->get('info:fedora/fedora-system:def/model#', 'hasModel');
    $associated_objects_mods_array = array(); 

// Only if a collection model
    if ($object_content_models['0']['object']['value'] == $object_model) {
      $associated_objects_mods_array[$pid]['object'] = $fc_object;

//      dsm($fc_object, 'object');

      if (isset($fc_object['DC']))
      {
        try {
          $mods = $fc_object['DC']->content;
          preg_match('<dc\:description>()</dc:description>\1', $mods, $mods_d);
//          $mods_object = simplexml_load_string($mods);
          $variables['test1'] = $mods_d;

      dsm($mods, 'metadata');

	        $associated_objects_mods_array[$pid]['mods_array'] = isset($mods_object) ? ($mods_object) : array();
        } catch (Exception $e) {
          drupal_set_message(t('Error retrieving object %s %t', array('%s' => $islandora_object->id, '%t' => $e->getMessage())), 'error', FALSE);
        }
      }

      $object_url = 'islandora/object/' . $pid;
	    $thumbnail_img = '<img src="' . $GLOBALS['base_url'] . $object_url . '/datastream/TN/view"' . '/>';
	    $title = $fc_object[0]['title']['value'];

      if (isset($fc_object['DESC-TEXT'])) {
        $description_text = $fc_object['DESC-TEXT']->content;
      }

      $associated_objects_mods_array[$pid]['pid'] = $pid;
      $associated_objects_mods_array[$pid]['path'] = $object_url;
      $associated_objects_mods_array[$pid]['title'] = $title;
      $associated_objects_mods_array[$pid]['class'] = drupal_strtolower(preg_replace('/[^A-Za-z0-9]/', '-', $pid));

      if (isset($fc_object['TN'])) {
        $thumbnail_img = '<img src="' . $GLOBALS['base_path'] . $object_url . '/datastream/TN/view"' . '/>';
      }

      $associated_objects_mods_array[$pid]['thumbnail'] = $thumbnail_img;
      $associated_objects_mods_array[$pid]['title_link'] = l($title, $object_url, array('html' => TRUE, 'attributes' => array('title' => $title)));
      $associated_objects_mods_array[$pid]['thumb_link'] = l($thumbnail_img, $object_url, array('html' => TRUE, 'attributes' => array('title' => $title)));
       $associated_objects_mods_array[$pid]['collection_description'] = $description_text;    

      dsm($associated_objects_mods_array, 'object');
      dsm($variables, 'vars');

    }
  }

  $variables['associated_objects_mods_array'] = $associated_objects_mods_array;
}
