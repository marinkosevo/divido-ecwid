<!DOCTYPE HTML>
<html>
<head>
<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!------ Include the above in your HEAD tag ---------->

<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="form.css">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-8 pb-5">
 <?php
$amount = $order["cart"]["order"]["total"];
$plans = json_decode($divido->get_plans())->data;
//Define empty string
$options = '<div class="form-group">';
foreach ($plans as $plan) {
    $options .= '<div class="form-check">
    <input class="form-check-input" type="radio" name="payment[financeOption]" id="'.$plan->id.'" value="'.$plan->id.'">
    <label class="form-check-label" for="'.$plan->id.'">
    '.$plan->description.' (Deposit amount £ '.number_format($amount * $plan->deposit->minimum_percentage, 2).' - '.number_format($amount * $plan->deposit->maximum_percentage, 2).')
    </label>
    </div>';
}
$options .= '</div>';

$order_items = array();
foreach ($order["cart"]["order"]["items"] as $item) {
    $order_items[] = array(
        "name" => $item["name"],
        "quantity" => $item["quantity"],
        "price" => intval($item["price"]*100)
    );
}
?>
            <!--Form with header-->

                    <form method="post">
                        <input type="hidden" name="payment[returnUrl]" value="<?= $order["returnUrl"]; ?>"/>
                        <input type="hidden" name="payment[orderNo]" value="<?= $order["cart"]["order"]["orderNumber"]; ?>"/>
                        <input type="hidden" name="payment[orderId]" value="<?= $order["cart"]["order"]["id"]; ?>"/>
                        <input type="hidden" name="payment[redirectUrl]" value="<?= $callbackUrl."&status=PAID" ?>"/>
                        <input type="hidden" name="payment[checkoutUrl]" value="<?= $callbackUrl."&status=CANCELLED"?>"/>
                        <input type="hidden" name="payment[order_items]" value='<?= json_encode($order_items);?>'/>
                        <input type="hidden" name="payment[total]" value="<?= $order["cart"]["order"]["total"] * 100;?>"/>
                        <div class="card border-primary rounded-0">
                            <div class="card-header p-0">
                                <div class="bg-info text-white text-center py-2">
                                    <h3><i class="fa fa-envelope"></i> Divido</h3>
                                    <p class="m-0">Apply for finance</p>
                                </div>
                            </div>
                            <div class="card-body p-3">

                                <!--Body-->
                                <?= $options; ?>
                                <div class="form-group">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><p>First Name</p></div>
                                        </div>
                                        <input type="text" class="form-control" id="number" name="payment[firstName]" placeholder="First Name" required value="">
                                    </div>
                                </div>
                            
                                 <div class="form-group">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><p>Last Name</p></div>
                                        </div>
                                        <input type="text" class="form-control" id="number" name="payment[lastName]" placeholder="Last Name" required>
                                    </div>
                                </div>

                                 <div class="form-group">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><p>Email</p></div>
                                        </div>
                                        <input type="email" class="form-control" id="number" name="payment[email]" placeholder="Email" required value="<?= $order['cart']['order']['email']; ?>">
                                    </div>
                                </div>

                                 <div class="form-group">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><p>Deposit amount</p></div>
                                        </div>
                                        <input type="number" class="form-control" id="number" name="payment[deposit]" placeholder="Deposit amount" required>
                                    </div>
                                </div>
                                
                                <div class="text-center">
                                    <input type="submit" value="Submit" class="btn btn-info btn-block rounded-0 py-2">
                                </div>
                            </div>

                        </div>
                    </form>
                    <!--Form with header-->

          </div>
    </div>
</div>
</body>