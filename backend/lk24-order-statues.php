<?php
function add_awaiting_shipment_to_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    foreach ( $order_statuses as $key => $status ) {

        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-label-creating'] = 'Creating Label';
            $new_order_statuses['wc-label-approval'] = 'Approval of Label';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_awaiting_shipment_to_order_statuses' );
// https://www.cloudways.com/blog/create-woocommerce-custom-order-status/