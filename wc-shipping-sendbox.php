<?php

/**
 * @package Woocommerce Shipping Options
 * @version 1.0
 */
/*
  Plugin Name: Shipments via Sendbox 
  Plugin URI: https://wordpress.org/plugins/woocommerce-shipping-sendbox/
  Description: Add new shipping method where user can add extra information via html select options.
  Author: Kpono Akpabio
  Version: 1.0
  Author URI: http://sendbox.ng/
 */

if (!class_exists('WC_Shipping_Sendbox')) {
    
    function wcso_shipping_methods_init() {
    
        class WC_Shipping_Sendbox extends WC_Shipping_Method {
          
             
            public function __construct() {
                $this->id = 'wcso_local_shipping';
                $this->single_rate=0;
                $this->method_title = __( 'Sendbox Shipments', 'wc_shipping_options' );
                $this->title = __('Sendbox Shipping');
                $this->options_array_label = 'wcso_shipping_options';
                $this->method_description = __('Shipping via Sendbox  for frontend and backend');

           
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_shipping_options' ) );
                $this->init();
            }

            /**
             * Init settings
             *
             * @access public
             * @return void
             */
            function init() {
             
                // Load the settings API
                $this->init_form_fields();
                $this->init_settings();
                
                 global $single_rate;
            global $origin_country;
            global $origin_state;
            global $origin_address;
            global $origin_street;
            global $origin_city;
            global $origin_phone;
             global $origin_phone;
            global $origin_email;
            global $origin_name;
            global $auth_header;
            global $use_max_carrier;
			 global $test_mode;
            
                
                // Define user set variables
        $this->title        = $this->get_option( 'title' );
        
        $this->codes        = $this->get_option( 'codes' );
        $this->availability = $this->get_option( 'availability' );
        $this->countries    = $this->get_option( 'countries' );
         $origin_country=$this->get_option( 'origin_country' );
         $origin_state=$this->get_option( 'origin_state' );
         $origin_address=$this->get_option( 'origin_address' );
         $origin_street=$this->get_option( 'origin_street' );
         $origin_city=$this->get_option( 'origin_city' );
         $origin_phone=$this->get_option( 'origin_phone' );
          $origin_name=$this->get_option( 'origin_name' );
           $origin_email=$this->get_option( 'origin_email' );
           if($this->get_option( 'test_mode' )=="yes")
         $auth_header=$this->get_option( 'auth_header' );
          else
           $auth_header=$this->get_option( 'auth_header_live' );
		  $test_mode=$this->get_option( 'test_mode' );
           
          $use_max_carrier=$this->get_option( 'use_max_carrier' );

                $this->get_shipping_options();
                
                add_filter( 'woocommerce_shipping_methods', array(&$this, 'add_wcso_shipping_methods'));
                add_action('woocommerce_cart_totals_after_shipping', array(&$this, 'wcso_review_order_shipping_options'));
                add_action('woocommerce_review_order_after_shipping', array(&$this, 'wcso_review_order_shipping_options'));
                add_action( 'woocommerce_checkout_update_order_meta', array(&$this, 'wcso_field_update_shipping_order_meta'), 10, 2);
                if (is_admin()) {
                    add_action( 'woocommerce_admin_order_data_after_shipping_address', array(&$this, 'wcso_display_shipping_admin_order_meta'), 10, 2 );
                }


            }
            
            /**
            * calculate_shipping function.
            *
            * @access public
            * @param array $package (default: array())
            * @return void
            */
           function calculate_shipping($package = array()) {
                $shipping_total = 0;
                
                if(get_sendbox_shipping_quotes_frontend($package)!=null)
				{
                    $shipping_total=get_sendbox_shipping_quotes_frontend($package);
                    $this->single_rate=$shipping_total;

                $rate = array(
                    'id' => $this->id,
                    'label' => $this->title,
                    'cost' => $shipping_total
                );

                $this->add_rate($rate);
				}
            }

            /**
             * init_form_fields function.
             *
             * @access public
             * @return void
             */
            function init_form_fields() {
                $countries_obj   = new WC_Countries();
                 $countries   = $countries_obj->get_allowed_countries();

                $states=$countries_obj->get_states(
                $countries_obj->get_base_country());
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable', 'woocommerce'),
                        'type' => 'checkbox',
                        'label' => __('Enable Sendbox Shipping ', 'wc_shipping_options'),
                        'default' => 'no'
                    ),
                    'title' => array(
                        'title' => __('Title', 'woocommerce'),
                        'type' => 'text',
                        'description' => __('This controls the title which the user sees during checkout.', 'woocommerce'),
                        'default' => __('Sendbox Shipping', 'wc_shipping_options'),
                        'desc_tip' => true,
                    ),
                   

  'origin_name' => array(
        'title'         => __( 'Origin Name', 'woocommerce' ),
        'type'          => 'text',
        'placeholder'   => '',
        'desc_tip'      => true,
    ),
    'origin_phone' => array(
        'title'         => __( 'Origin Phone', 'woocommerce' ),
        'type'          => 'text',
        'placeholder'   => '',
        'desc_tip'      => true,
    ),
        'origin_email' => array(
        'title'         => __( 'Origin Email', 'woocommerce' ),
        'type'          => 'text',
        'placeholder'   => '',
        'desc_tip'      => true,
    ),
    
        'origin_city' => array(
        'title'         => __( 'Origin City', 'woocommerce' ),
        'type'          => 'text',
        'placeholder'   => '',
        'desc_tip'      => true,
    ),
        'origin_address' => array(
        'title'         => __( 'Origin Address', 'woocommerce' ),
        'type'          => 'text',
        'placeholder'   => '',
        'desc_tip'      => true,
    ),'origin_street' => array(
        'title'         => __( 'Origin Street', 'woocommerce' ),
        'type'          => 'text',
        'placeholder'   => '',
        'desc_tip'      => true,
    ),
        'origin_state' => array(
        'title'         => __( 'Origin State', 'woocommerce' ),
         'type'       => 'select',
        'class'      => array( 'chzn-drop' ),
        'label'      => __('Item ships from - state'),
       
        'options'    => $states,
    ),
         'origin_country' => array(
        'title'         => __( 'Country of Origin', 'woocommerce' ),
        'type'       => 'select',
'class'      => array( 'chzn-drop' ),
'label'      => __('Country'),
'placeholder'    => __('Select Country'),
'options'    => $countries,
    ),
	 'use_max_carrier' => array(
        'title'         => __( 'Use max carrier', 'woocommerce' ),
        'type'          => 'select',
        'class'         => 'wc-enhanced-select',
        'default'       => 'no',
        'options'       => array(
            'yes'   => __( 'Yes', 'woocommerce' ),
            'no'        => _x( 'No', 'No', 'woocommerce' ),
        ),
    ),
	'test_mode' => array(
        'title'         => __( 'Test Mode', 'woocommerce' ),
        'type'          => 'select',
        'class'         => 'wc-enhanced-select',
        'default'       => 'yes',
        'options'       => array(
            'yes'   => __( 'Yes', 'woocommerce' ),
            'no'        => _x( 'No', 'No', 'woocommerce' ),
        ),
    ),
	'auth_header' => array(
        'title'         => __( 'Auth Header Test', 'woocommerce' ),
        'type'          => 'text',
        'placeholder'   => '',
        'desc_tip'      => true,
    ),
	'auth_header_live' => array(
        'title'         => __( 'Auth Header Live', 'woocommerce' ),
        'type'          => 'text',
        'placeholder'   => '',
        'desc_tip'      => true,
    ), 
                    'availability' => array(
                        'title' => __('Method availability', 'woocommerce'),
                        'type' => 'select',
                        'default' => 'all',
                        'class' => 'availability',
                        'options' => array(
                            'all' => __('All allowed countries', 'woocommerce'),
                            'specific' => __('Specific Countries', 'woocommerce')
                        )
                    )
                );
            }
            
            /**
            * admin_options function.
            *
            * @access public
            * @return void
            */
           function admin_options() {
                   ?>
                   <h3><?php echo $this->method_title; ?></h3>
                   <p><?php _e( 'Sendbox Shipping is a simple method for gettings rates from aggregated carriers via API' ); ?></p>
                   <table class="form-table">
                           <?php $this->generate_settings_html(); ?>
                   </table> <?php
           }
           
           /**
             * is_available function.
             *
             * @access public
             * @param array $package
             * @return bool
             */
            function is_available($package) {

                if ($this->enabled == "no")
                    return false;

                // If post codes are listed, let's use them.
                $codes = '';
                if ($this->codes != '') {
                    foreach (explode(',', $this->codes) as $code) {
                        $codes[] = $this->clean($code);
                    }
                }

                if (is_array($codes)) {

                    $found_match = false;

                    if (in_array($this->clean($package['destination']['postcode']), $codes)) {
                        $found_match = true;
                    }


                    // Pattern match
                    if (!$found_match) {

                        $customer_postcode = $this->clean($package['destination']['postcode']);
                        foreach ($codes as $c) {
                            $pattern = '/^' . str_replace('_', '[0-9a-zA-Z]', $c) . '$/i';
                            if (preg_match($pattern, $customer_postcode)) {
                                $found_match = true;
                                break;
                            }
                        }
                    }


                    // Wildcard search
                    if (!$found_match) {

                        $customer_postcode = $this->clean($package['destination']['postcode']);
                        $customer_postcode_length = strlen($customer_postcode);

                        for ($i = 0; $i <= $customer_postcode_length; $i++) {

                            if (in_array($customer_postcode, $codes)) {
                                $found_match = true;
                            }

                            $customer_postcode = substr($customer_postcode, 0, -2) . '*';
                        }
                    }

                    if (!$found_match) {
                        return false;
                    }
                }

                // Either post codes not setup, or post codes are in array... so lefts check countries for backwards compatibility.
                if ($this->availability == 'specific') {
                    $ship_to_countries = $this->countries;
                } else {
                    $ship_to_countries = array_keys(WC()->countries->get_shipping_countries());
                }

                if (is_array($ship_to_countries)) {
                    if (!in_array($package['destination']['country'], $ship_to_countries)) {
                        return false;
                    }
                }

                // Yay! We passed!
                return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', true, $package);
            }

            /**
             * clean function.
             *
             * @access public
             * @param mixed $code
             * @return string
             */
            function clean($code) {
                return str_replace('-', '', sanitize_title($code)) . ( strstr($code, '*') ? '*' : '' );
            }
            
            /**
            * validate_shipping_options_table_field function.
            *
            * @access public
            * @param mixed $key
            * @return bool
            */
            function validate_shipping_options_table_field( $key ) {
                return false;
            }
            
            /**
             * generate_options_table_html function.
             *
             * @access public
             * @return string
             */
            function generate_shipping_options_table_html() {
                ob_start();
                ?>
                    <tr valign="top">
                        <th scope="row" class="titledesc"><?php _e('Shipment via Sendbox ', 'woocommerce'); ?>:</th>
                        <td class="forminp" id="<?php echo $this->id; ?>_options">
                        <table class="shippingrows widefat" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="check-column"><input type="checkbox"></th>
                                    <th class="options-th"><?php _e('Option', 'woocommerce'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                $i = -1;
                                if ($this->shipping_options) :
                                    foreach ($this->shipping_options as $option) :
                                        $i++;
                            ?>
                                        <tr class="option-tr">
                                            <th class="check-column"><input type="checkbox" name="select" /></th>
                                            <td><input type="text" name="<?php echo esc_attr($this->id . '_options[' . $i . ']') ?>" value="<?php echo $option; ?>"></td>
                                        </tr>
                            <?php endforeach; endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4"><a href="#" class="add button"><?php _e('Add Option', 'woocommerce'); ?></a> <a href="#" class="remove button"><?php _e('Delete selected options', 'woocommerce'); ?></a></th>
                                </tr>
                            </tfoot>
                        </table>
                        <script type="text/javascript">
                            jQuery(function() {

                                jQuery('#<?php echo $this->id; ?>_options').on( 'click', 'a.add', function(){
                                    var size = jQuery('#<?php echo $this->id; ?>_options tbody .option-tr').size();
                                    jQuery('<tr class="option-tr"><th class="check-column"><input type="checkbox" name="select" /></th>' +
                                           '<td><input type="text" name="<?php echo esc_attr($this->id . '_options') ?>[' + size + ']" /></td></tr>')
                                        .appendTo('#<?php echo $this->id; ?>_options table tbody');
                                    return false;
                                });

                                // Remove row
                                jQuery('#<?php echo $this->id; ?>_options').on( 'click', 'a.remove', function(){
                                    var answer = confirm("<?php _e('Delete the selected options?', 'woocommerce'); ?>");
                                    if (answer) {
                                        jQuery('#<?php echo $this->id; ?>_options table tbody tr th.check-column input:checked').each(function(i, el){
                                                jQuery(el).closest('tr').remove();
                                        });
                                    }
                                    return false;
                                });

                            });
                        </script>
                        </td>
                    </tr>
                <?php
                return ob_get_clean();
            }
            
            /**
             * process_shipping_options function.
             *
             * @access public
             * @return void
             */
            function process_shipping_options() {
                
                $options = array();

                if (isset($_POST[$this->id . '_options']))
                    $options = array_map('wc_clean', $_POST[$this->id . '_options']);

                update_option($this->options_array_label, $options);
                
                $this->get_shipping_options();
            }

            /**
            * get_shipping_options function.
            *
            * @access public
            * @return void
            */
           function get_shipping_options() {
                   $this->shipping_options = array_filter( (array) get_option( $this->options_array_label ) );
           }
           
           function wcso_review_order_shipping_options() {
                global $woocommerce;
                $chosen_method = $woocommerce->session->get('chosen_shipping_methods');
                if (is_array($chosen_method) && in_array($this->id, $chosen_method)) {
                    echo '<tr class="shipping_option">';
                    echo '<th>' . $this->title . '</th>';
                    echo '<td>'.$this->single_rate.'</td></tr>';
                    
                    ?>
                        <script>
                            var options = document.getElementsByName("shipping_option");
                            if (options.length >= 1) {
                                options[0].addEventListener("change", function() {
                                    var data = "action=wcso_save_selected&shipping_option=" + this.value;
                                    var xmlhttp;
                                    if (window.XMLHttpRequest) {
                                        xmlhttp = new XMLHttpRequest();
                                    } else {
                                        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                                    }
                                    xmlhttp.open('GET', '<?php echo admin_url( 'admin-ajax.php' ); ?>?' + data, true);
                                    xmlhttp.send();
                                });
                            }
                        </script>
                    <?php
                }
            }
            
            function wcso_field_update_shipping_order_meta( $order_id, $posted ) {
                global $woocommerce;
                if (is_array($posted['shipping_method']) && in_array($this->id, $posted['shipping_method'])) {
                    if ( isset( $_POST['shipping_option'] ) && !empty( $_POST['shipping_option'] ) ) {
                        update_post_meta( $order_id, 'wcso_shipping_option', sanitize_text_field( $_POST['shipping_option'] ) );
                        $woocommerce->session->_chosen_shipping_option = sanitize_text_field( $_POST['shipping_option'] );
                    }
                } else { //visible  in cart, hidden in checkout
                    $chosen_method = $woocommerce->session->get('chosen_shipping_methods');
                    $chosen_option= $woocommerce->session->_chosen_shipping_option;
                    if (is_array($chosen_method) && in_array($this->id, $chosen_method) && $chosen_option) {
                        update_post_meta( $order_id, 'wcso_shipping_option', $woocommerce->session->_chosen_shipping_option );
                    }
                }
            }
          
            function wcso_display_shipping_admin_order_meta($order){
                $selected_option = get_post_meta( $order->get_id(), 'wcso_shipping_option', true );
                if ($selected_option) {
                    echo '<p><strong>' . $this->title . ':</strong> ' . get_post_meta( $order->id, 'wcso_shipping_option', true ) . '</p>';
                }
            }
            
            function add_wcso_shipping_methods( $methods ) {
                $methods[] = $this; 
                return $methods;
            }
            
        }
        
        new WC_Shipping_Sendbox();

    }
    
    add_action('woocommerce_shipping_init', 'wcso_shipping_methods_init');

    
    add_action( 'wp_ajax_wcso_save_selected', 'wcso_save_selected' );  
    add_action( 'wp_ajax_nopriv_wcso_save_selected', 'wcso_save_selected' );
    function wcso_save_selected() {
        if ( isset( $_GET['shipping_option'] ) && !empty( $_GET['shipping_option'] ) ) {
            global $woocommerce;
            $selected_option = $_GET['shipping_option'];
            $woocommerce->session->_chosen_shipping_option = sanitize_text_field( $selected_option );
        }
        die();
    }

//register and add shipment order statuses
function register_sendbox_posted_shipment_order_status() {
    register_post_status( 'wc-sendbox-posted-shipment', array(
        'label'                     => 'Posted shipment',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Posted shipment <span class="count">(%s)</span>', 'Posted shipment <span class="count">(%s)</span>' )
    ) );
}
add_action( 'init', 'register_sendbox_posted_shipment_order_status' );

// Add to list of WC Order statuses
function add_sendbox_posted_shipment_to_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {

        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-sendbox-posted-shipment'] = 'Posted shipment';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_sendbox_posted_shipment_to_order_statuses' );

function register_sendbox_updated_shipment_order_status() {
    register_post_status( 'wc-sendbox-updated-shipment', array(
        'label'                     => 'Sendbox updated shipment',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Sendbox updated shipment <span class="count">(%s)</span>', 'Sendbox updated shipment <span class="count">(%s)</span>' )
    ) );
}
add_action( 'init', 'register_sendbox_updated_shipment_order_status' );

// Add to list of WC Order statuses
function add_sendbox_updated_shipment_to_order_statuses( $order_statuses ) {

    $new_order_statuses = array();

    // add new order status after processing
    foreach ( $order_statuses as $key => $status ) {

        $new_order_statuses[ $key ] = $status;

        if ( 'wc-processing' === $key ) {
            $new_order_statuses['wc-sendbox-updated-shipment'] = 'Sendbox updated shipment';
        }
    }

    return $new_order_statuses;
}
add_filter( 'wc_order_statuses', 'add_sendbox_updated_shipment_to_order_statuses' );


// Add hook for admin <head></head>
add_action('admin_head', 'sendbox_quotes_js');
    
function sendbox_quotes_js() {
?>
<script src="http://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
<script type="text/javascript" src="<?=plugin_dir_url( __FILE__ ) ?>includes/quotes.js"></script>

<?php
}


  /**
 * Add a custom action to order actions select box on edit order page
 * Only added for paid orders that haven't fired this action yet
 *
 * @param array $actions order actions array to display
 * @return array - updated actions
 */
add_action('woocommerce_order_actions', 'add_sendbox_option', 10, 1 );
function add_sendbox_option( $actions ) {
    global $theorder;


    if ( is_array( $actions ) ) {
     
        $actions['get_sendbox_carrier_quotes'] = __( 'Ship via Sendbox' );
        
        
   }
    return $actions;
 
}






add_action('admin_footer', 'add_sendbox_carriers_form');
function add_sendbox_carriers_form() {
            global $origin_country;
            global $origin_state;
            global $origin_address;
            global $origin_street;
            global $origin_city;
            global $origin_phone;
             global $origin_email;
            global $auth_header;
            global $origin_name;
			global $use_max_carrier;
		    global $test_mode;

      

    echo ' 
    <div id="sendbox_ajax_wait" style="display:none;width:69px;height:89px;border:1px solid black;position:absolute;top:10%;left:50%;padding:2px;"><img src="'.plugin_dir_url( __FILE__ ).'images/demo_wait.gif" width="64" height="64" /><br>Loading..</div>
     <div class="modal fade" id="carriersModal" role="dialog" style="display:none;">
  
      
<center>
<div id="sendbox_quotes_div">

<h2>Choose Carrier</h2><form method="POST" action="#">
<input type="hidden" name="origin_country" id="origin_country" value="'.$origin_country.'" />
<input type="hidden" name="order_id" id="sendbox_order_id"  />
   <input type="hidden" name="origin_state"   id="origin_state" value="'.$origin_state.'" />
    <input type="hidden" name="origin_address"  id="origin_address" value="'.$origin_address.'" />
     <input type="hidden" name="origin_street"  id="origin_street" value="'.$origin_street.'" />
      <input type="hidden" name="origin_city"  id="origin_city" value="'.$origin_city.'" />
       <input type="hidden" name="origin_phone"  id="origin_phone" value="'.$origin_phone.'" />
        <input type="hidden" name="origin_email"  id="origin_email" value="'.$origin_email.'" />
        <input type="hidden" name="origin_name"  id="origin_name" value="'.$origin_name.'" />
       <input type="hidden" name="auth_header" id="auth_header" value="'.$auth_header.'" />
	   <input type="hidden" name="use_max_carrier" id="use_max_carrier" value="'.$use_max_carrier.'" />
	   <input type="hidden" name="test_mode" id="test_mode" value="'.$test_mode.'" />
         <input type="hidden" name="sendbox_rate_fee" id="sendbox_rate_fee"  />
        <input type="hidden" name="action" value="wpse10500" />
        

    <div class="origin_name">
        <label for="email">Carrier Rates:</label>
        <select name="sendbox_courier_id"  id="sendbox_courier_id" >
            
        </select>
    </div>
    <div >
    <br/>
        <button name="submit" class="button save_order button-primary" type="button" id="submit_post_sendbox_shipment">Post Shipment</button>
    </div>
    </div> 
    <div id="sendbox_shipment_response_div" style="display:none;">
    <h2 id="sendbox_shipment_header">Shipment Post:</h2>
     <div id="sendbox_ajax_wait2" style="width:69px;height:89px;border:1px solid black;padding:2px;"><img src="'.plugin_dir_url( __FILE__ ).'images/demo_wait.gif" width="32" height="32" /><br>Processing...</div>
    <div id="sendbox_shipment_response"></div>
    </div>
</form></center>
</div>';
}




add_action( 'wp_ajax_post_sendbox_shipment', 'post_sendbox_shipment' );

function post_sendbox_shipment() {
    
	//sanitize
	  $id=sanitize_text_field($_REQUEST['order_id']);
    $origin_country=sanitize_text_field($_REQUEST['origin_country']);
    $origin_state=sanitize_text_field($_REQUEST['origin_state']);
    $origin_address=sanitize_text_field($_REQUEST['origin_address']);
    $origin_street=sanitize_text_field($_REQUEST['origin_street']);
    $origin_city=sanitize_text_field($_REQUEST['origin_city']);
    $origin_phone=sanitize_text_field($_REQUEST['origin_phone']);
    $origin_name=sanitize_text_field($_REQUEST['origin_name']);
      $origin_email=sanitize_text_field($_REQUEST['origin_email']);
     $auth_header=sanitize_text_field($_REQUEST['auth_header']);
     $test_mode=sanitize_text_field($_REQUEST['test_mode']);
	$use_max_carrier=sanitize_text_field($_REQUEST['use_max_carrier']);

    $selected_rate_id=sanitize_text_field($_REQUEST['sendbox_courier_id']);
    $fee=sanitize_text_field($_REQUEST['sendbox_rate_fee']);
	
	//validate
	 if(!$origin_country)
	 {
		 $origin_country="";
	 }
	 if(!$origin_state)
	 {
		 $origin_state="";
	 }
	  if(!$origin_address)
	 {
		 $origin_address="";
	 }
	  if(!$origin_street)
	 {
		 $origin_street="";
	 }
	  if(!$origin_city)
	 {
		 $origin_city="";
	 }
         if(!$origin_email)
	 {
		 $origin_email="";
	 }
	  if(!$origin_phone)
	 {
		 $origin_phone="";
	 }
	  if(!$origin_name)
	 {
		 $origin_name="";
	 }
	  if(!$auth_header)
	 {
		 $auth_header="";
	 }
     if(!$test_mode)
	 {
		 $test_mode="yes";
	 }
     if(!$use_max_carrier)
	 {
		$use_max_carrier="no";
	 }
	  if(!$selected_rate_id)
	 {
		$selected_rate_id="";
	 }
	  if(!$fee)
	 {
		$fee="";
	 }
	
	

    $sendbox_options=[];
    $sendbox_options['origin_country']=$origin_country;
    $sendbox_options['origin_state']=$origin_state;
        $sendbox_options['origin_address']=$origin_address;
        $sendbox_options['origin_street']=$origin_street;
        $sendbox_options['origin_city']=$origin_city;
         $sendbox_options['origin_phone']=$origin_phone;
          $sendbox_options['origin_email']=$origin_email;
         $sendbox_options['origin_name']=$origin_name;
         $sendbox_options['auth_header']=$auth_header;
		 
		  $sendbox_options['use_max_carrier']=$use_max_carrier;
		  $test_mode=$test_mode;
    
     $order = new WC_Order($id);
    
$post=build_sendbox_shipment_payload($order,$selected_rate_id,$fee,$sendbox_options);
 

if($test_mode=="yes")
 $url= "http://api.sendbox.com.ng/v1/merchant/shipments";
else
 $url= "https://api.sendbox.ng/v1/merchant/shipments";

	
$args = array( 'headers' => array( 'Content-Type' => 'application/json','Authorization'=>$auth_header ), 'body' => $post);

$response = wp_remote_post( esc_url_raw( $url ), $args);

$response_code = wp_remote_retrieve_response_code( $response );
$response_body = json_decode(wp_remote_retrieve_body( $response ));



$tracking_code;
$status_code;
$output;

 if($response_code >= 400  & $response_code<500){
    
$output=$response_body;

}
 else if($response_code>=200 && $response_code<209){

$tracking_code=$response_body->{'code'};
$status_code=$response_body->{'status_code'};
$carrier=$response_body->{'courier'};
$carrier_name=$carrier->{'name'};
$output= "Tracking Number:".$tracking_code."; Status:". $status_code;
//update order status to shipment

 $order->update_status("wc-sendbox-posted-shipment"); 

}else{
$output='Sorry shipment could not be created.Try again later';


}

    echo $output;

    
    wp_die(); // this is required to terminate immediately and return a proper response
}



add_action( 'wp_ajax_load_sendbox_carriers', 'load_sendbox_carriers' );

function load_sendbox_carriers() {
    //load carriers
    $id=sanitize_text_field($_REQUEST['order_id']);
    $origin_country=sanitize_text_field($_REQUEST['origin_country']);
    $origin_state=sanitize_text_field($_REQUEST['origin_state']);
    $origin_address=sanitize_text_field($_REQUEST['origin_address']);
    $origin_street=sanitize_text_field($_REQUEST['origin_street']);
    $origin_city=sanitize_text_field($_REQUEST['origin_city']);
    $origin_phone=sanitize_text_field($_REQUEST['origin_phone']);
    $origin_name=sanitize_text_field($_REQUEST['origin_name']);
     $auth_header=sanitize_text_field($_REQUEST['auth_header']);
     $test_mode=sanitize_text_field($_REQUEST['test_mode']);
	 
	 //validate
	 if(!$origin_country)
	 {
		 $origin_country="";
	 }
	 if(!$origin_state)
	 {
		 $origin_state="";
	 }
	  if(!$origin_address)
	 {
		 $origin_address="";
	 }
	  if(!$origin_street)
	 {
		 $origin_street="";
	 }
	  if(!$origin_city)
	 {
		 $origin_city="";
	 }
	  if(!$origin_phone)
	 {
		 $origin_phone="";
	 }
	  if(!$origin_name)
	 {
		 $origin_name="";
	 }
	  if(!$auth_header)
	 {
		 $auth_header="";
	 }
     if(!$test_mode)
	 {
		 $test_mode="yes";
	 }
     if(!$use_max_carrier)
	 {
		$use_max_carrier="no";
	 }
	  if(!$selected_rate_id)
	 {
		$selected_rate_id="";
	 }
	  if(!$fee)
	 {
		$fee="";
	 }
	

    $sendbox_options=[];
    $sendbox_options['origin_country']=$origin_country;
    $sendbox_options['origin_state']=$origin_state;
        $sendbox_options['origin_address']=$origin_address;
        $sendbox_options['origin_street']=$origin_street;
        $sendbox_options['origin_city']=$origin_city;
         $sendbox_options['origin_phone']=$origin_phone;
         $sendbox_options['origin_name']=$origin_name;
         $sendbox_options['auth_header']=$auth_header;
		  $sendbox_options['test_mode']=$test_mode;
            global $origin_name;
    
     $order = new WC_Order($id);
    echo json_encode(get_sendbox_shipping_quotes_admin($order,$sendbox_options));
    
    

    wp_die(); // this is required to terminate immediately and return a proper response
}

      function get_sendbox_shipping_quotes_frontend($package)
    {
           
        global $auth_header;
		global $use_max_carrier;
		global $test_mode;
            
        $auth=$auth_header;
        $payload=build_sendbox_quote_payload_admin($package);
		if($test_mode)
        $url= "http://api.sendbox.com.ng/v1/merchant/shipments/delivery_quote";
	   else
		   $url= "https://api.sendbox.ng/v1/merchant/shipments/delivery_quote";

$args = array( 'headers' => array( 'Content-Type' => 'application/json','Authorization'=>$auth_header ), 'body' => $payload);

$response = wp_remote_post( esc_url_raw( $url ), $args);

$response_code = wp_remote_retrieve_response_code( $response );
$response_body = json_decode(wp_remote_retrieve_body( $response ));



if($response_code>=200 && $response_code <300)
{
if($use_max_carrier=="yes")
$shipping_fee=$response_body->{'max_quoted_fee'};
else
$shipping_fee=$response_body->{'min_quoted_fee'};
}else{
$shipping_fee=null;
}
return $shipping_fee;           

    }



   function build_sendbox_quote_payload_admin($package)
  {
         
            global $origin_country;
            global $origin_state;
            global $origin_address;
            global $origin_street;
            global $origin_city;
            global $origin_phone;
            global $origin_name;
           
    
        
         //billing street
        $street=$origin_street;
        //shipping street
        
        //items
        $items_string='[';
        $item_length=count($package['contents']);
       $fee=0;
       $j=0;

         foreach ( $package['contents'] as $item_id => $values ) {
            
         
           $fee+=floatval($values['line_total']);
            $output='{"name": "'.$values['data']->get_name().'",
            "weight": '.$values['data']->get_weight().',
            "package_size_code": "medium",
            "quantity": '.$values['quantity'].',
            "value": '.floatval($values['line_total']).',
            "amount_to_receive": '.floatval($values['line_total']).'
           }';

            if($j != ($item_length-1))
              $output.=',';
            $j++;
           $items_string.=$output;
         }//end for each item

         $items_string.=']';

  $billingCountry = $origin_country;

$nextday=$date = new DateTime();
$date->modify('+1 day');
$pickup_date= $date->format(DateTime::ATOM); 
  
$countries_obj   = new WC_Countries();
 $states=$countries_obj->get_states($billingCountry);
 

 $selected_state=$states[$origin_state];
$destination_state=$states[$package['destination']['state']];
 $countries=$countries_obj->get_allowed_countries();
  $billingCountry =$countries[$origin_country];
    $shippingCountry =$countries[$package['destination']['country']];

    $payload=' {
  "origin_name": "'.$origin_name.'",
  "origin_address": "'.$origin_address.'",
  "origin_phone": "'.$origin_phone.'",
  "origin_street":  "'.$origin_street.'",
  "origin_city": "'.$origin_city.'",
  "origin_state":"'. $selected_state.'",
  "origin_country": "'. $billingCountry.'",
  
  "destination_name": "",
  "destination_address":"",
  "destination_phone":"",
  "destination_street":"",
  "destination_city": "",
  "destination_state": "'.$destination_state.'",
  "destination_country": "'.    $shippingCountry.'",

  "delivery_priority_code": "next_day",
  
  "incoming_option_code": "pickup",
  "pickup_date":"'.$pickup_date.'",
  "delivery_type_code": "last_mile",
  
  "accept_value_on_delivery": true,
  "amount_to_receive": '.$fee.',
  "fee_payment_channel_code": "cash",
  "channel_code": "website",  
    "items": '.$items_string.'
 
}';
   

     return $payload;
       
  }



function get_sendbox_shipping_quotes_admin($order,$options)
    {          
             
        $auth=$options['auth_header'];
         $test_mode=$options['test_mode'];

        $payload=build_sendbox_shipping_quotes_payload($order,$options);

        if($test_mode=="yes")
		{
			
        $url= "http://api.sendbox.com.ng/v1/merchant/shipments/delivery_quote";
		}
	   else
	 {  
	 
	 $url= "https://api.sendbox.ng/v1/merchant/shipments/delivery_quote";
	   }
	  

	  
$args = array( 'headers' => array( 'Content-Type' => 'application/json','Authorization'=>$auth ), 'body' => $payload);

$response = wp_remote_post( esc_url_raw( $url ), $args);
$response_code = wp_remote_retrieve_response_code( $response );
$response_body = json_decode(wp_remote_retrieve_body( $response ));

if($response_code>=200 && $response_code <300)
{
    $response_body=$response_body;
return $response_body->{'rates'};
}
else
{
 $response_body=$response_body;
return $response_body;           
}
    }



  function build_sendbox_shipment_payload($order,$selected_rate_id,$fee,$options)
  {

      
           $origin_country=$options['origin_country'];
            $origin_state=$options['origin_state'];
             $origin_address=$options['origin_address'];
             $origin_street=$options['origin_street'];
             $origin_city=$options['origin_city'];
             $origin_phone=$options['origin_phone'];
             $origin_name=$options['origin_name'];
             $origin_email=$options['origin_email'];
             
            

          //billing street
          $street=$origin_street;
           //shipping street
        
        //items
        $items_string='[';
        $item_length=count($order->get_items());
       $fee=0;
       $j=0;

         foreach ( $order->get_items() as $item_id => $values ) {
            
         
           $fee+=floatval($values['line_total']);
            $output='{"name": "'.$values['name'].'",
            "weight": '.$values->get_product()->get_weight().',
            "package_size_code": "medium",
            "quantity": '.$values['quantity'].',
            "value": '.floatval($values['line_total']).',
            "amount_to_receive": '.floatval($values['line_total']).'
           }';

            
            $j++;
           $items_string.=$output;
         }//end for each item

          $items_string.=',{"name": "delivery fee",
            "weight": 0,
            "package_size_code": "medium",
            "quantity": 1,
            "value": '.$order->get_total_shipping().',
            "amount_to_receive": '.$order->get_total_shipping().'
           }';

         $items_string.=']';

  $billingCountry = $origin_country;

$nextday=$date = new DateTime();
$date->modify('+1 day');
$pickup_date= $date->format(DateTime::ATOM); 
 

$countries_obj   = new WC_Countries();
 $states=$countries_obj->get_states($billingCountry);

 

 $selected_state=$states[$origin_state];
$destination_state=$states[$order->get_shipping_state()];
 $countries=$countries_obj->get_allowed_countries();
  $billingCountry =$countries[$origin_country];
$shippingCountry=
$countries[$order->get_shipping_country()];


    $payload=' {
  "origin_name": "'.$origin_name.'",
  "origin_email": "'.$origin_email.'",
  "origin_phone": "'.$origin_phone.'",
  "origin_street":  "'.$street.'",
  "origin_city": "'.$origin_city.'",
  "origin_state":"'.$selected_state.'",
  "origin_country": "'. $billingCountry.'",
  
  "destination_name": "'.$order->get_shipping_first_name().' '.$order->get_shipping_last_name().'",
  "destination_address": "'.$order->get_shipping_address_1().'",
   "destination_email": "'.$order->get_billing_email().'",
  "destination_phone":  "'.$order->get_billing_phone().'",
  "destination_street":  "'.$order->get_shipping_address_1().'",
  "destination_city": "'.$order->get_shipping_city().'",
  "destination_state": "'.$destination_state.'",
  "destination_country": "'. $shippingCountry.'",

  "delivery_priority_code": "next_day",
  "delivery_callback":"'.get_site_url().'/sendbox_deliveryupdate" ,
  "finance_callback":"'.get_site_url().'/sendbox_financeupdate",
  "incoming_option_code": "pickup",
  "pickup_date":"'.$pickup_date.'",
  "delivery_type_code": "last_mile",
  "reference_code": "'.$order->get_id().'",
  
  "use_selected_rate": true,
  "selected_rate_id": "'.$selected_rate_id.'",
  "accept_value_on_delivery": true,
  "amount_to_receive": '.($order->get_subtotal()+$fee).',
  "fee_payment_channel_code": "cash",
  "channel_code": "website",
  
  "items": '.$items_string.'
}';

     return $payload;
       
  }



  
    function build_sendbox_shipping_quotes_payload($order,$options)
  {

           $origin_country=$options['origin_country'];
            $origin_state=$options['origin_state'];
             $origin_address=$options['origin_address'];
             $origin_street=$options['origin_street'];
             $origin_city=$options['origin_city'];
             $origin_phone=$options['origin_phone'];
             $origin_name=$options['origin_name'];
             
            

          //billing street
          $street=$origin_street;
           //shipping street
        
        //items
        $items_string='[';
        $item_length=count($order->get_items());
       $fee=0;
       $j=0;

         foreach ( $order->get_items() as $item_id => $values ) {
            
         
           $fee+=floatval($values['line_total']);
            $output='{"name": "'.$values['name'].'",
            "weight": '.$values->get_product()->get_weight().',
            "package_size_code": "medium",
            "quantity": '.$values['quantity'].',
            "value": '.floatval($values['line_total']).',
            "amount_to_receive": '.floatval($values['line_total']).'
           }';

           if($j != ($item_length-1))
              $output.=',';
            
            $j++;
           $items_string.=$output;
         }//end for each item
		 
		 

         $items_string.=']';

  $billingCountry = $origin_country;

$nextday=$date = new DateTime();
$date->modify('+1 day');
$pickup_date= $date->format(DateTime::ATOM); 


$countries_obj   = new WC_Countries();
 $states=$countries_obj->get_states($billingCountry);

 $selected_state=$states[$origin_state];
$destination_state=$states[$order->shipping_state];
 $countries=$countries_obj->get_allowed_countries();
  $billingCountry =$countries[$origin_country];
$shippingCountry=
$countries[$order->shipping_country];

    $payload=' {
  "origin_name": "'.$origin_name.'",
  "origin_address": "'.$origin_address.'",
  "origin_phone": "'.$origin_phone.'",
  "origin_street":  "'.$origin_street.'",
  "origin_city": "'.$origin_city.'",
  "origin_state":"'.$selected_state.'",
  "origin_country": "'. $billingCountry.'",
  
  "destination_name": "",
  "destination_address":"",
  "destination_phone":"",
  "destination_street":"",
  "destination_city": "",
  "destination_state": "'.$destination_state.'",
  "destination_country": "'.    $shippingCountry.'",

  "delivery_priority_code": "next_day",
  
  "incoming_option_code": "pickup",
  "pickup_date":"'.$pickup_date.'",
  "delivery_type_code": "last_mile",
  
  "accept_value_on_delivery": true,
  "amount_to_receive": '.$fee.',
  "fee_payment_channel_code": "cash",
  "channel_code": "website",  
    "items": '.$items_string.'
 
}';

  
     return $payload;

       
  }   

  add_action( 'init', 'sendbox_delivery_register_extra_page' );

function sendbox_delivery_register_extra_page()
{
    add_feed( 'sendbox_deliveryupdate', 'sendbox_delivery_callback' );
}

function sendbox_delivery_callback()
{

  $data = json_decode(file_get_contents('php://input'), true);
               
  $response=[];
            
if($data["reference_code"]!=null)
 {
  $order = new WC_Order($data["reference_code"]);
  
  $status_code=$data['status_code'];
  $status_from_sendbox=$data['status']['name'];

$order->update_status('wc-sendbox-updated-shipment');
  $response["status"]=200;
}
else
{          
 $response["status"]=300;
}
            
header('Content-Type: application/json');
echo json_encode($response);
  
}

 


  add_action( 'init', 'sendbox_50842_register_extra_page' );

function sendbox_50842_register_extra_page()
{
    add_feed( 'sendbox_financeupdate', 'sendbox_finance_callback' );
}

function sendbox_finance_callback()
{

  $data = json_decode(file_get_contents('php://input'), true);
               
  $response=[];
            
if($data["reference_code"]!=null)
 {
  $order = new WC_Order($data["reference_code"]);
  
  $status_code=$data['status_code'];
  $status_from_sendbox=$data['status']['name'];

$order->update_status('wc-sendbox-updated-shipment', __($status_from_sendbox, 'woocommerce'));
  $response["status"]=200;
}
else
{          
 $response["status"]=300;
}
            
header('Content-Type: application/json');
echo json_encode($response);
  
}
 
}

?>
