<?php
$curl = curl_init();
$data = array(
  "finance_plan_id"=> "16911111-6a3f-4129-987a-076b077338ea",
  "deposit_percentage"=>5,
  "applicants"=>array(
    array(
      "email"=>"marinkosevo94@gmail.com",
    )
  ),
  "order_items"=>array(
    array(
      "name"=>"NAME",
      "quantity"=>1,
      "price"=>100000
    )
  ),
  "urls"=>array(
    "merchant_redirect_url"=>"https://google.com",
    "merchant_checkout_url"=>"https://google.com",
    "merchant_response_url"=>"https://google.com"
  )
  );
curl_setopt_array($curl, [
  CURLOPT_URL => "https://merchant.api.sandbox.divido.com/applications",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS =>json_encode($data),
  CURLOPT_HTTPHEADER => [
    "Content-Type: application/json",
    "X-DIVIDO-API-KEY: sandbox_50fae652.596d7d7d2ede1355baae5126894a32dc"
  ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}