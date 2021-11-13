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

    public function sync() {
        $this->load_products();
                
        $sku_list = [];

        if (null == $this->products) {
            //update_post_meta(4, 'damn_error', 'happened'); // todo error_log
            return;
        }
        
        foreach ($this->products as $product) {
            $sku_list[] = $product->get_sku();
        }

        $leftovers = $this->get_leftovers($sku_list);

        if (null == $leftovers) {
            return;
        }

        foreach ($this->products as $key => $product) {
            if ( (int) $leftovers[$key] == 0 ) {
                $product->set_stock_status(self::OUT_OF_STOCK);
                $product->set_stock_quantity($leftovers[$key]);
            }
            if ( (int) $leftovers[$key] > 0 ) {
                $product->set_stock_status(self::IN_STOCK);
                $product->set_stock_quantity($leftovers[$key]);
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

    private function get_leftovers($sku_list) {
        $body = [
            'params' => [$sku_list],
            'request' => [
                "compuid" => MONEO_COMPANY_ID
            ],
        ];
        $response = wp_remote_post( self::BASE_URI . 'method/stock.getstockquantforitemlist', [
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => [
                'Authorization' => MONEO_KEY
            ],
            'body'        => wp_json_encode( $body ),
            'cookies'     => []
        ]);
         
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            update_post_meta(4, 'damn_error', $error_message); // error log here
            return null;
        } else {
            $result = $response['body'];
            $result = json_decode($response['body']);
            update_post_meta(4, 'result', $result->result[0]);
            return $result->result[0];       
        }
    }
}