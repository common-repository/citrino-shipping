<?php

    class Citrino_Shipping_For_WooCommerce_Canada_Post 
    {
        private $rates = array();

        private function get_normalized_weight($weight) 
        {
            $woo_weight_unit = strtolower(get_option('woocommerce_weight_unit'));
    
            if ($woo_weight_unit != 'kg') {
                switch ($woo_weight_unit) {
                    case 'g':
                        $weight *= 0.001;
                        break;
                    case 'lbs':
                        $weight *= 0.4353;
                        break;
                    case 'oz':
                        $weight *= 0.0283495;
                }
            }
    
            return $weight;
        }

        private function get_citrino_api_request($weight, $destination)
        {
            $general_options = get_option('woocommerce_citrino_shipping_by_citrino_settings');
            $origin_postal_code = strtoupper(str_replace(' ', '', $general_options['citrino_origin_postal_code']));
            $key = base64_encode($general_options['citrino_api_username'] . ':' . $general_options['citrino_api_password']);

            $destination_postal_code = '';
            if ($destination['country'] == 'CA') {
                $destination_postal_code = strtoupper(str_replace(' ', '', $destination['postcode']));
            }
            else if ($destination['country'] == 'US') {
                $destination_postal_code = str_replace(' ', '', $destination['postcode']);
            }
            else
            {
                $destination_postal_code = strtoupper($destination['country']);
            }

            $request_data = array(
                'method' => 'POST',
                'timeout' => '250',
                'httpversion' => '1.0',
                'sslverify' => false,
                'headers' => array(
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . $key,
                    'Content-type' => 'application/json'
                ),
                'body' => array(
                    'weight' => $weight,
                    'origin_postal_code' => $origin_postal_code,
                    'destination_postal_code' => $destination_postal_code,
                    'country_destination' => $destination['country'],
                    'state_destination' => $destination['state'],
                    'city_destination' => $destination['city']
                )
            );

            $results = wp_remote_post('http://maplaza.citrinocourier.com/sys/api/wp/quote.php', $request_data);
            
            $there__error = false;
            if(!is_wp_error($results)) {
                if($results['body'])
                {
                    $getJson = json_decode($results['body']);
                    if($getJson->status=='SUCCESS')
                    {
                        for ($i = 0; $i < count($getJson->data); $i++) {
                            $rate = array(
                                'id' => $getJson->data[$i]->{'service-code'},
                                'label' => $getJson->data[$i]->{'service-name'},
                                'cost' => $getJson->data[$i]->{'price'},
                                'calc_tax' => 'per_order'
                            );
                           array_push($this->rates, $rate);
                        }
                    }else{
                        return;
                    }
                }else{
                    return;
                }
            }else{
                return;
            }
        }

        public function calculate_shipping($package, $destination)
        {
            $weight_total = 0.00;
            foreach($package as $item) 
            {
                $non_normalized_weight = $item['data']->get_weight();
                $weight_total += $item['quantity'] * $this->get_normalized_weight($non_normalized_weight);
            }

            $this->get_citrino_api_request($weight_total, $destination);

            return $this->rates;
        }
    }
?>
	