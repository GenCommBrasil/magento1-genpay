/**
 ************************************************************************
 * Copyright [2018] [RakutenConnector]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 ************************************************************************
 */

/**
 * Validate the active payment method before magento save payment
 * @returns {Boolean}
 */
function validateRakutenPayActiveMethod() {
    //OSCPayment.currentMethod
    if(typeof(document.querySelector('#checkout-payment-method-load .radio:checked').value) !== "undefined") {
        switch (document.querySelector('#checkout-payment-method-load .radio:checked').value) {
            case "rakutenpay_credit_card":
                return validateCreditCardFormOneStepCheckout();
                break;
            case "rakutenpay_boleto":
                return validateBoletoFormOneStepCheckout();
                break;
            default:
                return true;
                break;
        }
    }
    return false;
}

/**
 * Converts an brazilian real price string, like R$9,99, or 9,99, to float (9.99)
 * @param {string} priceString
 * @returns {float}
 */
function convertPriceStringToFloat(priceString){
    if(priceString === ""){
        priceString =  0;
    }else{
        priceString = priceString.replace("R$","");
        priceString = priceString.replace(".","");
        priceString = priceString.replace(",",".");
        priceString = parseFloat(priceString);
    }
    return priceString;
}

function addCardFieldsObserver() {

    try {
        var paymentMethod = document.querySelector('#checkout-payment-method-load .radio:checked').value;

        if (paymentMethod === "rakutenpay_credit_card") {
            var creditCardNum = document.querySelector('#creditCardNumVisible');
            var creditCardMonth = document.querySelector('#creditCardExpirationMonth');
            var creditCardYear = document.querySelector('#creditCardExpirationYear');

            Element.observe(creditCardNum,'keyup',function(e){updateCreditCardToken(creditCardNum.value, creditCardMonth.value, creditCardYear.value);});
            Element.observe(creditCardMonth,'change',function(e){updateCreditCardToken(creditCardNum.value, creditCardMonth.value, creditCardYear.value);});
            Element.observe(creditCardYear,'change',function(e){updateCreditCardToken(creditCardNum.value, creditCardMonth.value, creditCardYear.value);});
        } else if (paymentMethod === "rakutenpay_boleto") {
            var countFingerprint = document.getElementsByName("payment[fingerprint]").length;

            if (countFingerprint === 0) {
                updateBilletFingerprint();
            }
        }
    } catch(e) {
        // console.error('Não foi possível adicionar observação aos cartões. ' + e.message);
    }
}

function updateCreditCardToken(creditCardNum, creditCardMonth, creditCardYear) {

    if (creditCardNum.length > 6 && creditCardMonth !== "" && creditCardYear !== "") {

        var container = document.getElementById("rakutenpay-cc-method-div");
        while (container.hasChildNodes()) {
            container.removeChild(container.lastChild);
        }
        var rpay_method = document.createElement("input");
        rpay_method.type = "hidden";
        rpay_method.setAttribute("data-rkp", "method");
        rpay_method.value = "credit_card";
        container.appendChild(rpay_method);

        //Gets the form element
        var form = rpay_method.form;

        //Generates the fingerprint and token
        var rpay = new RPay();
        rpay.listeners = {
            "result:success": function () {
                document.getElementsByName('rkp[fingerprint]')[0].type = "hidden";
                document.getElementsByName('rkp[fingerprint]')[0].id = "fingerprint";
                document.getElementsByName('rkp[fingerprint]')[0].name = "payment[fingerprint]";
                document.getElementsByName('rkp[token]')[0].type = "hidden";
                document.getElementById('creditCardToken').value = document.getElementsByName('rkp[token]')[0].value;
            },
            "result:error": function (errors) {
                console.log(errors);
            }
        };
        rpay.generate(form);

        return true;
    }
}

function updateBilletFingerprint() {
    //Clears the div and adds the required RakutenPay method to the form
    var container = document.getElementById("rakutenpay-billet-method-div");
    while (container.hasChildNodes()) {
        container.removeChild(container.lastChild);
    }
    var rpay_method = document.createElement("input");
    rpay_method.type = "hidden";
    rpay_method.setAttribute("data-rkp", "method");
    rpay_method.value = "billet";
    container.appendChild(rpay_method);

    //Gets the form element
    var form = rpay_method.form;

    //Generates the fingerprint and token
    var rpay = new RPay();
    rpay.listeners = {
        "result:success": function(){
            //Hides the fingerprint and token fields (don't know why it isn't by default)
            document.getElementsByName('rkp[fingerprint]')[0].type = "hidden";
            document.getElementsByName('rkp[fingerprint]')[0].id = "payment[fingerprint]";
            document.getElementsByName('rkp[fingerprint]')[0].name = "payment[fingerprint]";
        },
        "result:error":   function(errors){
            console.log(errors);
        }
    };
    rpay.generate(form);

    return true;
}

function forceTokenFingerprint() {

    if (document.querySelector('#checkout-payment-method-load .radio:checked') != null) {
        var paymentMethod = document.querySelector('#checkout-payment-method-load .radio:checked').value;

        if (paymentMethod === "rakutenpay_credit_card") {
            var creditCardNum = document.querySelector('#creditCardNumVisible');
            var creditCardMonth = document.querySelector('#creditCardExpirationMonth');
            var creditCardYear = document.querySelector('#creditCardExpirationYear');

            updateCreditCardToken(creditCardNum.value, creditCardMonth.value, creditCardYear.value);
        } else if (paymentMethod === "rakutenpay_boleto") {
            var countFingerprint = document.getElementsByName("payment[fingerprint]").length;

            if (countFingerprint === 0) {
                updateBilletFingerprint();
            }
        }
    }
}

/**
 * Observer for checkout price modifications, like changes in shipment price or taxes
 * to call the installments value with the updated value
 * @object OnestepcheckoutForm.hidePriceChangeProcess
 *
 */
OnestepcheckoutForm.prototype.hidePriceChangeProcess = OnestepcheckoutForm.prototype.hidePriceChangeProcess.wrap(function(hidePriceChangeProcess){

    forceTokenFingerprint();
    var granTotalAmountUpdated = convertPriceStringToFloat(this.granTotalAmount.textContent);

    if (document.getElementById('grand_total') !== null && parseFloat(document.getElementById('grand_total').value) !== granTotalAmountUpdated) {
        document.getElementById('grand_total').value = granTotalAmountUpdated;
        if (document.getElementById('creditCardNumVisible') !== null && document.getElementById('creditCardNumVisible').value.length > 6) {
            getInstallments(document.getElementById('creditCardBrand').value);
        }
    }

    return hidePriceChangeProcess();
});

/**
 * Call events before magento OneSstepChekouPayment switchToMethod event - only call
 * when the type of payment selected is relative to RakutenPay methods, preventing to
 * save all the time a RakutenPay Method is selected
 * @type OnestepcheckoutShipment
 */
OnestepcheckoutShipment.prototype.switchToMethod = OnestepcheckoutShipment.prototype.switchToMethod.wrap(
    function (switchToMethod, methodCode, forced) {

        // normal flow
        return switchToMethod(methodCode, forced);
    });

OnestepcheckoutForm.prototype.validate = OnestepcheckoutForm.prototype.validate.wrap(function (validate) {
    forceTokenFingerprint();

    return validate() && validateRakutenPayActiveMethod();
});

addCardFieldsObserver();