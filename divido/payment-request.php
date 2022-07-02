<?php
//Development mode
error_reporting(E_ALL); ini_set('display_errors', '1');

include 'divido.php';
$client_secret = "Q55qKyHZxc3OwNsKLv9hcb5cOtfh9SaB"; // This is a dummy value. Place your client_secret key here. You received it from Ecwid team in email when registering the app
//$cipher = "AES-128-CBC";
$iv = "abcdefghijklmnop";// this can be generated random if you plan to store it for later but in this case e.g. openssl_random_pseudo_bytes($ivlen);
$cipher = "aes-128-gcm";
$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
$tag = 0;

$divido = new Divido('sandbox_50fae6', 'https://merchant.api.sandbox.divido.com');
if (isset($_POST["data"])) {

  // Functions to decrypt the payment request from Ecwid
  
    function getEcwidPayload($app_secret_key, $data)
    {
        // Get the encryption key (16 first bytes of the app's client_secret key)
        $encryption_key = substr($app_secret_key, 0, 16);
  
        // Decrypt payload
        $json_data = aes_128_decrypt($encryption_key, $data);
  
        // Decode json
        $json_decoded = json_decode($json_data, true);
        return $json_decoded;
    }
  
    function aes_128_decrypt($key, $data)
    {
        // Ecwid sends data in url-safe base64. Convert the raw data to the original base64 first
        $base64_original = str_replace(array('-', '_'), array('+', '/'), $data);
  
        // Get binary data
        $decoded = base64_decode($base64_original);
  
        // Initialization vector is the first 16 bytes of the received data
        $iv = substr($decoded, 0, 16);
  
        // The payload itself is is the rest of the received data
        $payload = substr($decoded, 16);
  
        // Decrypt raw binary payload
        $json = openssl_decrypt($payload, "aes-128-cbc", $key, OPENSSL_RAW_DATA, $iv);
        //$json = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $payload, MCRYPT_MODE_CBC, $iv); // You can use this instead of openssl_decrupt, if mcrypt is enabled in your system
  
        return $json;
    }
  
    // Get payload from the POST and decrypt it
    $ecwid_payload = $_POST['data'];
    // The resulting JSON from payment request will be in $order variable
    $order = getEcwidPayload($client_secret, $ecwid_payload);

    // Encode access token and prepare calltack URL template
    $ciphertext_raw = openssl_encrypt($order['token'], $cipher, $client_secret, $options=0, $iv, $tag);
    $callbackPayload = base64_encode($ciphertext_raw);
    $callbackUrl = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"."?storeId=".$order['storeId']."&orderNumber=".$order['cart']['order']['orderNumber']."&callbackPayload=".$callbackPayload;

    //Set first payment status for order
    $c = base64_decode($callbackPayload);
    update_order_status($order['cart']['order']['orderNumber'], 'AWAITING_PAYMENT', $order['storeId'], openssl_decrypt($c, $cipher, $client_secret, $options=0, $iv, $tag));

    // The resulting JSON from payment request will be in $order variable
    $order = getEcwidPayload($client_secret, $ecwid_payload);
  
    // Print form on a page to submit it from a button press
    include 'form.php';
}
  
  // If we are returning back to storefront. Callback from payment
  
  elseif (isset($_GET["callbackPayload"]) && isset($_GET["status"])) {
  
      // Set variables
      $client_id = "custom-app-3";
      $c = base64_decode($_GET['callbackPayload']);
      $token = openssl_decrypt($c, $cipher, $client_secret, $options=0, $iv, $tag);
      $storeId = $_GET['storeId'];
      $orderNumber = $_GET['orderNumber'];
      $status = $_GET['status'];
      $returnUrl = "https://app.ecwid.com/custompaymentapps/$storeId?orderId=$orderNumber&clientId=$client_id";
    
      update_order_status($orderNumber, $status, $storeId, $token);
  
      // return customer back to storefront
      echo "<script>window.location = '$returnUrl'</script>";
  } else {
      header('HTTP/1.0 403 Forbidden');
      echo 'Access forbidden!';
  }
  
  function update_order_status($orderNumber, $status, $storeId, $token)
  {
      // Prepare request body for updating the order
      $json = json_encode(array(
        "paymentStatus" => $status,
        "externalTransactionId" => "transaction_".$orderNumber
    ));

      // URL used to update the order via Ecwid REST API
      $url = "https://app.ecwid.com/api/v3/$storeId/orders/transaction_$orderNumber?token=$token";

      // Send request to update order
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($json)));
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($ch);
      curl_close($ch);
  }
