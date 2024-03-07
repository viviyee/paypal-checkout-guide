<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class PaypalController extends Controller
{
    protected $base_url;

    public function __construct()
    {
        $this->base_url = strtoupper(env('PAYPAL_ENV')) === 'LIVE' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';
    }

    public function handle_exception($e)
    {
        $e = $e->getResponse()->getBody();
        $e = (array)json_decode($e);
        return $e;
    }

    public function generate_access_token(Client $client)
    {
        $url = $this->base_url . '/v1/oauth2/token';
        // dd(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'), $url);
        try {
            $response = $client->post($url, [
                'auth' => [
                    env('PAYPAL_CLIENT_ID'),
                    env('PAYPAL_SECRET')
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials'
                ]
            ]);
            $response = $response->getBody();
            $access_token = json_decode($response)->access_token;
            return $access_token;
        } catch (ClientException $e) {
            return $this->handle_exception($e);
        }
    }

    public function get_purchase_details($value, $currency, $name, $details)
    {
        return [
            'intent' => "CAPTURE",
            'purchase_units' => [
                [
                    'items' => [
                        [
                            'name' => $name,
                            'description' => $details,
                            'quantity' => '1',
                            'unit_amount' => [
                                'currency_code' => strtoupper($currency),
                                'value' => $value,
                            ]
                        ]
                    ],
                    'amount' => [
                        'currency_code' => strtoupper($currency),
                        'value' => $value,
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => strtoupper($currency),
                                'value' => $value
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    public function create_order($id, Client $client)
    {
        // Modify codes here. This is an example.
        $product = Product::find($id);
        $purchase_details = $this->get_purchase_details($product->value, $product->currency, $product->name, $product->details);
        // end

        $url = $this->base_url . '/v2/checkout/orders';
        $access_token = $this->generate_access_token($client);

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token
                ],
                'json' => $purchase_details
            ]);
            $response = $response->getBody();
            $response = (array)json_decode($response);
            return $response;
        } catch (ClientException $e) {
            return $this->handle_exception($e);
        }
    }

    public function capture_order($order_id, Client $client)
    {
        $url = $this->base_url . '/v2/checkout/orders/' . $order_id . '/capture';
        $access_token = $this->generate_access_token($client);

        try {
            $response = $client->post($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token
                ],
            ]);
            $response = $response->getBody();
            $response = (array)json_decode($response);
            return $response;
        } catch (ClientException $e) {
            return $this->handle_exception($e);
        } catch (ServerException $server_e) {
            return $this->handle_exception($server_e);
        }
    }

    public function show_order($order_id, Client $client)
    {
        $url = $this->base_url . '/v2/checkout/orders/' . $order_id;
        $access_token = $this->generate_access_token($client);

        try {
            $response = $client->get($url, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token
                ],
            ]);
            $response = $response->getBody();
            $response = (array)json_decode($response);
            return $response;
        } catch (ClientException $e) {
            return $this->handle_exception($e);
        }
    }
}
