    <?php

require __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Client;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


//US (Sandbox Details)
//client - AXIAU0bbukqWHCVwzHjmM0fe_YRPqg3C6MUSvLS85zG4QImSAG_B4okX-l54H8hErIH9GV9TuLVqoX4u
//Secret - EBa6TIoif8jPJCwTahjCPGk5AaRGr0BWBq41qAnLFjW4ImgJPSriQ-WV0HGKV1vOcx2Q5G0IIZacsfyR



$PAYPAL_CLIENT_ID = 'AXIAU0bbukqWHCVwzHjmM0fe_YRPqg3C6MUSvLS85zG4QImSAG_B4okX-l54H8hErIH9GV9TuLVqoX4u';
$PAYPAL_CLIENT_SECRET = 'EBa6TIoif8jPJCwTahjCPGk5AaRGr0BWBq41qAnLFjW4ImgJPSriQ-WV0HGKV1vOcx2Q5G0IIZacsfyR';

// $PORT = 8888;
$base = "https://api-m.sandbox.paypal.com";

$app = AppFactory::create();

$app->add(new \Slim\Middleware\BodyParsingMiddleware());

/**
 * Generate an OAuth 2.0 access token for authenticating with PayPal REST APIs.
 * @see https://developer.paypal.com/api/rest/authentication/
 */
function generateAccessToken() {
    global $PAYPAL_CLIENT_ID, $PAYPAL_CLIENT_SECRET, $base;

    if (!$PAYPAL_CLIENT_ID || !$PAYPAL_CLIENT_SECRET) {
        throw new Exception("MISSING_API_CREDENTIALS");
    }

    $auth = base64_encode($PAYPAL_CLIENT_ID . ":" . $PAYPAL_CLIENT_SECRET);
    $client = new Client();
    $response = $client->post("$base/v1/oauth2/token", [
        'form_params' => [
            'grant_type' => 'client_credentials'
        ],
        'headers' => [
            'Authorization' => "Basic $auth"
        ]
    ]);

    $data = json_decode($response->getBody(), true);
    return $data['access_token'];
}

/**
 * Create an order to start the transaction.
 * @see https://developer.paypal.com/docs/api/orders/v2/#orders_create
 */
function createOrder($cart) {
    global $base;

    $accessToken = generateAccessToken();
    $client = new Client();
    $payload = [
        'intent' => 'CAPTURE',
        'purchase_units' => [
            [
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => '100.00'
                ]
            ]
        ],
        

    ];

    $response = $client->post("$base/v2/checkout/orders", [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $accessToken"
        ],
        'json' => $payload
    ]);

    return handleResponse($response);
}

/**
 * Capture payment for the created order to complete the transaction.
 * @see https://developer.paypal.com/docs/api/orders/v2/#orders_capture
 */
function captureOrder($orderID) {
    global $base;

    $accessToken = generateAccessToken();
    $client = new Client();
    $response = $client->post("$base/v2/checkout/orders/$orderID/capture", [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer $accessToken"
        ]
    ]);

    return handleResponse($response);
}

function handleResponse($response) {
    $jsonResponse = json_decode($response->getBody(), true);
    return [
        'jsonResponse' => $jsonResponse,
        'httpStatusCode' => $response->getStatusCode()
    ];
}

$app->get('/', function ($request, $response, $args) {
    global $PAYPAL_CLIENT_ID;

    try {
        $html=file_get_contents('checkout.html');
        $html=str_replace("clientId", $PAYPAL_CLIENT_ID, $html);

        $response->withHeader('Content-Type', 'text/html');
        $response->getBody()->write($html);
        return $response;
    } catch (Exception $e) {
        return $response->withStatus(500)->write($e->getMessage());
    }
});

$app->post('/api/orders', function ($request, $response, $args) {
    $cart = $request->getParsedBody()['cart'];

    try {
        $orderResponse = createOrder($cart);
        return $response->withStatus($orderResponse['httpStatusCode'])->withJson($orderResponse['jsonResponse']);
    } catch (Exception $e) {
        return $response->withStatus(500)->withJson(['error' => 'Failed to create order.']);
    }
});

$app->post('/api/orders/{orderID}/capture', function ($request, $response, $args) {
    $orderID = $args['orderID'];

    try {
        $captureResponse = captureOrder($orderID);
        return $response->withStatus($captureResponse['httpStatusCode'])->withJson($captureResponse['jsonResponse']);
    } catch (Exception $e) {
        return $response->withStatus(500)->withJson(['error' => 'Failed to capture order.']);
    }
});

$app->run();
?>
