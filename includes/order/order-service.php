<?php
/**
 * Order Service - Business logic for orders
 *
 * @package    Kolai
 * @subpackage Kolai/includes/order
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Order Service class
 */
class Kolai_Order_Service {

    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    private function is_woocommerce_active() {
        return class_exists('WooCommerce');
    }

    /**
     * Create order from external request.
     *
     * @param array $payload
     * @return array
     */
    public function create_order($payload) {
        if (!$this->is_woocommerce_active()) {
            throw new Kolai_WooCommerce_Inactive_Exception();
        }

        if (!is_array($payload)) {
            throw new Kolai_Invalid_Order_Request_Exception('Invalid request body');
        }

        $buyer = isset($payload['buyer']) ? $payload['buyer'] : null;
        $billing = isset($payload['billingAddress']) ? $payload['billingAddress'] : null;
        $shipping = isset($payload['shippingAddress']) ? $payload['shippingAddress'] : null;
        $products = isset($payload['products']) ? $payload['products'] : null;
        $shipment_option_id = isset($payload['shipmentOptionId']) ? $payload['shipmentOptionId'] : null;
        $discount_amount = isset($payload['discountAmount']) ? $payload['discountAmount'] : null;

        $this->validate_buyer($buyer);
        Kolai_Address::validate_address($billing);
        Kolai_Address::validate_address($shipping);
        $items = $this->validate_products($products);

        if (empty($shipment_option_id)) {
            throw new Kolai_Invalid_Shipment_Option_Exception('shipmentOptionId is required');
        }

        $order = wc_create_order();
        if (is_wp_error($order)) {
            throw new Kolai_Internal_Error_Exception('Order creation failed');
        }

        $customer_id = $this->resolve_customer_id($buyer['email']);
        if ($customer_id) {
            $order->set_customer_id($customer_id);
        }

        $order->set_address(Kolai_Address::build_order_address($billing, $buyer, true), 'billing');
        $order->set_address(Kolai_Address::build_order_address($shipping, $buyer, false), 'shipping');

        foreach ($items as $item) {
            $order->add_product($item['product'], $item['quantity']);
        }

        $shipping_service = new Kolai_Shipping_Service();
        $rate = $shipping_service->get_rate_by_id($this->extract_product_ids($items), $shipping, $shipment_option_id);

        $shipping_item = new WC_Order_Item_Shipping();
        $shipping_item->set_shipping_rate($rate);
        $order->add_item($shipping_item);

        $order->set_currency(get_woocommerce_currency());
        $order->set_payment_method('kolai-app');
        $order->set_payment_method_title('Kolai App');

        $order->calculate_totals();

        if (!is_null($discount_amount)) {
            $this->apply_discount($order, $discount_amount);
        }

        $order->set_status('processing');
        $order->save();

        $order->reduce_order_stock();

        return array(
            'orderId' => $order->get_id(),
            'orderNumber' => $order->get_order_number(),
            'status' => $order->get_status(),
            'total' => (float) $order->get_total(),
            'currency' => $order->get_currency(),
            'paymentMethod' => $order->get_payment_method(),
        );
    }

    /**
     * Validate buyer info.
     *
     * @param array $buyer
     * @return void
     */
    private function validate_buyer($buyer) {
        if (!is_array($buyer) || empty($buyer['email'])) {
            throw new Kolai_Invalid_Order_Request_Exception('buyer.email is required');
        }
    }

    /**
     * Validate product items and check stock.
     *
     * @param array $products
     * @return array
     */
    private function validate_products($products) {
        if (!is_array($products) || empty($products)) {
            throw new Kolai_Invalid_Product_List_Exception('Products list is required');
        }

        $items = array();
        foreach ($products as $item) {
            if (!is_array($item) || empty($item['productId'])) {
                throw new Kolai_Invalid_Product_List_Exception('productId is required');
            }

            $quantity = isset($item['quantity']) ? (int) $item['quantity'] : 0;
            if ($quantity < 1) {
                throw new Kolai_Invalid_Product_List_Exception('quantity must be at least 1');
            }

            $product = wc_get_product((int) $item['productId']);
            if (!$product) {
                throw new Kolai_Product_Not_Found_Exception();
            }

            $this->assert_stock($product, $quantity);

            $items[] = array(
                'product' => $product,
                'quantity' => $quantity,
            );
        }

        return $items;
    }

    /**
     * Check stock rules.
     *
     * @param WC_Product $product
     * @param int        $quantity
     * @return void
     */
    private function assert_stock($product, $quantity) {
        if (!$product->is_in_stock() && !$product->backorders_allowed()) {
            throw new Kolai_Insufficient_Stock_Exception('Product is out of stock');
        }

        if ($product->managing_stock()) {
            $stock = (int) $product->get_stock_quantity();
            if ($stock < $quantity && !$product->backorders_allowed()) {
                throw new Kolai_Insufficient_Stock_Exception('Insufficient stock quantity');
            }
        }
    }

    /**
     * Resolve customer id by email.
     *
     * @param string $email
     * @return int
     */
    private function resolve_customer_id($email) {
        $user = get_user_by('email', $email);
        return $user ? (int) $user->ID : 0;
    }

    /**
     * Extract product ids from items.
     *
     * @param array $items
     * @return array
     */
    private function extract_product_ids($items) {
        $ids = array();
        foreach ($items as $item) {
            $ids[] = $item['product']->get_id();
        }
        return $ids;
    }

    /**
     * Apply discount to order (tax included).
     *
     * @param WC_Order $order
     * @param mixed    $discount_amount
     * @return void
     */
    private function apply_discount($order, $discount_amount) {
        if (!is_numeric($discount_amount)) {
            throw new Kolai_Discount_Exceeds_Total_Exception('discountAmount must be numeric');
        }

        $discount = (float) $discount_amount;
        if ($discount <= 0) {
            throw new Kolai_Invalid_Order_Request_Exception('discountAmount must be greater than 0');
        }

        $total_before = (float) $order->get_total();
        if ($discount > $total_before) {
            throw new Kolai_Discount_Exceeds_Total_Exception();
        }

        $fee = new WC_Order_Item_Fee();
        $fee->set_name('Discount');
        $fee->set_amount(-$discount);
        $fee->set_total(-$discount);
        $fee->set_tax_status('none');
        $fee->set_taxes(array());

        $order->add_item($fee);
        $order->calculate_totals();
    }
}
