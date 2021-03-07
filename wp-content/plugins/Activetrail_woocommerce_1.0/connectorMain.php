<?php
/*
Plugin Name: ActiveTrail
Description: Connect your WooCommerce store to ActiveTrail
Version: 1.0.0
*/
defined('ABSPATH') or die("Cannot access pages directly.");

include 'worker.php';
$worker = new BridgeConnector();

if ($worker->isBridgeExist()) {
  include $worker->bridgePath . $worker->configFilePath;
  $storeKey = M1_TOKEN;
}

if (isset($_REQUEST['connector_action'])) {
  $action = $_REQUEST['connector_action'];
  $storeKey = BridgeConnector::generateStoreKey();
  switch ($action) {
    case 'installBridge':
      $status = $worker->installBridge();
      $worker->updateToken($storeKey);
      $data = ['storeKey' => $storeKey];
      break;
    case 'removeBridge':
      $status = $worker->unInstallBridge();
      $data = [];
      break;
    case 'updateToken':
      $status = $worker->updateToken($storeKey);
      $data = ['storeKey' => $storeKey];
  }
  echo json_encode(['status' => $status, 'data' => $data]);
  exit();
}

function connector_plugin_action_links($links, $file)
{
  if ($file == plugin_basename(dirname(__FILE__) . '/connectorMain.php')) {
    $links[] = '<a href="' . admin_url('admin.php?page=connector-config') . '">' . __('Settings') . '</a>';
  }

  return $links;
}

add_filter('plugin_action_links', 'connector_plugin_action_links', 10, 2);

function connector_config()
{
  global $worker;

  if ($worker->isBridgeExist()) {
    include_once $worker->bridgePath . $worker->configFilePath;
    $storeKey = M1_TOKEN;
  } else {
    $storeKey = '';
  }

  wp_enqueue_style('connector-css', plugins_url('css/style.css', __FILE__));
  wp_enqueue_script('jquery-1.11.3-js', plugins_url('js/jquery-1.11.3.min.js', __FILE__));
  wp_enqueue_script('connector-js', plugins_url('js/scripts.js', __FILE__), array('jquery'));

  $showButton = 'install';
  if ($worker->isBridgeExist()) {
    $showButton = 'uninstall';
  }

  $cartName = 'WooCommerce';
  $sourceCartName = 'WooCommerce';
  $sourceCartName = strtolower(str_replace(' ', '-', trim($sourceCartName)));
  $referertext = 'Connector: ' . $sourceCartName . ' to ' . $cartName . ' module';

  include 'settings.phtml';
  return true;
}

function connector_load_menu()
{
  add_submenu_page('plugins.php', __('Connector'), __('Connector'), 'manage_options', 'connector-config', 'connector_config');
}

add_action('admin_menu', 'connector_load_menu');
