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
  if ($islandora_object = menu_get_object('islandora_object', 2)) {
    $object_content_models = $islandora_object->relationships->get('info:fedora/fedora-system:def/model#', 'hasModel');
// If Large Image content model
    foreach ($object_content_models as $k => $v) {
      if ($object_content_models[$k]['object']['value'] == 'islandora:sp_large_image_cmodel') {
				$variables['print_css'] = '<style>@import url("http://dldev.lib.umsystem.edu/umkc/sites/all/themes/umkc-theme/css/print.css")</style>';
      }
    }
	}
  $pos = strpos($variables['menu_item']['page_callback'], 'islandora_print_object');
  if ($pos !== false) {
    $variables['attributes_array']['onload'] = 'window.print()';
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

    $object_content_models = $islandora_object->relationships->get('info:fedora/fedora-system:def/model#', 'hasModel');

		foreach ($object_content_models as $model) {
			$variables['theme_hook_suggestions'][] = 'page__islandora__object__' . str_replace(':', '_', $model['object']['value']);
		}

    foreach ($object_content_models as $k => $v) {
      if ($object_content_models[$k]['object']['value'] == 'islandora:bookCModel') {
        foreach ($variables['tabs']['#primary'] as $k2 => $v2) {
          if ($variables['tabs']['#primary'][$k2]['#link']['title'] == 'View') {
            unset($variables['tabs']['#primary'][$k2]);
          }
          elseif ($variables['tabs']['#primary'][$k2]['#link']['title'] == 'Pages') {
            unset($variables['tabs']['#primary'][$k2]);
          }
        }
      }
    }
// If Collection content model
    if ($object_content_models['0']['object']['value'] == 'islandora:collectionCModel') {

      $metadata = $islandora_object['DC']->content;
			$tabs = $variables['tabs'];
      preg_match("/<dc:description>([^<>]*)<\/dc:description>/", $metadata, $description);

      $temp_array['pid'] = $pid;
      $temp_array['description'] = $description;
      $temp_array['path'] = $object_url;
      $temp_array['thumbnail'] = $thumbnail_img;
      $temp_array['thumb_link'] = l($thumbnail_img, $object_url);

      foreach ($tabs['#primary'] as $key => $value) {
        if ($tabs['#primary'][$key]['#link']['title'] == 'View') {
          unset($tabs['#primary'][$key]);
        }
      }
      $variables['islandora_object'] = $temp_array;
      $variables['tabs'] = $tabs;
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

/**
 * Implements hook_preprocess().
 */
function umkc_theme_preprocess_islandora_large_image(&$variables) {
  drupal_add_js('misc/form.js');
  drupal_add_js('misc/collapse.js');
  $islandora_object = $variables['islandora_object'];
  $repository = $islandora_object->repository;
  module_load_include('inc', 'islandora', 'includes/datastream');
  module_load_include('inc', 'islandora', 'includes/utilities');
  module_load_include('inc', 'islandora', 'includes/metadata');

  // We should eventually remove the DC object and dc_array code as it only
  // exists to not break legacy implementations.
  if (isset($islandora_object['DC']) && islandora_datastream_access(ISLANDORA_VIEW_OBJECTS, $islandora_object['DC'])) {
    try {
      $dc = $islandora_object['DC']->content;
      $dc_object = DublinCore::importFromXMLString($dc);
    }
    catch (Exception $e) {
      $variables = array(
        '@ret' => $e->getMessage(),
        '@trace' => $e->getTraceAsString(),
      );
      watchdog('islandora_large_image', 'Error retrieving object.<br/>Error: @ret<br/>Trace: @trace', $variables, WATCHDOG_ERROR);
      drupal_set_message(t('Error retrieving object %s %t', array('%s' => $islandora_object->id, '%t' => $e->getMessage())), 'error', FALSE);
    }
  }
  $variables['islandora_dublin_core'] = isset($dc_object) ? $dc_object : NULL;
  $variables['dc_array'] = isset($dc_object) ? $dc_object->asArray() : array();
  $variables['islandora_object_label'] = $islandora_object->label;
  $variables['theme_hook_suggestions'][] = 'islandora_large_image__' . str_replace(':', '_', $islandora_object->id);
  $variables['parent_collections'] = islandora_get_parents_from_rels_ext($islandora_object);
  $variables['metadata'] = islandora_retrieve_metadata_markup($islandora_object);
  $variables['description'] = islandora_retrieve_description_markup($islandora_object);

  // Thumbnail.
  if (isset($islandora_object['TN']) && islandora_datastream_access(ISLANDORA_VIEW_OBJECTS, $islandora_object['TN'])) {
    $params = array(
      'title' => $islandora_object->label,
      'path' => url("islandora/object/{$islandora_object->id}/datastream/TN/view"),
    );
    $variables['islandora_thumbnail_img'] = theme('image', $params);
  }
  module_load_include('inc', 'islandora', 'includes/solution_packs');

  $params = array();

  if (isset($islandora_object['JP2']) && islandora_datastream_access(ISLANDORA_VIEW_OBJECTS, $islandora_object['JP2'])) {
    // Get token to allow access to XACML protected datastreams.
    // Always use token authentication in case there is a global policy.
    module_load_include('inc', 'islandora', 'includes/authtokens');
    $token = islandora_get_object_token($islandora_object->id, 'JP2', 2);
    $jp2_url = url("islandora/object/{$islandora_object->id}/datastream/JP2/view",
      array(
        'absolute' => TRUE,
        'query' => array('token' => $token),
      ));
    // Display large image.
    $params['jp2_url'] = $jp2_url;
  }

  $viewer = islandora_get_viewer($params, 'islandora_large_image_viewers', $islandora_object);
  $variables['islandora_content'] = '';
  if ($viewer) {
    if (strpos($viewer, 'islandora-openseadragon') !== FALSE) {
      if (isset($islandora_object['JP2']) && islandora_datastream_access(ISLANDORA_VIEW_OBJECTS, $islandora_object['JP2'])) {
        $url = url("islandora/object/{$islandora_object->id}", array('absolute' => TRUE));
// Download Link
        $variables['content']['download_link'] = l(
					'<img id="download_link_icon" src="/sites/all/themes/umkc-theme/images/download-icon-SpecialCollections.svg">',
          "islandora/object/{$islandora_object->id}/datastream/JPG/download",
          array(
						'html' => true,
            'attributes' => array(
              'title' => t('Download'),
              'id' => 'download_link',
            ),
          )
        );
// Print Link
        $variables['content']['print_link'] = l(
					'<img id="print_link_icon" src="/sites/all/themes/umkc-theme/images/printer-icon-SpecialCollections.svg">',
          "islandora/object/{$islandora_object->id}/print",
          array(
						'html' => true,
            'attributes' => array(
              'title' => t('Print'),
              'id' => 'print_link',
              'target' => '_blank',
            ),
          )
        );
// Persistent Link
        $variables['content']['persistent_url'] = l(
					'<img id="persistent_url_icon" src="/sites/all/themes/umkc-theme/images/link-icon-SpecialCollections.svg">',"javascript:toggle('toggleText');",
          array(
						'html' => true,
            'external' => true,
            'attributes' => array(
              'title' => t('Share Link'),
              'id' => 'persistent_url',
            ),
          )
        );
// Persistent Link popout
        $variables['content']['persistent_url_popout'] = '<div id="toggleText" style="display:none";><input value="' . $url . '" onclick="this.focus();this.select()" size="50"></div>';
      }
    }
    $variables['islandora_content'] = $viewer;
  }
  // If no viewer is configured just show the jpeg.
  elseif (isset($islandora_object['JPG']) && islandora_datastream_access(ISLANDORA_VIEW_OBJECTS, $islandora_object['JPG'])) {
    $params = array(
      'title' => $islandora_object->label,
      'path' => url("islandora/object/{$islandora_object->id}/datastream/JPG/view"),
    );
    $variables['islandora_content'] = theme('image', $params);
  }
  else {
    $variables['islandora_content'] = NULL;
  }
}

/**
 * Implements hook_process_theme().
 */
function umkc_theme_preprocess_islandora_object_print(array &$variables) {
  $object = $variables['object'];
  if (in_array('islandora:sp_large_image_cmodel', $object->models)) {
    $url = url("islandora/object/{$object->id}", array('absolute' => TRUE));
    $variables['content']['link'] = array(
      '#weight' => -10,
      '#markup' => t('<img id="persistent_url_icon" src="/sites/all/themes/umkc-theme/images/link-icon-SpecialCollections.svg"> !link', array("!link" => l($url, $url))),
    );
    $variables['content']['clip'] = array(
      '#weight' => 0,
      '#prefix' => "<div id='clip'>",
      '#markup' => t('<img src="' . $url . '/datastream/JPG">'),
      '#suffix' => '</div>',
    );
  }
}

/**
 * Implements hook_process_theme().
 */
function umkc_theme_process_islandora_object_print(array &$variables) {
  $object = $variables['object'];
  if (in_array('islandora:sp_large_image_cmodel', $object->models)) {
    if (isset($variables['content']['buttons'])) {
      unset($variables['content']['buttons']);
    }
    if (isset($variables['content']['buttons'])) {
      unset($variables['content']['buttons']);
    }
  }
}
//      dsm($islandora_object, 'islandora object');
//      dsm($metadata, 'metadata object');
//      dsm($temp_array, 'custom array');
//      dsm($variables, 'variables array');
//      dsm($tabs, 'tabs array');
