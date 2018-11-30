<?php

function register_labels_order_status() {
    register_post_status( 'wc-label-creating', array(
        'label'                     => _x('Creating Label', 'woocommerce'),
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Creating Label <span class="count">(%s)</span>', 'Creating Label <span class="count">(%s)</span>' )
    ) );
    register_post_status( 'wc-label-approval', array(
        'label'                     => _x('Approval of Label', 'woocommerce'),
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Aproval of Label <span class="count">(%s)</span>', 'Aproval of Label <span class="count">(%s)</span>' )
    ) );
}
add_action( 'init', 'register_labels_order_status' );