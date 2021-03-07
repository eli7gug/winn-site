<?php
/*
Plugin Name: ActiveTrail Webhook Helper
Description: The plugin adds extra webhook topics for WooCommerce.
Author:
Version: 1.1.0
Author URI:
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
*/

/*
Webhook helper is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Webhook helper is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Webhook helper. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

global $wl_wh_allow_execute;
$wl_wh_allow_execute = false;

if (!defined('WEBHOOK_HELPER_VERSION')) {// if there are no other plugin with the same functionality
  define('WEBHOOK_HELPER_VERSION', '1.1.0');

  $wl_wh_allow_execute = true;

  function wl_wh_add_new_webhook_resource($resources)
  {
    $resources[] = 'basket';

    return $resources;
  }

  function wl_wh_add_new_webhook_topic($topics)
  {
    $topics = array_merge(
        $topics,
        array(
            'basket.updated' => __('Basket Updated (white label)', 'wl_wh'),
            'basket.deleted' => __('Basket Deleted (white label)', 'wl_wh'),
        )
    );

    return $topics;
  }

  function wl_wh_cart_updated($arg)
  {
    $user_id = get_current_user_id() ?: WooCommerce::instance()->session->get_customer_id();
    $blog_id = get_current_blog_id();
    $createdKey = '_a2c_wh_cart_' . $blog_id . '_created_gmt';
    $time = time();

    if (preg_match('/^[a-f0-9]{32}$/', $user_id) !== 1) {
      if ($arg !== false) {
        do_action(
          'a2c_wh_cart_updated_action',
          array(
            'user_id' => $user_id,
            'blog_id' => $blog_id
          )
        );
      }

      update_user_meta($user_id, '_a2c_wh_cart_' . $blog_id . '_updated_gmt', $time);

      if (empty(get_user_meta($user_id, $createdKey, true))) {
        update_user_meta($user_id, $createdKey, $time);
      }

    }

    return $arg;
  }

  function wl_wh_cart_emptied($arg)
  {
    $user_id = get_current_user_id() ?: WooCommerce::instance()->session->get_customer_id();
    $blog_id = get_current_blog_id();
    $createdKey = '_a2c_wh_cart_' . $blog_id . '_created_gmt';
    $updatedKey = '_a2c_wh_cart_' . $blog_id . '_updated_gmt';

    if (preg_match('/^[a-f0-9]{32}$/', $user_id) !== 1) {
      delete_user_meta($user_id, $createdKey);
      delete_user_meta($user_id, $updatedKey);
    }

    if ($arg !== false) {
      do_action(
        'a2c_wh_cart_emptied_action',
        array(
          'user_id' => $user_id,
          'blog_id' => $blog_id
        )
      );
    }

    return $arg;
  }

  function wl_wh_cart_resolve_event($arg)
  {
    if (empty(WooCommerce::instance()->cart->get_cart_contents())) {
      return wl_wh_cart_emptied($arg);
    } else {
      return wl_wh_cart_updated($arg);
    }
  }

  function wl_wh_add_new_webhook_topic_hook($topics)
  {
    $topics['basket.updated'] = array(
      'a2c_wh_cart_updated_action',
    );
    $topics['basket.deleted'] = array(
      'a2c_wh_cart_emptied_action',
    );

    return $topics;
  }

  function wl_wh_build_webhook_payload($payload, $resource, $resource_id)
  {
    if ($resource === 'basket') {
      $payload = $resource_id;
    }

    return $payload;
  }

  function wl_wh_activate()
  {
    if (class_exists('WooCommerce')) {
      $version = WooCommerce::instance()->version;
    }

    if (empty($version) || version_compare($version, '2.6') === -1) {
      echo '<h3>'.__('Woocommerce 2.6+ is required. ', 'a2c_wh').'</h3>';
      @trigger_error(__('Woocommerce 2.6+ is required. ', 'a2c_wh'), E_USER_ERROR);
    }

    update_option('webhook_helper_version', WEBHOOK_HELPER_VERSION, false);
    update_option('webhook_helper_active', true, false);
  }

  function wl_wh_uninstall()
  {
    /**
     * @global $wpdb wpdb Database Access Abstraction Object
     */
    global $wpdb;
    $wpdb->query('DELETE FROM `' . $wpdb->prefix . 'usermeta` WHERE `meta_key` LIKE "_a2c_wh_cart_%_updated_gmt"');
    delete_option('webhook_helper_version');
    delete_option('webhook_helper_active');
  }

  function wl_wh_deactivate()
  {
    update_option('webhook_helper_active', false);
  }

  function wl_wh_check_version()
  {
    if (WEBHOOK_HELPER_VERSION !== get_option('webhook_helper_version')) {
      wl_wh_activate();
    }
  }

  add_action('plugins_loaded', 'wl_wh_check_version');
  add_action('woocommerce_valid_webhook_resources', 'wl_wh_add_new_webhook_resource');
  add_action('woocommerce_webhook_topics', 'wl_wh_add_new_webhook_topic');
  add_action('woocommerce_webhook_topic_hooks', 'wl_wh_add_new_webhook_topic_hook');
  add_action('woocommerce_webhook_payload', 'wl_wh_build_webhook_payload', 10, 3);

  add_action('woocommerce_update_cart_action_cart_updated', 'wl_wh_cart_resolve_event');
  add_action('woocommerce_add_to_cart', 'wl_wh_cart_updated');
  add_action('woocommerce_cart_item_removed', 'wl_wh_cart_resolve_event');
  add_action('woocommerce_cart_item_restored', 'wl_wh_cart_updated');
  add_action('woocommerce_new_order', 'wl_wh_cart_emptied');
}

function customized_wl_wh_uninstall()
{
  global $wl_wh_allow_execute;

  if ($wl_wh_allow_execute) {
    wl_wh_uninstall();
  }

  //put your code here
}

function customized_wl_wh_activate()
{
  global $wl_wh_allow_execute;

  if ($wl_wh_allow_execute) {
    wl_wh_activate();
  }

  //put your code here
}

function customized_wl_wh_deactivate()
{
  global $wl_wh_allow_execute;

  if ($wl_wh_allow_execute) {
    wl_wh_deactivate();
  }

  //put your code here
}

register_uninstall_hook(__FILE__, 'customized_wl_wh_uninstall');
register_activation_hook(__FILE__, 'customized_wl_wh_activate');
register_deactivation_hook(__FILE__, 'customized_wl_wh_deactivate');
