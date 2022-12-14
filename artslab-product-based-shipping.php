<?php

/**
 * Plugin Name: Artslab Product Based Shipping
 * Plugin URI: https://artslabcreatives.com
 * Description: Products that will increase shipping based on the count and not the weight
 * Version: 1.0.0
 * Requires at least: 6.1
 * Requires PHP: 7.4
 * Author: Artslab Creatives
 * Author URI: https://artslabcreatives.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI: https://artslabcreatives.com
 * Text Domain: artslab-product-based-shipping
 * Domain Path: localization
 *
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
    function artslab_shipping_method() {
        if ( ! class_exists( 'Artslab_Shipping_Method' ) ) {
            class Artslab_Shipping_Method extends WC_Shipping_Method {

                public $params = "";
                public $products = array();
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct($instance_id = 0) {

                    $this->id                    = 'artslab_product_count_method';
                    $this->instance_id           = absint($instance_id);
                    $this->method_title          = __('Product Count based Shipping Method');
                    $this->method_description    = __('Product Count based shipping method by Artslab Creatives');
                    $this->supports              = array(
                        /*'settings',*/
                        'shipping-zones',
                        'instance-settings',
                        'instance-settings-modal',
                    );

                    $args = array(
                        'taxonomy'     => 'product_cat',
                        'orderby'      => 'name',
                        'hierarchical' => true,
                        'hide_empty'   => false
                    );
                    $all_categories = get_categories( $args );
                    $categories = array();
                    foreach ($all_categories as $key => $parent) {
                        $categories = $this->category_maker($parent, $categories);
                    }

                    $currency = "LKR";
                    //$currencies = $this->get_field('currency', array(get_option('woocommerce_currency')));
                    $form_fields = array(
                        'enabled' => array(
                            'title'         => __('Enable/Disable'),
                            'type'          => 'checkbox',
                            'label'         => __('Enable this shipping method'),
                            'default'       => 'yes',
                            'key'           => 'checkbox',
                            'currency'      => 'CC',
                            'neotype'       => 'enabled'
                        ),
                        'title' => array(
                            'title'         => __('Title'),
                            'type'          => 'text',
                            'description'   => __('This shows the title at the cart and checkout page'),
                            'default'       => __('Product Count based Shipping Method'),
                            'desc_tip'      => true,
                            'key'           => 'title',
                            'currency'      => 'CV',
                            'neotype'       => 'enabled'
                        ),
                    );
                    $this->products = array();

                    $form_fields['product_all'] = array(
                        'title'         => __( 'All Products'),
                        'type'          => 'title',
                        'description'   => __( 'Pricing rules apply to all products' ),
                        'callback'      => 'ecec',
                    );
                    //foreach ($currencies as $key => $currency) {
                        $form_fields['all_'.$currency.'_cost'] = array(
                            'title'         => __($currency.' Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the first product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                        );
                        $form_fields['all_'.$currency.'_cost_additional'] = array(
                            'title'         => __($currency.' Each Additional Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the every addtional product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                        );
                    //}

                    $form_fields['product_category_0_title'] = array(
                        'title'         => __( 'Product Category 0'),
                        'type'          => 'title',
                        'description'   => __( 'If there is a seperate product category these pricing rules apply to' ),
                        'callback'      => 'ecec',
                    );
                    $form_fields['product_category_0_enabled'] = array(
                        'title'         => __( 'Enable/Disable Category Pricing' ),
                        'type'          => 'checkbox',
                        'label'         => __( 'Enable this product category method it will be active for only one category'),
                        'default'       => 'yes',
                    );
                    $form_fields['product_category_0'] = array(
                        'title'         => __( 'Product Category 0' ),
                        'type'          => 'multiselect',
                        'select_buttons'    => true,
                        'class'         => 'wc-enhanced-select',
                        'options'       => $categories,
                        'description'   => __( 'Which product category does this apply to' ),
                        'desc_tip'      => true,
                    );

                    //foreach ($currencies as $key => $currency) {
                        $form_fields['product_0_'.$currency.'_cost'] = array(
                            'title'         => __($currency.' Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the first product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost'
                        );
                        $form_fields['product_0_'.$currency.'_cost_additional'] = array(
                            'title'         => __($currency.' Each Additional Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the every addtional product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost_additional'
                        );
                    //}

                    /******** Product 1*/

                    $form_fields['product_category_1_title'] = array(
                        'title'         => __( 'Product Category 1'),
                        'type'          => 'title',
                        'description'   => __( 'If there is a seperate product category these pricing rules apply to' ),
                        'callback'      => 'ecec',
                    );
                    $form_fields['product_category_1_enabled'] = array(
                        'title'         => __( 'Enable/Disable Category Pricing' ),
                        'type'          => 'checkbox',
                        'label'         => __( 'Enable this product category method it will be active for only one category'),
                        'default'       => 'yes',
                    );
                    $form_fields['product_category_1'] = array(
                        'title'         => __( 'Product Category 1' ),
                        'type'          => 'multiselect',
                        'select_buttons'    => true,
                        'class'         => 'wc-enhanced-select',
                        'options'       => $categories,
                        'description'   => __( 'Which product category does this apply to' ),
                        'desc_tip'      => true,
                    );

                    //foreach ($currencies as $key => $currency) {
                        $form_fields['product_1_'.$currency.'_cost'] = array(
                            'title'         => __($currency.' Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the first product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost'
                        );
                        $form_fields['product_1_'.$currency.'_cost_additional'] = array(
                            'title'         => __($currency.' Each Additional Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the every addtional product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost_additional'
                        );
                    //}

                    /********** Product 2*/

                    $form_fields['product_category_2_title'] = array(
                        'title'         => __( 'Product Category 2'),
                        'type'          => 'title',
                        'description'   => __( 'If there is a seperate product category these pricing rules apply to' ),
                        'callback'      => 'ecec',
                    );
                    $form_fields['product_category_2_enabled'] = array(
                        'title'         => __( 'Enable/Disable Category Pricing' ),
                        'type'          => 'checkbox',
                        'label'         => __( 'Enable this product category method it will be active for only one category'),
                        'default'       => 'yes',
                    );
                    $form_fields['product_category_2'] = array(
                        'title'         => __( 'Product Category 2' ),
                        'type'          => 'multiselect',
                        'select_buttons'    => true,
                        'class'         => 'wc-enhanced-select',
                        'options'       => $categories,
                        'description'   => __( 'Which product category does this apply to' ),
                        'desc_tip'      => true,
                    );

                    //foreach ($currencies as $key => $currency) {
                        $form_fields['product_2_'.$currency.'_cost'] = array(
                            'title'         => __($currency.' Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the first product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost'
                        );
                        $form_fields['product_2_'.$currency.'_cost_additional'] = array(
                            'title'         => __($currency.' Each Additional Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the every addtional product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost_additional'
                        );
                    //}

                    /********** Product 3*/

                    $form_fields['product_category_3_title'] = array(
                        'title'         => __( 'Product Category 3'),
                        'type'          => 'title',
                        'description'   => __( 'If there is a seperate product category these pricing rules apply to' ),
                        'callback'      => 'ecec',
                    );
                    $form_fields['product_category_3_enabled'] = array(
                        'title'         => __( 'Enable/Disable Category Pricing' ),
                        'type'          => 'checkbox',
                        'label'         => __( 'Enable this product category method it will be active for only one category'),
                        'default'       => 'yes',
                    );
                    $form_fields['product_category_3'] = array(
                        'title'         => __( 'Product Category 3' ),
                        'type'          => 'multiselect',
                        'select_buttons'    => true,
                        'class'         => 'wc-enhanced-select',
                        'options'       => $categories,
                        'description'   => __( 'Which product category does this apply to' ),
                        'desc_tip'      => true,
                    );

                    //foreach ($currencies as $key => $currency) {
                        $form_fields['product_3_'.$currency.'_cost'] = array(
                            'title'         => __($currency.' Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the first product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost'
                        );
                        $form_fields['product_3_'.$currency.'_cost_additional'] = array(
                            'title'         => __($currency.' Each Additional Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the every addtional product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost_additional'
                        );
                    //}

                    /********** Product 4*/

                    $form_fields['product_category_4_title'] = array(
                        'title'         => __( 'Product Category 4'),
                        'type'          => 'title',
                        'description'   => __( 'If there is a seperate product category these pricing rules apply to' ),
                        'callback'      => 'ecec',
                    );
                    $form_fields['product_category_4_enabled'] = array(
                        'title'         => __( 'Enable/Disable Category Pricing' ),
                        'type'          => 'checkbox',
                        'label'         => __( 'Enable this product category method it will be active for only one category'),
                        'default'       => 'yes',
                    );
                    $form_fields['product_category_4'] = array(
                        'title'         => __( 'Product Category 4' ),
                        'type'          => 'multiselect',
                        'select_buttons'    => true,
                        'class'         => 'wc-enhanced-select',
                        'options'       => $categories,
                        'description'   => __( 'Which product category does this apply to' ),
                        'desc_tip'      => true,
                    );

                    //foreach ($currencies as $key => $currency) {
                        $form_fields['product_4_'.$currency.'_cost'] = array(
                            'title'         => __($currency.' Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the first product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost'
                        );
                        $form_fields['product_4_'.$currency.'_cost_additional'] = array(
                            'title'         => __($currency.' Each Additional Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the every addtional product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost_additional'
                        );
                    //}

                    /********** Product 5*/

                    $form_fields['product_category_5_title'] = array(
                        'title'         => __( 'Product Category 5'),
                        'type'          => 'title',
                        'description'   => __( 'If there is a seperate product category these pricing rules apply to' ),
                        'callback'      => 'ecec',
                    );
                    $form_fields['product_category_5_enabled'] = array(
                        'title'         => __( 'Enable/Disable Category Pricing' ),
                        'type'          => 'checkbox',
                        'label'         => __( 'Enable this product category method it will be active for only one category'),
                        'default'       => 'yes',
                    );
                    $form_fields['product_category_5'] = array(
                        'title'         => __( 'Product Category 5' ),
                        'type'          => 'multiselect',
                        'select_buttons'    => true,
                        'class'         => 'wc-enhanced-select',
                        'options'       => $categories,
                        'description'   => __( 'Which product category does this apply to' ),
                        'desc_tip'      => true,
                    );

                    //foreach ($currencies as $key => $currency) {
                        $form_fields['product_5_'.$currency.'_cost'] = array(
                            'title'         => __($currency.' Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the first product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost'
                        );
                        $form_fields['product_5_'.$currency.'_cost_additional'] = array(
                            'title'         => __($currency.' Each Additional Product Cost'),
                            'type'          => 'price',
                            'description'   => __($currency.' cost of the every addtional product'),
                            'default'       => __('1'),
                            'desc_tip'      => true,
                            'currency'      => $currency,
                            'neotype'       => 'cost_additional'
                        );
                    //}
                    
                    $this->instance_form_fields = $form_fields;

                    $this->enabled                 = $this->get_option('enabled');
                    $this->title                   = $this->get_option('title');

                        //add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                    $this->init();
                }
                /**
                     * Init your settings
                     *
                     * @access public
                     * @return void
                     */
                function init() {
                    // Load the settings API
                    $this->init_form_fields(); 
                    $this->init_settings(); 

                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                public function process_admin_options()
                {
                    if ( ! $this->instance_id ) {
                        return parent::process_admin_options();
                    }
                    // Check we are processing the correct form for this instance.
                    if ( ! isset( $_REQUEST['instance_id'] ) || absint( $_REQUEST['instance_id'] ) !== $this->instance_id ) { // WPCS: input var ok, CSRF ok.
                        return false;
                    }

                    $this->init_instance_settings();

                    $post_data = $this->get_post_data();

                    if(!is_array($this->instance_settings['products'])){
                        $this->instance_settings['products'] = array();
                    }

                    foreach ( $this->get_instance_form_fields() as $key => $field ) {

                        if ( 'title' !== $this->get_field_type( $field ) ) {
                            try {
                                $this->instance_settings[$key] = $this->get_field_value( $key, $field, $post_data );
                            } catch ( Exception $e ) {
                                $this->add_error( $e->getMessage() );
                            }
                        }
                    }

                    return update_option( $this->get_instance_option_key(), apply_filters( 'woocommerce_shipping_' . $this->id . '_instance_settings_values', $this->instance_settings, $this ), 'yes' );
                }

                /**
                 * @param $field
                 * @param string $default
                 *
                 * @return string
                 */
                public function get_field( $field, $default = '' ) {
                    global $wmc_settings;
                    $params = $wmc_settings;

                    if ( $this->params ) {
                        $params = $this->params;
                    } else {
                        $this->params = $params;
                    }
                    if ( isset( $params[ $field ] ) && $field ) {
                        return $params[ $field ];
                    } else {
                        return $default;
                    }
                }

                public $r = "";
                /**
                 * calculate_shipping function.
                 * @param array $package (default: array())
                 */
                public function calculate_shipping( $package = array() ) {
                    $this->setting2s = WOOMULTI_CURRENCY_F_Data::get_ins();
                    $cost = 0;
                    $cost_additional = 0;
                    $wc = 'LKR';//$this->setting2s->get_current_currency();

                    //Not used right now
                    $country = $package["destination"]["country"];
                    $contents = $package['contents'];
                    /*
                    Add the initial costs
                    */
                    //$cost += $all_cost;
                    foreach ($contents as $item_id => $values ) 
                    { 
                        $_product = $values['data'];
                        
                        $categories = $_product->get_category_ids();

                        if($_product->get_type() == "variation"){
                            $p = wc_get_product($_product->get_parent_id());
                            $categories = $p->get_category_ids();
                        }

                        $currencies = $this->get_field('currency', array(get_option('woocommerce_currency')));
                        
                        $all_cost_additional = $this->instance_settings['all_'.$wc.'_cost_additional'];
                        $all_cost = $this->instance_settings['all_'.$wc.'_cost'];

                        $product_category0 = $this->instance_settings['product_category_0'];
                        $product_0_cost_additional = $this->instance_settings['product_0_'.$wc.'_cost_additional'];
                        $product_0_cost = $this->instance_settings['product_0_'.$wc.'_cost'];

                        $product_category1 = $this->instance_settings['product_category_1'];
                        $product_1_cost_additional = $this->instance_settings['product_1_'.$wc.'_cost_additional'];
                        $product_1_cost = $this->instance_settings['product_1_'.$wc.'_cost'];

                        //Product 2
                        $product_category2 = $this->instance_settings['product_category_2'];
                        $product_2_cost_additional = $this->instance_settings['product_2_'.$wc.'_cost_additional'];
                        $product_2_cost = $this->instance_settings['product_2_'.$wc.'_cost'];

                        //Product 3
                        $product_category3 = $this->instance_settings['product_category_3'];
                        $product_3_cost_additional = $this->instance_settings['product_3_'.$wc.'_cost_additional'];
                        $product_3_cost = $this->instance_settings['product_3_'.$wc.'_cost'];

                        //Product 4
                        $product_category4 = $this->instance_settings['product_category_4'];
                        $product_4_cost_additional = $this->instance_settings['product_4_'.$wc.'_cost_additional'];
                        $product_4_cost = $this->instance_settings['product_4_'.$wc.'_cost'];

                        //Product 5
                        $product_category5 = $this->instance_settings['product_category_5'];
                        $product_5_cost_additional = $this->instance_settings['product_5_'.$wc.'_cost_additional'];
                        $product_5_cost = $this->instance_settings['product_5_'.$wc.'_cost'];

                        /*error_log(json_encode($product_category0));
                        error_log(json_encode($product_category1));
                        error_log(json_encode($product_category2));
                        error_log(json_encode($product_category3));
                        error_log(json_encode($product_category4));
                        error_log(json_encode($product_category5));

                        is_array($product_category0) ? error_log(json_encode(count(array_intersect($product_category0, $categories)))) : null;
                        is_array($product_category1) ? error_log(json_encode(count(array_intersect($product_category1, $categories)))) : null;
                        is_array($product_category2) ? error_log(json_encode(count(array_intersect($product_category2, $categories)))) : null;
                        is_array($product_category3) ? error_log(json_encode(count(array_intersect($product_category3, $categories)))) : null;
                        is_array($product_category4) ? error_log(json_encode(count(array_intersect($product_category4, $categories)))) : null;
                        is_array($product_category5) ? error_log(json_encode(count(array_intersect($product_category5, $categories)))) : null;*/

                        if(is_array($product_category0) && count(array_intersect($product_category0, $categories)) != 0){
                            if($values['quantity'] != 1){
                                $cost += ($product_0_cost_additional * ($values['quantity'] - 1)) + $product_0_cost;
                            }else{
                                $cost += $product_0_cost;//* ($values['quantity']);
                            }
                        }elseif(is_array($product_category1) && count(array_intersect($product_category1, $categories)) != 0){
                            if($values['quantity'] != 1){
                                $cost += ($product_1_cost_additional * ($values['quantity'] - 1)) + $product_1_cost;
                            }else{
                                $cost += $product_1_cost;//($values['quantity']);
                            }
                        }elseif(is_array($product_category2) && count(array_intersect($product_category2, $categories)) != 0){
                            //Product 2
                            if($values['quantity'] != 1){
                                $cost += ($product_2_cost_additional * ($values['quantity'] - 1)) + $product_2_cost;
                            }else{
                                $cost += $product_2_cost; // ($values['quantity']);
                            }
                        }elseif(is_array($product_category3) && count(array_intersect($product_category3, $categories)) != 0){
                            //Product 3
                            if($values['quantity'] != 1){
                                $cost += ($product_3_cost_additional * ($values['quantity'] - 1)) + $product_3_cost;
                            }else{
                                $cost += $product_3_cost;//($values['quantity']);
                            }
                        }elseif(is_array($product_category4) && count(array_intersect($product_category4, $categories)) != 0){
                            //Product 4
                            if($values['quantity'] != 1){
                                $cost += ($product_4_cost_additional * ($values['quantity'] - 1)) + $product_4_cost;
                            }else{
                                $cost += $product_4_cost;//($values['quantity']);
                            }
                        }elseif(is_array($product_category5) && count(array_intersect($product_category5, $categories)) != 0){
                            //Product 5
                            if($values['quantity'] != 1){
                                $cost += ($product_5_cost_additional * ($values['quantity'] - 1)) + $product_5_cost;
                            }else{
                                $cost += $product_5_cost;//($values['quantity']);
                            }
                        }else{
                            if($values['quantity'] != 1){
                                $cost += ($all_cost_additional * ($values['quantity'] - 1)) + $all_cost;
                            }else{
                                $cost += $all_cost;// ($values['quantity']);
                            }
                        }
                    }
                    $this->add_rate( array(
                        'id'    => $this->id . $this->instance_id,
                        //'label' => $this->title.' '.$cost.' '.wmc_get_price($cost, $wc).' '.wmc_get_price($cost, 'AUD'),
                        'label' => $this->title,
                        'cost'  => $cost / wmc_get_price(1, $wc, true),
                    ));
                }

                /**
                 * Add a shipping rate. If taxes are not set they will be calculated based on cost.
                 *
                 * @param array $args Arguments (default: array()).
                 */
                public function add_rate( $args = array() ) {
                    $args = apply_filters(
                        'woocommerce_shipping_method_add_rate_args',
                        wp_parse_args(
                            $args,
                            array(
                                'id'             => $this->get_rate_id(), // ID for the rate. If not passed, this id:instance default will be used.
                                'label'          => '', // Label for the rate.
                                'cost'           => '0', // Amount or array of costs (per item shipping).
                                'taxes'          => '', // Pass taxes, or leave empty to have it calculated for you, or 'false' to disable calculations.
                                'calc_tax'       => 'per_order', // Calc tax per_order or per_item. Per item needs an array of costs.
                                'meta_data'      => array(), // Array of misc meta data to store along with this rate - key value pairs.
                                'package'        => false, // Package array this rate was generated for @since 2.6.0.
                                'price_decimals' => wc_get_price_decimals(),
                            )
                        ),
                        $this
                    );

                    // ID and label are required.
                    if ( ! $args['id'] || ! $args['label'] ) {
                        return;
                    }

                    // Total up the cost.
                    $total_cost = is_array( $args['cost'] ) ? array_sum( $args['cost'] ) : $args['cost'];
                    $taxes      = $args['taxes'];

                    // Taxes - if not an array and not set to false, calc tax based on cost and passed calc_tax variable. This saves shipping methods having to do complex tax calculations.
                    if ( ! is_array( $taxes ) && false !== $taxes && $total_cost > 0 && $this->is_taxable() ) {
                        $taxes = 'per_item' === $args['calc_tax'] ? $this->get_taxes_per_item( $args['cost'] ) : WC_Tax::calc_shipping_tax( $total_cost, WC_Tax::get_shipping_tax_rates() );
                    }

                    // Round the total cost after taxes have been calculated.
                    $total_cost = wc_format_decimal( $total_cost, $args['price_decimals'] );

                    // Create rate object.
                    $rate = new WC_Shipping_Rate();
                    $rate->set_id( $args['id'] );
                    $rate->set_method_id( $this->id );
                    $rate->set_instance_id( $this->instance_id );
                    $rate->set_label( $args['label'] );
                    $rate->set_cost( $total_cost );
                    $rate->set_taxes( $taxes );

                    if ( ! empty( $args['meta_data'] ) ) {
                        foreach ( $args['meta_data'] as $key => $value ) {
                            $rate->add_meta_data( $key, $value );
                        }
                    }

                    // Store package data.
                    if ( $args['package'] ) {
                        $items_in_package = array();
                        foreach ( $args['package']['contents'] as $item ) {
                            $product            = $item['data'];
                            $items_in_package[] = $product->get_name() . ' &times; ' . $item['quantity'];
                        }
                        $rate->add_meta_data( __( 'Items', 'woocommerce' ), implode( ', ', $items_in_package ) );
                    }

                    $this->rates[ $args['id'] ] = apply_filters( 'woocommerce_shipping_method_add_rate', $rate, $args, $this );
                }


                /**
                 * Generate Text Input HTML.
                 *
                 * @param string $key Field key.
                 * @param array  $data Field data.
                 * @since  1.6.0
                 * @return string
                 */
                public function generate_hidden_html( $key, $data ) {
                    $field_key = $this->get_field_key( $key );
                    $defaults  = array(
                        'title'             => '',
                        'disabled'          => false,
                        'class'             => '',
                        'css'               => '',
                        'placeholder'       => '',
                        'type'              => 'text',
                        'desc_tip'          => false,
                        'description'       => '',
                        'custom_attributes' => array(),
                    );

                    $data = wp_parse_args( $data, $defaults );

                    ob_start();
                    ?>
                    <?php echo $this->get_description_html( $data ); // WPCS: XSS ok. ?>
                    <tr style="display: none;" valign="top">
                        <th scope="row" class="titledesc">
                            <label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // WPCS: XSS ok. ?></label>
                        </th>
                        <td class="forminp">
                            <fieldset>
                                <input class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" value="<?php echo esc_attr( $this->get_option( $key ) ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); // WPCS: XSS ok. ?> />
                                <legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
                            </fieldset>
                        </td>
                    </tr>
                    <?php

                    return ob_get_clean();
                }

                public function category_maker($category, $categories)
                {
                    // code...
                    if ($category->category_parent == $category->term_id || $category->category_parent == 0) {
                        $categories[$category->term_id] = $category->cat_name;
                    }
                    $args2 = array(
                        'taxonomy'     => 'product_cat',
                        'child_of'     => $category->term_id,
                        'parent'       => $category->term_id,
                        'hide_empty'   => false,
                        'hierarchical' => true,
                    );
                    $sub_cats = get_categories($args2);
                    if($sub_cats) {
                        foreach($sub_cats as $sub_category) {
                            if ($sub_category->category_parent == $category->term_id) {
                                $categories[$sub_category->term_id] = " - ".$sub_category->cat_name;
                            }
                        }
                    }
                    return $categories;
                }                
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'artslab_shipping_method' );
 
    function add_artslab_shipping_method( $methods ) {
        $methods[] = 'Artslab_Shipping_Method';
        return $methods;
    }
 

    add_filter( 'woocommerce_shipping_methods', 'register_artslab_shipping_method' );

    function register_artslab_shipping_method( $methods ) {
        $methods[ 'artslab_product_count_method' ] = 'Artslab_Shipping_Method';
        return $methods;
    }
 
    //add_action( 'woocommerce_review_order_before_cart_contents', 'artslab_validate_order' , 10 );
    //add_action( 'woocommerce_after_checkout_validation', 'artslab_validate_order' , 10 );
}

require_once( __DIR__ . '/updater.php' );
new ALCPBSUpdater();