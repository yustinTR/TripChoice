<?php

/**
 * @file
 * Local development configuration.
 */

use Drupal\Component\Assertion\Handle;

// Dev settings.
$config['system.performance']['css']['preprocess'] = FALSE;
$config['system.performance']['js']['preprocess'] = FALSE;
$dev = TRUE;
if ($dev) {
  assert_options(ASSERT_ACTIVE, TRUE);
  Handle::register();
  $settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';
  $config['system.logging']['error_level'] = 'verbose';
  $settings['cache']['bins']['render'] = 'cache.backend.null';
  $settings['cache']['bins']['page'] = 'cache.backend.null';
  $settings['cache']['bins']['dynamic_page_cache'] = 'cache.backend.null';
  $settings['rebuild_access'] = FALSE;

  /**
   * Set config split dev status to TRUE.
   */
  $config['config_split.config_split.dev']['status'] = TRUE;
}
else {
  $config['config_split.config_split.dev']['status'] = FALSE;
}

$settings['skip_permissions_hardening'] = TRUE;
$config['image.settings']['allow_insecure_derivatives'] = TRUE;

/**
 * Override solr server.
 */
$config['search_api.server.solr'] = [
  'name' => 'Solr (Overridden)',
  'backend_config' => [
    'connector_config' => [
      'host' => 'solr',
      'path' => '',
      'core' => 'dev',
      'port' => '8983',
    ],
  ],
];

/**
 * Environment indicator.
 */
$config['environment_indicator.indicator']['bg_color'] = 'Green';
$config['environment_indicator.indicator']['fg_color'] = 'White';
$config['environment_indicator.indicator']['name'] = 'Dev';
$config['environment_indicator.settings']['favicon'] = true;
$config['environment_indicator.settings']['toolbar_integration']['toolbar'] = 'toolbar';
