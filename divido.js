let url = "https://shopify.omnicapital.co.uk/divido.php";
Ecwid.OnPageLoaded.add(function (page) {
  if (page.type == "PRODUCT")
    jQuery(".product-details__product-price").append(`
      <p id="divido_call" data-pop="divido_call"></p>
      <input type="hidden" value="&#163" id="divido_currency">
      <div id="divido_overlay" data-popup="divido_call" >
        <div id="divido_popup" >
            <div id="divido_widget">
            <div id="divido_loader" class='lds-dual-ring hidden'></div>
                <h4>Pay in Instalments</h4>
                <div id="divido_widget_content">
                <div id="divido_finance_options"></div>
                <div id="divido_select_deposit">
                    <p class="title">Select deposit: <output><span id="divido_slider_value">0</span></output></p>
                    <input type="range" min="0" max="100" step="1" class="divido_slider slider-progress" id="divido_slider" list="tickmarks">
                </div>
                <table id="divido_calculator_table">
                    <tr>
                    <td class="row_name">Instalment fee</td>
                    <td class="row_value" id="instalment_fee_amount"></td>
                    </tr>
                    <tr>
                    <td class="row_name">Rate of interest</td>
                    <td class="row_value" id="interest_rate_percentage"></td>
                    </tr>
                    <tr>
                    <td class="row_name">Effective APR</td>
                    <td class="row_value" id="effective_interest_rate_percentage"></td>
                    </tr>
                    <tr>
                    <td class="row_name">Number of payments</td>
                    <td class="row_value" id="instalments"></td>
                    </tr>
                    <tr>
                    <td class="row_name">Agreement duration</td>
                    <td class="row_value" id="agreement_duration_months"></td>
                    </tr>
                    <tr>
                    <td class="row_name">Amount of each repayment</td>
                    <td class="row_value" id="monthly_payment_amount"></td>
                    </tr>
                    <tr>
                    <td class="row_name">Cost of credit</td>
                    <td class="row_value" id="credit_cost_amount"></td>
                    </tr>
                    <tr>
                    <td class="row_name">Deposit amount</td>
                    <td class="row_value" id="deposit_amount"></td>
                    </tr>
                    <tr>
                    <td class="row_name">Total amount payable</td>
                    <td class="row_value" id="total_repayable_amount"></td>
                    </tr>
                    <tr>
                    <td class="row_name">Setup fee</td>
                    <td class="row_value" id="setup_fee"></td>
                    </tr>
                </table>
                </div>
                <button data-pop>OK</button>
            </div>
        </div>
      </div>`);
  let currency = jQuery("#divido_currency").val();
  let deposit_slide = jQuery("#divido_slider");
  let amount = jQuery(".product-details__product-price").attr("content");
  get_lowest();
  divido_get_data();

  function divido_get_data() {
    let data = { action: "get_plans" };
    jQuery.ajax({
      type: "GET",
      url: url,
      data: data,
      dataType: "jsonp",
      success: function (response) {
        let plans = JSON.parse(response).data;

        jQuery(plans).each(function (index) {
          if (index == 0) var selected = 'checked="checked"';
          jQuery("#divido_finance_options").append(
            `
                <div class="divido_finance_option_single">
                  <input type="radio" name="divido_finance_option" id="` +
              this.id +
              `"
                  data-agreement_duration_months="` +
              this.agreement_duration_months +
              `"
                  data-calculation_family="` +
              this.calculation_family +
              `"
                  data-interest_rate_percentage="` +
              this.interest_rate_percentage +
              `"
                  data-credit_amount_maximum_amount="` +
              this.credit_amount.maximum_amount +
              `"
                  data-credit_amount_minimum_amount="` +
              this.credit_amount.minimum_amount +
              `"
                  data-deferral_period_months="` +
              this.deferral_period_months +
              `"
                  data-deposit_maximum_percentage="` +
              this.deposit.maximum_percentage +
              `"
                  data-deposit_minimum_percentage="` +
              this.deposit.minimum_percentage +
              `"
                  data-description="` +
              this.description +
              `"
                  data-fees_instalment_fee_amount="` +
              this.fees.instalment_fee_amount +
              `"
                  data-fees_setup_fee_amount="` +
              this.fees.setup_fee_amount +
              `"
                  data-country_code="` +
              this.country.id +
              `"
                  data-id="` +
              this.id +
              `"
                  data-lender_code="` +
              this.lender_code +
              `" ` +
              selected +
              `
                  />
                  <label for="` +
              this.id +
              `">` +
              this.description +
              `</label>
                </div>
                `
          );
        });
      },
    });
  }

  function calculate() {
    let data = jQuery("input[name=divido_finance_option]:checked").data();
    data.action = "calculate";
    data.amount = amount;
    data.deposit = parseFloat(jQuery(deposit_slide).val()).toFixed(2);
    jQuery.ajax({
      type: "POST",
      url: url,
      data: data,
      dataType: "jsonp",
      success: function (response) {
        let data = JSON.parse(response);
        console.log(data);
        jQuery("#instalment_fee_amount").html(
          currency + data.fees.instalment_fee_amount.toFixed(2)
        );
        jQuery("#interest_rate_percentage").html(
          data.interest_rate_percentage + "%"
        );
        jQuery("#effective_interest_rate_percentage").html(
          data.effective_interest_rate_percentage + "%"
        );
        jQuery("#instalments").html(data.instalments.length);
        jQuery("#agreement_duration_months").html(
          data.agreement_duration_months + " Months"
        );
        jQuery("#monthly_payment_amount").html(
          currency + data.amounts.monthly_payment_amount.toFixed(2)
        );
        jQuery("#credit_cost_amount").html(
          currency + data.amounts.credit_cost_amount.toFixed(2)
        );
        jQuery("#deposit_amount").html(
          currency + data.amounts.deposit_amount.toFixed(2)
        );
        jQuery("#total_repayable_amount").html(
          currency + data.amounts.total_repayable_amount.toFixed(2)
        );
        jQuery("#setup_fee").html(
          currency + data.fees.setup_fee_amount.toFixed(2)
        );
      },
    });
  }

  function get_lowest() {
    let data = { action: "get_lowest" };
    data.amount = amount;

    jQuery.ajax({
      type: "POST",
      url: url,
      data: data,
      dataType: "jsonp",
      headers: { "Access-Control-Allow-Origin": "*" }, // <-------- set this
      success: function (response) {
        jQuery("#divido_call").html(
          "Finance available from: \u00A3" + response + " per month"
        );
      },
    });
  }

  jQuery("[data-pop]").click(function () {
    var n = jQuery(this).data("pop");
    var jQuerypopup = n
      ? jQuery("[data-popup='" + n + "']")
      : jQuery(this).closest("[data-popup]");
    jQuerypopup.slideToggle(240);
    set_deposit();
    calculate();
  });

  function set_deposit() {
    var selected_plan = jQuery(
      "input[name=divido_finance_option]:checked"
    ).data();
    var min = selected_plan.deposit_minimum_percentage;
    var max = selected_plan.deposit_maximum_percentage;
    deposit_slide.attr("min", Math.ceil(min * amount));
    deposit_slide.attr("max", Math.ceil(max * amount));
    for (let e of document.querySelectorAll(
      'input[type="range"].slider-progress'
    )) {
      e.style.setProperty("--value", e.value);
      e.style.setProperty("--min", e.min == "" ? "0" : e.min);
      e.style.setProperty("--max", e.max == "" ? "1000" : e.max);
    }
  }

  jQuery(document).on("change", "#divido_slider", function () {
    set_deposit();
    calculate();
  });
  jQuery(document).on("input", "#divido_slider", function () {
    var percentage = 0;
    percentage = (jQuery(deposit_slide).val() / amount) * 100;
    jQuery(this).css("--value", jQuery(this).val());
    jQuery("#divido_slider_value").html(
      currency +
        parseFloat(jQuery(deposit_slide).val()).toFixed(2) +
        " (" +
        percentage.toFixed(0) +
        "%)"
    );
  });

  jQuery(document).on(
    "click",
    "input[name=divido_finance_option]",
    function () {
      var percentage = 0;
      jQuery(deposit_slide).val(0);
      percentage = (jQuery(deposit_slide).val() / amount) * 100;
      jQuery("#divido_slider_value").html(
        currency +
          parseFloat(jQuery(deposit_slide).val()).toFixed(2) +
          " (" +
          percentage.toFixed(0) +
          "%)"
      );
      set_deposit();
      calculate();
    }
  );

  console.log("Current page is of type: " + page.type);
});
//Slider progress background
