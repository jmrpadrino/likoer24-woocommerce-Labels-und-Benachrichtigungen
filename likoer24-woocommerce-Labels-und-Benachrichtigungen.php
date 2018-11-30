<?php
/**
 * @package likoer24-woocommerce-Labels-und-Benachrichtigungen
 */
/*
Plugin Name: Likoer24 Woocommerce Labels und Benachrichtigungen
Plugin URI: https://palacios-online.de/
Description: This plugin creates 2 tags for woocommerce and their respective notification emails upon request of the client.
Version: 0.1
Author: Jose Manuel Rodriguez & Palacios Online
Author URI: https://palacios-online.de/
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/


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


function woo_order_status_change_custom($order_id, $status_form, $status_to, $order){
    //echo $order_id . ' - ' . $status_form . ' - ' . $status_to . ' - ' . $order;
    //die;
    if ( 'label-creating' ==  $status_to ){
        lk24_send_label_creating_mail($order);
    }
    if ( 'label-approval' ==  $status_to ){
        lk24_send_label_approval_mail($order);
    }
}
add_action('woocommerce_order_status_changed', 'woo_order_status_change_custom', 10, 4);
// https://stackoverflow.com/questions/46090181/woocommerce-order-status-changed-hook-getting-old-and-new-status

// do_action( 'woocommerce_order_status_changed', $this->get_id(), $status_transition['from'], $status_transition['to'], $this );
// https://docs.woocommerce.com/wc-apidocs/source-class-WC_Order.html#334


function lk24_send_label_creating_mail($order){
    global $woocommerce;
    $mailer = $woocommerce->mailer();


    $message_body  = '<h3>Bestellung '.$order->id.' – Etikett Entwurf</h3>';
    $message_body .= '<p>Hallo '.$order->get_billing_first_name().' '.$order->get_billing_last_name().',</p>';
    $message_body .= '<p>vielen Dank noch einmal für Ihre Bestellung 3225867. Wir erstellen derzeit Ihr individuelles Etikett. In spätesten 3 Werktagen erhalten Sie den Entwurf per E-Mail. <br />Bitte prüfen Sie dann alle Angaben auf dem Etikett und geben Sie das Etikett per Mail zum Druck frei. </p>';
    $message_body .= '<h5>Bankverbindung:</h5>';
    $message_body .= '<ul>';
    $message_body .= '<li><strong>Bankhaus Ludwig Sperrer</strong></li>';
    $message_body .= '<li><strong>IBAN:</strong> DE 06 7003 1000 0001 2212 33</li>';
    $message_body .= '<li>Angela Bauer</li>';
    $message_body .= '</ul>';
    $message_body .= '<p>Nach Zahlungseingang und Druckfreigabe bekleben wir die Flaschen und versenden Ihre Bestellung.</p>';
    $message_body .= '<p>Noch Fragen? Dann schreiben Sie uns einfach an <a href="mailto:info@likoer24.de">info@likoer24.de</a></p>';


    $message = $mailer->wrap_message(
        // Message head and message body.
        sprintf( __( 'Etikett Entwurf' ), $order->get_order_number() ), $message_body );

    // Cliente email, email subject and message.
    $mailer->send( $order->billing_email, sprintf( __( 'Etikett Entwurf' ), $order->get_order_number() ), $message );
}


function lk24_send_label_approval_mail($order){
    global $woocommerce;
    $mailer = $woocommerce->mailer();


    $message_body  = '<h3>Druckfreigabe Etikett - Bestellung '.$order->id.'</h3>';
    $message_body .= '<p>Hallo '.$order->get_billing_first_name().' '.$order->get_billing_last_name().',</p>';
    $message_body .= '<p>es freut uns, dass Ihnen der Entwurf für Ihr persönlichen Etiketts gefällt. Vielen Dank für die Druckfreigabe. <br /> Sobald der Zahlungseingang auf unserem Konto erfolgt, drucken wir die Etiketten und bekleben die Flaschen. Anschließend versenden wir Ihre Bestellung.</p>';
    $message_body .= '<h5>Bankverbindung:</h5>';
    $message_body .= '<ul>';
    $message_body .= '<li><strong>Bankhaus Ludwig Sperrer</strong></li>';
    $message_body .= '<li><strong>IBAN:</strong> DE 06 7003 1000 0001 2212 33</li>';
    $message_body .= '<li>Angela Bauer</li>';
    $message_body .= '</ul>';
    $message_body .= '<p>Noch Fragen? Dann schreiben Sie uns einfach an <a href="mailto:info@likoer24.de">info@likoer24.de</a></p>';


    $message = $mailer->wrap_message(
        // Message head and message body.
        sprintf( __( 'Druckfreigabe Etikett' ), $order->get_order_number() ), $message_body );

    // Cliente email, email subject and message.
    $mailer->send( $order->billing_email, sprintf( __( 'Druckfreigabe Etikett' ), $order->get_order_number() ), $message );
}
// https://stackoverflow.com/questions/43716196/send-an-email-notification-when-custom-order-status-changes-in-woocommerce

add_action('pre_get_posts', function($query){
    if (is_admin() && $query->is_main_query() && $_GET['post_type'] == 'shop_order'){
        $post_status = $query->query_vars['post_status'];
        $post_status[] = 'wc-label-creating';
        $post_status[] = 'wc-label-approval';
        $query->set('post_status', $post_status);
    }
});

//add_action( 'woocommerce_product_query', 'so_20990199_product_query' );
function so_20990199_product_query( $q ){
    echo '<pre>';
    var_dump($q);
    echo '</pre>';
    die;
}

add_action('admin_head', 'styling_admin_order_list' );
function styling_admin_order_list() {
    global $pagenow, $post;

    if( $pagenow != 'edit.php') return; // Exit
    if( get_post_type($post->ID) != 'shop_order' ) return; // Exit
    ?>
    <style>
        .order-status.status-label-approval {
            color: green;
            border-left: 5px solid green;
			font-weight: bold;
        }
        .order-status.status-label-approval:before{
            content: '\f53f';
            position: absolute;
            right: 0;
            top: 10px;
        }
		.order-status.status-label-creating {
    		color: darkorange;
            border-left: 5px solid darkorange;
			font-weight: bold;
        }
    </style>
    <?php
}
// https://stackoverflow.com/questions/49333542/custom-order-status-background-button-color-in-woocommerce-3-3-admin-order-list
