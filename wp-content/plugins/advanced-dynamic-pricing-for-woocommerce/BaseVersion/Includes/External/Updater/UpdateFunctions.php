<?php

namespace ADP\BaseVersion\Includes\External\Updater;

use ADP\BaseVersion\Includes\Common\Database;
use ADP\BaseVersion\Includes\Context;
use ADP\BaseVersion\Includes\Rule\OptionsConverter;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class UpdateFunctions {
	public static function call_update_function( $function ) {
		if ( method_exists( __CLASS__, $function ) ) {
			self::$function();
		}
	}

	public static function migrate_to_2_2_3() {
		global $wpdb;

		$table = $wpdb->prefix . Database::TABLE_RULES;
		$sql   = "SELECT id, conditions FROM $table";
		$rows  = $wpdb->get_results( $sql );

		$rows = array_map( function ( $item ) {
			$result = array(
				'id'         => $item->id,
				'conditions' => unserialize( $item->conditions ),
			);

			return $result;
		}, $rows );

		foreach ( $rows as &$row ) {
			$prev_row = $row;
			foreach ( $row['conditions'] as &$condition ) {
				if ( 'amount_' === substr( $condition['type'], 0,
						strlen( 'amount_' ) ) && 3 === count( $condition['options'] ) ) {
					array_unshift( $condition['options'], 'in_list' );
				}
			}
			if ( $prev_row != $row ) {
				$row['conditions'] = serialize( $row['conditions'] );
				$result            = $wpdb->update( $table, array( 'conditions' => $row['conditions'] ),
					array( 'id' => $row['id'] ) );
			}
		}
	}

	public static function migrate_to_3_0_0() {
		global $wpdb;

		$table = $wpdb->prefix . Database::TABLE_RULES;
		$sql   = "SELECT id, conditions, limits, cart_adjustments FROM $table";
		$rows  = $wpdb->get_results( $sql );

		$rows = array_map( function ( $item ) {
			$result = array(
				'id'         => $item->id,
				'conditions' 	   => unserialize( $item->conditions ),
				'limits'	 	   => unserialize( $item->limits ),
				'cart_adjustments' => unserialize( $item->cart_adjustments ),
			);

			return $result;
		}, $rows );

		foreach ( $rows as &$row ) {
			$prev_row = $row;
			foreach( $row['conditions'] as &$data ) {
				$data 	   = OptionsConverter::convertCondition( $data );
			}
			foreach( $row['cart_adjustments'] as &$data ) {
				$data 	   = OptionsConverter::convertCartAdj( $data );
			}
			foreach( $row['limits'] as &$data ) {
				$data 	   = OptionsConverter::convertLimit( $data );
			}
			if( $prev_row != $row ) {
				$row['conditions'] 		 = serialize( $row['conditions'] );
				$row['cart_adjustments'] = serialize( $row['cart_adjustments'] );
				$row['limits'] 			 = serialize( $row['limits'] );
				$result            = $wpdb->update( $table, array(
					'conditions' 	   => $row['conditions'],
					'cart_adjustments' => $row['cart_adjustments'],
					'limits'		   => $row['limits'],
				),
					array( 'id' => $row['id'] ) );
			}
		}
	}

	public static function migrate_options_to_3_1_0() {
		$context                     = new Context();
		$settings                    = $context->get_settings();
		$disableExternalCouponsValue = $settings->getOption( 'disable_external_coupons' );

		if ( $disableExternalCouponsValue === "dont_disable" ) {
			if ( ! $settings->getOption( 'apply_external_coupons_only_to_unmodified_products' ) ) {
				$settings->set( "external_coupons_behavior", "apply" );
			} else {
				$settings->set( "external_coupons_behavior", "apply_to_unmodified_only" );
			}
		} elseif ( $disableExternalCouponsValue === "if_any_rule_applied" ) {
			$settings->set( "external_coupons_behavior", "disable_if_any_rule_applied" );
		} elseif ( $disableExternalCouponsValue === "if_any_of_cart_items_updated" ) {
			$settings->set( "external_coupons_behavior", "disable_if_any_of_cart_items_updated" );
		}

		$context->get_settings()->save();
	}

	public static function migrate_free_products_to_3_1_0() {
		global $wpdb;

		$table = $wpdb->prefix . Database::TABLE_RULES;
		$sql   = "SELECT id, get_products FROM $table";
		$rows  = $wpdb->get_results( $sql );

		$rows = array_filter( array_map( function ( $item ) {
			$result = array(
				'id'           => $item->id,
				'get_products' => unserialize( $item->get_products ),
			);

			if ( empty( $result['get_products'] ) ) {
				return false;
			}

			return $result;
		}, $rows ) );

		foreach ( $rows as &$row ) {
			$values = isset( $row['get_products']['value'] ) ? $row['get_products']['value'] : array();
			foreach ( $values as &$value ) {
				$giftMode             = isset( $value['gift_mode'] ) ? $value['gift_mode'] : "giftable_products";
				$useProductFromFilter = isset( $value['use_product_from_filter'] ) ? $value['use_product_from_filter'] === 'on' : false;
				if ( $useProductFromFilter ) {
					$giftMode = "use_product_from_filter";
				}

				$value['gift_mode'] = $giftMode;
				unset( $value['use_product_from_filter'] );
			}
			$row['get_products']['value'] = $values;

			$result = $wpdb->update( $table, array( 'get_products' => serialize( $row['get_products'] ) ),
				array( 'id' => $row['id'] ) );
		}
	}
}

