<?php

class WC_Wowmelo_Gateway extends WC_Payment_Gateway
{
    protected static $_instance = null;

    /**
     * Class constructor, more about it in Step 3
     */
    public function __construct()
    {

        $this->id = 'wowmelo'; // payment gateway plugin ID
        $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
        $this->has_fields = true; // in case you need a custom credit card form
        $this->method_title = 'Wowmelo Payment';
        $this->method_description = 'Intergrated Wowmelo payment gateway'; // will be displayed on the options page

        // gateways can support subscriptions, refunds, saved payment methods,
        // but in this tutorial we begin with simple payments
        $this->supports = array(
            'products'
        );

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->testmode = 'yes' === $this->get_option('testmode');
        $this->client_id = $this->testmode ? $this->get_option('sandbox_client_id') : $this->get_option('production_client_id');
        $this->client_secret = $this->testmode ? $this->get_option('sandbox_client_secret') : $this->get_option('production_client_secret');
        $this->api_endpoint = $this->testmode ? $this->get_option('sandbox_api_endpoint') : $this->get_option('production_api_endpoint');
        $this->wowmelo_access_token = '';

        // We need custom JavaScript to obtain a token
        // add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

        // You can also register a webhook here
        // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );

        $this->init_hooks();
    }

    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function init_hooks()
    {
        // This action hook saves the settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
    }

    /**
     * Plugin options, we deal with it in Step 3 too
     */
    public function init_form_fields()
    {
        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Enable/Disable',
                'label' => 'Enable Wowmelo Payment',
                'type' => 'checkbox',
                'description' => '',
                'default' => 'yes'
            ),
            'title' => array(
                'title' => 'Title',
                'type' => 'text',
                'default' => 'Thanh toán trả góp cùng Wowmelo',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => 'Description',
                'type' => 'textarea',
                'default' => 'Thanh toán qua hình thức trả góp với Wowmelo',
            ),
            'testmode' => array(
                'title' => 'Sandbox mode',
                'label' => 'Enable Sandbox Mode',
                'type' => 'checkbox',
                'default' => 'yes',
                'desc_tip' => true,
            ),
            'sandbox_api_endpoint' => array(
                'title' => 'Sandbox API Endpoint',
                'type' => 'text',
                'default' => 'https://sandbox-api.wowmelo.com/',
            ),
            'sandbox_client_id' => array(
                'title' => 'Sandbox Client ID',
                'type' => 'number'
            ),
            'sandbox_client_secret' => array(
                'title' => 'Sandbox Client Secret',
                'type' => 'text',
            ),
            'production_api_endpoint' => array(
                'title' => 'Production API Endpoint',
                'type' => 'text',
                'default' => 'https://api.wowmelo.com/'
            ),
            'production_client_id' => array(
                'title' => 'Production Client ID',
                'type' => 'number'
            ),
            'production_client_secret' => array(
                'title' => 'Production Client Secret',
                'type' => 'text'
            )
        );
    }

    /**
     * You will need it if you want your custom credit card form, Step 4 is about it
     */
    public function payment_fields()
    {
        global $woocommerce;
        $orderTotal = $woocommerce->cart->total;

        if ($this->description) {
            // display the description with <p> tags etc.
            echo wpautop(wp_kses_post($this->description));
        }

        $this->fetchToken();
        $this->fetchCalculationTable($orderTotal);

    }

    /*
      * Fields validation, more in Step 5
     */
    public function validate_fields()
    {
        if (empty($_POST['billing_first_name'])) {
            wc_add_notice('First name is missing', 'error');
            return false;
        }

        if (empty($_POST['billing_last_name'])) {
            wc_add_notice('Last name is missing', 'error');
            return false;
        }

        if (empty($_POST['billing_email'])) {
            wc_add_notice('Email is missing', 'error');
            return false;
        }

        if (empty($_POST['billing_phone'])) {
            wc_add_notice('Phone is missing', 'error');
            return false;
        }

        if (empty($_POST['billing_address_1'])) {
            wc_add_notice('Address is missing', 'error');
            return false;
        }

        if (empty($_POST['billing_city'])) {
            wc_add_notice('City is missing', 'error');
            return false;
        }
        return true;
    }

    /*
     * We're processing the payments here, everything about it is in Step 5
     */
    public function process_payment($order_id)
    {
        global $woocommerce;
        $customer_order = new WC_Order($order_id);
        $orderReceivedEnpoint = $customer_order->get_checkout_order_received_url();

        // we received the payment
        $customer_order->payment_complete();

        $woocommerce->cart->empty_cart();

        // some notes to customer (replace true with false to make it private)
        $wowmeloCheckoutUrl = $this->processWowmeloPayment($customer_order, $orderReceivedEnpoint);

        if (!is_wp_error($wowmeloCheckoutUrl)) {
            if ($wowmeloCheckoutUrl) {
                return array(
                    'result' => 'success',
                    'redirect' => $wowmeloCheckoutUrl
                );
            } else {
                wc_add_notice('Something wrong happened, please try again', 'error');
                return;
            }
        } else {
            wc_add_notice('Wowmelo connection error', 'error');
            return;
        }


    }

    public function fetchToken()
    {
        $wowmelo_access_token = get_transient('wowmelo_access_token');

        if (!$wowmelo_access_token) {
            $header = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ];

            $body = [
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret
            ];

            $postData = [
                'headers' => $header,
                'body' => wp_json_encode($body)
            ];

            $response = wp_remote_post($this->api_endpoint . 'oauth/token/', $postData);


            if (!is_wp_error($response)) {
                $result = json_decode(wp_remote_retrieve_body($response), true);

                set_transient('wowmelo_access_token', $result['access_token'], 60 * 15);
            } else {
                wc_add_notice('Wowmelo login error', 'error');
                return;
            }
        }
    }

    public function fetchCalculationTable($orderTotal)
    {
        $wowmelo_access_token = get_transient('wowmelo_access_token');

        if ($wowmelo_access_token) {
            $header = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $wowmelo_access_token
            ];

            $postData = [
                'headers' => $header,
            ];

            $response = wp_remote_get($this->api_endpoint . 'v1/package?total=' . $orderTotal, $postData);

            if (!is_wp_error($response)) {
                $packages = json_decode(wp_remote_retrieve_body($response), true);
            } else {
                wc_add_notice('Wowmelo packages error', 'error');
                return;
            }

            echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';

            // Add this action hook if you want your custom payment gateway to support it
            do_action('woocommerce_credit_card_form_start', $this->id);

            $paymentChoices = '';
            foreach ($packages as $key => $package) {
                $checked = $key == 0 ? "checked" : "";
                // I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
                $paymentChoices .= "<div class='form-row wowmelo-payment-options'>
                            <label style='padding-left: 20px; padding-right: 20px'>
                                <input name='package_id' type='radio' value={$package['id']} {$checked} />" .
                    $package['name']
                    . $this->renderCalculatedTable($orderTotal, $package['calculation']['table'], $package['calculation']['total'], $package['calculation']['different'])
                    . "</label>
                        </div>";
            }
            echo $paymentChoices;

            do_action('woocommerce_credit_card_form_end', $this->id);

            echo '<div class="clear"></div></fieldset>';

        }
    }

    public function renderCalculatedTable($total, $tables, $wowmeloTotal, $different)
    {
        $tbody = '';

        $formattedTotal = (number_format($total, 0, ',', '.'));
        $formattedWowmeloTotal = (number_format($wowmeloTotal, 0, ',', '.'));
        $formattedDifferent = (number_format($different, 0, ',', '.'));
        foreach ($tables as $key => $table) {
            $tbody .= '<tr>' . '<th>' . 'Đợt ' . ($key + 1) . '</th>' . '<td>' . (number_format($table['total'])) . 'đ' . '</td>' . '</tr>';
        }

        $tbody .= '<tr>' . '<th>' . 'Tổng trả góp' . '</th>' . '<td><strong>' . $formattedWowmeloTotal . 'đ' . '</strong></td>' . '</tr>';
        $tbody .= '<tr>' . '<th>' . 'Chênh lệch' . '</th>' . '<td><strong>' . $formattedDifferent . 'đ' . '</strong></td>' . '</tr>';

        return "
            <table class='calculation-table'>
                <thead>
                    <tr>
                        <th>Tổng đơn hàng</th>
                        <th>{$formattedTotal}đ</th>
                    </tr>
                </thead>            
                <tbody>
                    {$tbody}
                </tbody>
            </table>";
    }

    public function processWowmeloPayment($order, $redirectEndpoint)
    {
        $wowmelo_access_token = get_transient('wowmelo_access_token');
        $currentDate = date("ymdhis", strtotime("now"));

        if ($wowmelo_access_token) {
            $header = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $wowmelo_access_token
            ];

            $items = [];
            foreach ($order->get_items() as $item_id => $item) {
                $arrItem['id'] = $item->get_product_id();
                $arrItem['name'] = $item->get_name();
                $arrItem['quantity'] = $item->get_quantity();
                $arrItem['unit_price'] = $item->get_product()->get_price();
                $arrItem['total'] = $item->get_total();
                array_push($items, $arrItem);
            }

            $body = [
                'order_code' => $currentDate . $order->id,
                'package_id' => isset($_POST['package_id']) ? $_POST['package_id'] : 1,
                'first_payment_method' => 2,
                'base_price' => $order->total,
                'processing_fee' => 0,
                'discount_price' => 0,
                'total' => $order->total,
                'callback_url' => 'https://wowmelo.com',
                'redirect_url' => $redirectEndpoint,
                'store_address' => get_option('woocommerce_store_address') ?? '',
                'billing' => [
                    'customer_id' => $order->customer_id,
                    'name' => $order->billing_first_name . ' ' . $order->billing_last_name,
                    'nid' => $order->billing_nid ?? '',
                    'email' => $order->billing_email,
                    'phone' => $order->billing_phone,
                    'address' => $order->billing_address_1 ?? '',
                    'city' => $order->billing_city ?? '',
                    'district' => $order->billing_district ?? '',
                    'ward' => $order->billing_ward ?? ''
                ],
                'shipping' => [
                    'name' => ($order->shipping_first_name ? $order->shipping_first_name : '') . ' ' . ($order->shipping_last_name ? $order->shipping_last_name : ''),
                    'phone' => $order->shipping_phone ?? $order->billing_phone,
                    'address' => $order->shipping_address_1 ? $order->shipping_address_1 : $order->billing_address_1,
                    'city' => $order->billing_city ?? '',
                    'district' => $order->shipping_district ?? '',
                    'ward' => $order->shipping_ward ?? ''
                ],
                'items' => $items
            ];

            $postData = [
                'headers' => $header,
                'body' => wp_json_encode($body)
            ];

            $response = wp_remote_post($this->api_endpoint . 'v1/order', $postData);
            $result = json_decode(wp_remote_retrieve_body($response), true);

            return $result['checkout_url'];
        }
    }
}
