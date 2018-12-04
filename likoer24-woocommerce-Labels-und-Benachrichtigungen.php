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
    register_post_status( 'wc-payment-reminder', array(
        'label'                     => _x('Zahlungserinnerung', 'woocommerce'),
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Zahlungserinnerung <span class="count">(%s)</span>', 'Zahlungserinnerung <span class="count">(%s)</span>' )
    ) );
    register_post_status( 'wc-payment-receipt', array(
        'label'                     => _x('Zahlungseingang', 'woocommerce'),
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,
        'label_count'               => _n_noop( 'Zahlungseingang <span class="count">(%s)</span>', 'Zahlungseingang <span class="count">(%s)</span>' )
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
            $new_order_statuses['wc-payment-reminder'] = 'Zahlungserinnerung';
            $new_order_statuses['wc-payment-receipt'] = 'Zahlungseingang';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_awaiting_shipment_to_order_statuses' );
// https://www.cloudways.com/blog/create-woocommerce-custom-order-status/


function woo_order_status_change_custom($order_id, $status_form, $status_to, $order){
    //echo $order_id . ' - ' . $status_form . ' - ' . $status_to . ' - ' . $order;
    //die;
    $order_items = $order->get_items();
    echo '<pre>';
    var_dump($order_items);
    echo '</pre>';
    
    /*
    if ( 'on-hold' ==  $status_to ){
        lk24_send_on_hold_mail($order);
    }
    if ( 'label-creating' ==  $status_to ){
        lk24_send_label_creating_mail($order);
    }
    if ( 'label-approval' ==  $status_to ){
        lk24_send_label_approval_mail($order);
    }
    if ( 'payment-reminder' ==  $status_to ){
        lk24_send_label_approval_mail($order);
    }
    if ( 'payment-receipt' ==  $status_to ){
        lk24_send_label_approval_mail($order);
    }
    */
}
add_action('woocommerce_order_status_changed', 'woo_order_status_change_custom', 10, 4);
// https://stackoverflow.com/questions/46090181/woocommerce-order-status-changed-hook-getting-old-and-new-status

// do_action( 'woocommerce_order_status_changed', $this->get_id(), $status_transition['from'], $status_transition['to'], $this );
// https://docs.woocommerce.com/wc-apidocs/source-class-WC_Order.html#334


function lk24_send_on_hold_mail($order){
    $order_items = $order->get_items();
    
//    echo '<pre>';
//    var_dump($order_items);
//    echo '</pre>';
    
    foreach ($order_items as $item){
        // Compatibility for woocommerce 3+
        $product_id = version_compare( WC_VERSION, '3.0', '<' ) ? $item['product_id'] : $item->get_product_id();

        // Here you get your data
        $custom_field = get_post_meta( $product_id, '_tmcartepo_data', true); 

        // To test data output (uncomment the line below)
        // print_r($custom_field);

        // If it is an array of values
        if( is_array( $custom_field ) ){
            echo implode( '<br>', $custom_field ); // one value displayed by line 
        } 
        // just one value (a string)
        else {
            echo $custom_field;
        }
    }
    die;
    
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
    //global $pagenow, $post;
	//if( $pagenow != 'edit.php') return; // Exit
	//if( get_post_type($post->ID) != 'shop_order' ) return; // Exit
    ?>
    <style>
		.order-status-dashboard{
			display: -webkit-inline-box;
			display: inline-flex;
			line-height: 2.5em;
			color: #777;
			background: #e5e5e5;
			border-radius: 4px;
			border-bottom: 1px solid rgba(0,0,0,.05);
			cursor: inherit!important;
			white-space: nowrap;
			max-width: 100%;
			min-width: 120px;
			padding: 0 12px;
			margin: 0 auto;
		}
		
        .order-status-dashboard.status-label-approval,
		.order-status.status-label-approval{
            color: green;
            border-left: 5px solid green;
			font-weight: bold;
        }
		.order-status-dashboard.status-label-creating,
		.order-status.status-label-creating{
    		color: darkorange;
            border-left: 5px solid darkorange;
			font-weight: bold;
        }
		.lk24-dashboard-table thead,
		.lk24-dashboard-table tfoot{
			font-weight: bold;
			background: lightgray;
		}
		.lk24-dashboard-table tr{
			margin-bottom: 8px;
		}
    </style>
    <?php
}

function wc_cancelled_order_add_customer_email( $recipient, $order ){
     return $recipient . ',' . $order->billing_email;
}
add_filter( 'woocommerce_email_recipient_cancelled_order', 'wc_cancelled_order_add_customer_email', 10, 2 );
// https://stackoverflow.com/questions/33843092/woocommerce-not-sending-emails-on-cancelled-order
// 
/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function lk24_add_dashboard_widget() {

	wp_add_dashboard_widget(
		'lk24_dashboard_order_label_statues',         // Widget slug.
		_x('Likoer24 Order Label Statues', 'likoer24'),         // Title.
		'lk24_dashboard_order_label_statues' // Display function.
	);	
}
add_action( 'wp_dashboard_setup', 'lk24_add_dashboard_widget' );
// https://codex.wordpress.org/Dashboard_Widgets_API

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function lk24_dashboard_order_label_statues() {

	$query = new WC_Order_Query( array(
		'limit' => 10,
		'status' => array('label-creating','label-approval'),
		'orderby' => 'date',
		'order' => 'ASC',
		//'return' => 'ids',
	) );
	$orders = $query->get_orders();
	if ($orders){
		echo '<table class="lk24-dashboard-table" width="100%" border="0" align="center">';
		echo '<thead>';
		echo '<tr>';
		echo '<td><strong>#Order</strong></td>';
		echo '<td><strong>Date</strong></td>';
		echo '<td><strong>Status</strong></td>';
		echo '<td>&nbsp;</td>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		foreach($orders as $order){
//			var_dump($order->get_status());
			echo '<tr>';
			echo '<td height="40">#'. $order->get_id() .'</td>';
			echo '<td>'. date('d.m.Y', strtotime($order->get_date_created()) ) .'</td>';
			echo '<td><mark class="order-status-dashboard status-'.$order->get_status().' tips"><span>'. wc_get_order_status_name( $order->get_status() ) .'</span></mark></td>';
			echo '<td aligh="center"><a href="'. $order->get_edit_order_url().'"><span class="dashicons dashicons-admin-generic"></span></a></td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '<tfoot>';
		echo '<tr>';
		echo '<td><strong>#Order</strong></td>';
		echo '<td><strong>Date</strong></td>';
		echo '<td><strong>Status</strong></td>';
		echo '<td>&nbsp;</td>';
		echo '</tr>';
		echo '</tfoot>';
		echo '</table>';
	}
	// https://businessbloomer.com/woocommerce-easily-get-order-info-total-items-etc-from-order-object/
	// https://github.com/woocommerce/woocommerce/wiki/wc_get_orders-and-WC_Order_Query
	// https://www.webhat.in/article/woocommerce-tutorial/how-to-get-order-details-by-order-id/
	// https://docs.woocommerce.com/wc-apidocs/class-WC_Order.html
	// http://woocommerce.wp-a2z.org/
}