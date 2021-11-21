<?php
/* Checking MONEO leftovers by WC product SKU.
*/

class Moneo {

    const BASE_URI = 'https://vinson.moneo.lv:8004/api/';

    const OUT_OF_STOCK = 'outofstock';
    const IN_STOCK = 'instock' ;

    private $products;

    public function __construct() {
        $this->products = null;
    }

    public function sync_residue() {
        error_log("\nAnother Sync with MONEO started at: ". time(), 3, "/var/www/ttatem/data/www/procosmetics.lv/wp-content/plugins/moneo-api-sync/error.log");
        $this->load_products();

        if (null == $this->products) {
            error_log("\nNo products were found". time(), 3, "/var/www/ttatem/data/www/procosmetics.lv/wp-content/plugins/moneo-api-sync/error.log");
            return;
        }

        foreach ($this->products as $product) {
            $leftover = $this->get_leftover($product->get_sku());
            //error_log("\nleftover for SKU: ". $product->get_sku() . ' - ' . $leftover, 3, "/var/www/ttatem/data/www/procosmetics.lv/wp-content/plugins/moneo-api-sync/error.log");
            if (null == $leftover) {
                error_log("\nCould not get leftover for SKU: ". $product->get_sku(), 3, "/var/www/ttatem/data/www/procosmetics.lv/wp-content/plugins/moneo-api-sync/error.log");
                continue;
            }
            if ( (int) $leftover == 0 ) {
                $product->set_stock_status(self::OUT_OF_STOCK);
                $product->set_stock_quantity($leftover);
            }
            if ( (int) $leftover > 0 ) {
                $product->set_stock_status(self::IN_STOCK);
                $product->set_stock_quantity($leftover);
            }
            $product->save();
        }
    }

    private function load_products() {
        $args = [
            'status'    => 'publish',
            'limit' => -1,
        ];
        $all_products = wc_get_products($args);
        $this->products = $all_products;
    }

    private function get_leftover($sku) {
        $body = [
            'params' => [$sku],
            'request' => [
                "compuid" => MONEO_COMPANY_ID
            ],
        ];
        $response = wp_remote_post( self::BASE_URI . 'method/stock.getstockquant', [
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'sslverify' => FALSE,
            'headers'     => [
                'Authorization' => MONEO_KEY
            ],
            'body'        => wp_json_encode( $body ),
            'cookies'     => []
        ]);
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            error_log($error_message . "\n", 3, "/var/www/ttatem/data/www/procosmetics.lv/wp-content/plugins/moneo-api-sync/error.log");
            return null;
        } else {
            $result = json_decode($response['body']);
            return $result->result[0];       
        }
    }

    public function sync_prices() {
        error_log("\nAnother Price Sync with MONEO started at: ". time(), 3, "/var/www/ttatem/data/www/procosmetics.lv/wp-content/plugins/moneo-api-sync/error.log");
        $this->load_products();

        if (null == $this->products) {
            error_log("\nNo products were found". time(), 3, "/var/www/ttatem/data/www/procosmetics.lv/wp-content/plugins/moneo-api-sync/error.log");
            return;
        }
        foreach ($this->products as $product) {
            $sku = $product->get_sku();
            if ($sku) {
                $price = $this->get_price($sku);
                if (null == $price) {
                    error_log("\nNo price from moneo". time(), 3, "/var/www/ttatem/data/www/procosmetics.lv/wp-content/plugins/moneo-api-sync/error.log");
                    return;
                }
                if (!isset($price[1][0][0])) { // damn moneo data structure
                    continue;
                }
                $product->set_price($price[1][0][0]);
                $product->set_regular_price($price[1][0][0]);
                $product->save();
            }
        }
    }

    private function get_price($sku) {
        $body = [
            'filter' => ["code" => $sku],
            'fieldlist' => ["price"],
            'request' => [
                "compuid" => MONEO_COMPANY_ID
            ],
        ];
        $response = wp_remote_post( self::BASE_URI . 'items.items', [
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'sslverify' => FALSE,
            'headers'     => [
                'Authorization' => MONEO_KEY
            ],
            'body'        => wp_json_encode( $body ),
            'cookies'     => []
        ]);
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            //error_log($error_message . "\n", 3, "/var/www/ttatem/data/www/procosmetics.lv/wp-content/plugins/moneo-api-sync/error.log");
            return null;
        } else {
            $result = json_decode($response['body']);
            return $result->result[0];
        }
    }
}