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
    console.log("call addCardFieldsObserver");

    var creditCardNum = document.querySelector('#creditCardNumVisible');
    var creditCardMonth = document.querySelector('#creditCardExpirationMonth');
    var creditCardYear = document.querySelector('#creditCardExpirationYear');

    var rpay = new RPay();
    generateFingerprint(rpay);

    Element.observe(creditCardNum, 'change', function(e) {
        updateCreditCardToken(rpay, creditCardNum.value, creditCardMonth.value, creditCardYear.value);
    });
    Element.observe(creditCardMonth, 'change', function(e) {
        updateCreditCardToken(rpay, creditCardNum.value, creditCardMonth.value, creditCardYear.value);
    });
    Element.observe(creditCardYear, 'change', function(e) {
        updateCreditCardToken(rpay, creditCardNum.value, creditCardMonth.value, creditCardYear.value);
    });
}

function generateFingerprint(rpay) {
    console.log("call generateFingerprint");
    var fingerprintFields = document.querySelectorAll(".rakutenFingerprint");
    rpay.fingerprint(function(error, fingerprint) {
        if (error) {
            console.log("Erro ao gerar fingerprint", error);
            return;
        }
        console.log("complete generateFingerprint");
        for (var i = 0; i < fingerprintFields.length; i++) {
            fingerprintFields[i].value = fingerprint;
        }
    });
}

function updateCreditCardToken(rpay, creditCardNum, creditCardMonth, creditCardYear) {
    console.log("enter updateCreditCardToken");
    if (creditCardNum.length == 19 && creditCardMonth !== "" && creditCardYear !== "") {

        console.log("call updateCreditCardToken");

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

        //Generates the token
        var creditCardTokenField = document.getElementById("creditCardToken");
        var creditCardBrandField = document.getElementById('creditCardBrand');

        var elements = {
            "form": form,
            "card-number": document.querySelector("#creditCardNum"),
            "card-cvv": document.querySelector("#creditCardCode"),
            "expiration-month": document.querySelector('#creditCardExpirationMonth'),
            "expiration-year": document.querySelector('#creditCardExpirationYear')
        };

        rpay.tokenize(elements, function(error, data) {
            if (error) {
                console.log("Dados de cartão inválidos", error);
                return;
            }
            console.log("complete updateCreditCardToken");
            creditCardTokenField.value = data.cardToken;
            creditCardBrandField.value = rpay.cardBrand(elements["card-number"].value);
        });

        return true;
    }
}

/**
 * Observer for checkout price modifications, like changes in shipment price or taxes
 * to call the installments value with the updated value
 * @object OnestepcheckoutForm.hidePriceChangeProcess
 *
 */
OnestepcheckoutForm.prototype.hidePriceChangeProcess = OnestepcheckoutForm.prototype.hidePriceChangeProcess.wrap(function(hidePriceChangeProcess){

    var granTotalAmountUpdated = convertPriceStringToFloat(this.granTotalAmount.textContent);

    if (document.getElementById('grand_total') !== null && parseFloat(document.getElementById('grand_total').value) !== granTotalAmountUpdated) {
        document.getElementById('grand_total').value = granTotalAmountUpdated;
        if (document.getElementById('creditCardNumVisible') !== null && document.getElementById('creditCardNumVisible').value.length > 6) {
            getInstallments(document.getElementById('creditCardBrand').value);
        }
    }

    return hidePriceChangeProcess();
});

OnestepcheckoutForm.prototype.validate = OnestepcheckoutForm.prototype.validate.wrap(function (validate) {
    return validate() && validateRakutenPayActiveMethod();
});

addCardFieldsObserver();