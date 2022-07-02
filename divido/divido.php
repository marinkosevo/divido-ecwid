<?php

class Divido
{
    private $api_key;
    private $url;
  
    public function __construct($api_key, $url)
    {
        $this->api_key = $api_key;
        $this->url = $url;
    }

    public function get_plans()
    {
        $curl = curl_init();
    
        curl_setopt_array($curl, [
        CURLOPT_URL => $this->url."/finance-plans",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "X-DIVIDO-API-KEY: ".$this->api_key
        ],
        ]);
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
    
        curl_close($curl);
    
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
     
    public function calculate(
        $amount=0,
        $deposit=0,
        $months=0,
        $interest=0.0,
        $deposit_maximum=0,
        $deposit_minimum=0,
        $description='',
        $instalment_fee=0,
        $setup_fee=0,
        $country_code='',
        $id='',
        $lender_code=''
    ) {
        $data = array(
            "amount"=> floatval($amount),
            "deposit_amount"=>floatval($deposit),
            "agreement_start_date"=>'',
            "plan"=>array(
                "credit_amount"=>array(
                    "maximum_amount"=> null,
                    "minimum_amount"=> 0
                ),
                "deposit"=>array(
                    "maximum_percentage"=>intval($deposit_maximum),
                    "minimum_percentage"=>intval($deposit_minimum)
                ),
                "fees"=>array(
                    "instalment_fee_amount"=>intval($instalment_fee),
                    "setup_fee_amount"=>intval($setup_fee)
                ),
                "deferral_period_months"=>0,
                "description" => $description,
                "country_code" => $country_code,
                "id" => $id,
                "lender_code" => $lender_code,
                "agreement_duration_months"=>intval($months),
                "calculation_family"=>'',
                "interest_rate_percentage"=>floatval($interest),
            )
        );
        $curl = curl_init();
    
        curl_setopt_array($curl, [
        CURLOPT_URL => $this->url."/calculations",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS =>json_encode($data),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],
        ]);
    
        $response = curl_exec($curl);
        $err = curl_error($curl);
    
        curl_close($curl);
    
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
    
    public function get_lowest($amount=0)
    {
        $plans = json_decode($this->get_plans())->data;
        $monthly_amounts = array();
        foreach ($plans as $plan) {
            $calculation = $this->calculate(
                $amount,
                floatval($amount*$plan->deposit->maximum_percentage),
                $plan->agreement_duration_months,
                $plan->interest_rate_percentage,
                floatval($amount*$plan->deposit->maximum_percentage),
                floatval($amount*$plan->deposit->minimum_percentage),
                $plan->description,
                $plan->fees->instalment_fee_amount,
                $plan->fees->setup_fee_amount,
                $plan->country->id,
                $plan->lender->id
            );
            $monthly_amounts[] = json_decode($calculation)->amounts->monthly_payment_amount;
    
            return min($monthly_amounts);
        }
    }
    
    public function process_payment($finance_plan, $deposit, $first_name, $last_name, $email, $order_items, $urls, $metadata, $callback_url)
    {
        $data = array(
            "finance_plan_id"=> $finance_plan,
            "deposit_amount"=>$deposit,
            "applicants"=>array(
              array(
                "firstName"=>$first_name,
                "lastName"=>$last_name,
                "email"=>$email,
              )
            ),
            "order_items"=>$order_items,
            "metadata" => $metadata,
            "urls"=>$urls
            );
          
        $curl = curl_init();
    
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->url."/applications",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>json_encode($data),
            CURLOPT_HTTPHEADER => [
              "Content-Type: application/json",
              "X-DIVIDO-API-KEY: ".$this->api_key
            ],
          ]);
          
        $response = curl_exec($curl);
        $err = curl_error($curl);
          
        curl_close($curl);
          
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $curl = curl_init();
    
            curl_setopt_array($curl, [
                CURLOPT_URL => $callback_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET"
            ]);
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            
            curl_close($curl);
          
            echo $response;
        }
    }
}
