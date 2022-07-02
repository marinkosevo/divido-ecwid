<?php

include 'divido/divido.php';
$divido = new Divido('sandbox_50fae65', 'https://merchant.api.sandbox.divido.com');
if (isset($_GET['action'])) {
    $data = $_REQUEST;
    if (isset($data['action'])) {
        if ($data['action'] == 'get_plans') {
            echo $_GET['callback'] . '(' . json_encode($divido->get_plans()) . ');';
        }
        if ($data['action'] == 'calculate') {
            echo $_GET['callback'] . '(' . json_encode($divido->calculate(
                $data['amount'],
                $data['deposit'],
                $data['agreement_duration_months'],
                $data['interest_rate_percentage'],
                $data['deposit_maximum_percentage'],
                $data['deposit_minimum_percentage'],
                $data['description'],
                $data['fees_instalment_fee_amount'],
                $data['fees_setup_fee_amount'],
                $data['country_code'],
                $data['id'],
                $data['lender_code']
            )) . ');';
        }

        if ($data['action'] == 'get_lowest') {
            echo $_GET['callback'] . '(' . json_encode($divido->get_lowest($data['amount'])) . ');';
        }
    }
}
