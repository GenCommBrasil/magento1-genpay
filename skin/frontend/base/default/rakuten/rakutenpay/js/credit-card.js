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
function validateCreditCard(self) {
  if (self.validity.valid && removeNumbers(unmask(self.value)) === "" && (self.value.length >= 14 && self.value.length <= 22)) {
    var rpay = new RPay();
    cardValidate = rpay.cardValidate(unmask(self.value));
    if (cardValidate.valid) {
      displayError(self, false);
      return true;
    } else {
      displayError(self);
      return false;
    }
  } else {
    displayError(self);
    return false;
  }
}

function validateCardHolder(self) {
  if (self.validity.tooShort || !self.validity.valid || removeLetters(unmask(self.value)) !== "") {
    displayError(self);
    return false;
  } else {
    displayError(self, false);
    return true;
  }
}

function validateCardDate() {
  monthField = document.getElementById('creditCardExpirationMonth');
  yearField = document.getElementById('creditCardExpirationYear');
  if (!monthField.validity.valid) {
    displayError(monthField);
    return false;
  } else {
    displayError(monthField, false);
  }
  if (!yearField.validity.valid) {
    displayError(yearField);
    return false;
  } else {
    displayError(yearField, false);
  }
  month = monthField.value;
  year = yearField.value;
  var rpay = new RPay();
  var valid = rpay.cardExpirationValidate(year, month);
  if (!valid) {
    displayError(monthField);
    displayError(yearField);
    return false;
  } else {
    displayError(monthField, false);
    displayError(yearField, false);
    return true;
  }
}

function validateCreditCardMonth(self) {
  if (self.validity.valid) {
    displayError(self, false)
    return true
  } else {
    displayError(self)
    return false
  }
}

function validateCreditCardYear(self) {
  if (self.validity.valid) {
    displayError(self, false)
    return true
  } else {
    displayError(self)
    return false
  }
}

function cardInstallmentOnChange(data) {
  data = JSON.parse(data);
  document.getElementById('creditCardInstallment').value = data.quantity;
  document.getElementById('creditCardInstallmentValue').value = data.amount;
  document.getElementById('card_total').innerHTML = 'R$ ' + data.totalAmount;
  document.getElementById('creditCardInterestAmount').value = data.interestAmount;
  document.getElementById('creditCardInterestPercent').value = data.interestPercent;
  document.getElementById('creditCardInstallmentTotalValue').value = data.totalAmount;
}

function validateCreditCardInstallment(self) {
  if (self.validity.valid && self.value != "null") {
    displayError(self, false)
    return true
  } else {
    displayError(self)
    return false
  }
}

function validateHiddenFields() {
    target = document.getElementsByClassName('creditCardHiddenFields-error-message')[0]

    if (document.getElementById('creditCardNum').value != "" &&
    document.getElementById('creditCardToken').value != "" &&
    document.getElementById('creditCardBrand').value != "" &&
    document.getElementById('creditCardInstallment').value != "" &&
    document.getElementById('creditCardInstallmentValue').value != "" &&
    document.getElementById('creditCardInterestPercent').value != "" &&
    document.getElementById('creditCardInterestAmount').value != "" &&
    document.getElementById('creditCardInstallmentTotalValue').value != "")
    {
        if(!target.classList.contains('display-none')) {
            target.classList.add('display-none')
        }

        return true;
    } else if (target.classList.contains('display-none')) {
          target.classList.remove('display-none')
    }

    return false;
}

function getBrand(self) {
  if (validateCreditCard(self)) {
    var rpay = new RPay();
    brand = rpay.cardBrand(unmask(document.getElementById('creditCardNumVisible').value));
    document.getElementById('creditCardBrand').value = brand;
  } else {
    displayError(document.getElementById('creditCardNumVisible'))
  }
}

function createCardToken(save) {
  //Clears the div and adds the required RakutenPay method to the form
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
      //Hides the fingerprint and token fields (don't know why it isn't by default)
      document.getElementsByName('rkp[fingerprint]')[0].type = "hidden";
      document.getElementsByName('rkp[fingerprint]')[0].id = "fingerprint";
      document.getElementsByName('rkp[fingerprint]')[0].name = "payment[fingerprint]";
      document.getElementsByName('rkp[token]')[0].type = "hidden";
      document.getElementById('creditCardToken').value = document.getElementsByName('rkp[token]')[0].value;
      save();
    },
    "result:error": function (errors) {
      console.log(errors);
    }
  };
  rpay.generate(form);
}

function validateCreditCardCode(self) {
  if (self.validity.tooLong || self.validity.tooShort || !self.validity.valid) {
    displayError(self);
    return false;
  } else {
    displayError(self, false);
    return true;
  }
}

function validateCreditCardForm(save) {
  if (
      validateCreditCard(document.querySelector('#creditCardNum')) &&
      validateCreditCardCode(document.querySelector('#creditCardCode')) &&
      validateCreditCardMonth(document.querySelector('#creditCardExpirationMonth')) &&
      validateCreditCardYear(document.querySelector('#creditCardExpirationYear')) &&
      validateCardHolder(document.querySelector('#creditCardHolder')) &&
      validateDocument(document.querySelector('#creditCardDocument')) &&
     validateCreditCardInstallment(document.querySelector('#card_installment_option'))
  ) {

    createCardToken(save);
    return true;
  }

  validateCreditCard(document.querySelector('#creditCardNum'))
  validateCreditCardCode(document.querySelector('#creditCardCode'))
  validateCreditCardMonth(document.querySelector('#creditCardExpirationMonth'))
  validateCreditCardYear(document.querySelector('#creditCardExpirationYear'))
  validateCardHolder(document.querySelector('#creditCardHolder'))
  validateDocument(document.querySelector('#creditCardDocument'))
  validateCreditCardInstallment(document.querySelector('#card_installment_option'))
  return false;
}

function validateCreditCardFormOneStepCheckout() {
    if (
        validateCreditCard(document.querySelector('#creditCardNumVisible')) &&
        validateCreditCardCode(document.querySelector('#creditCardCode')) &&
        validateCreditCardMonth(document.querySelector('#creditCardExpirationMonth')) &&
        validateCreditCardYear(document.querySelector('#creditCardExpirationYear')) &&
        validateCardHolder(document.querySelector('#creditCardHolder')) &&
        validateDocument(document.querySelector('#creditCardDocument')) &&
        validateCreditCardInstallment(document.querySelector('#card_installment_option')) &&
        validateHiddenFields()
    ) {
        return true;
    }
    return false;
}

function validateCreateToken() {
  if (validateCreditCard(document.querySelector('#creditCardNum'))
    && validateCreditCardMonth(document.querySelector('#creditCardExpirationMonth'))
    && validateCreditCardYear(document.querySelector('#creditCardExpirationYear'))
    && validateCreditCardCode(document.querySelector('#creditCardCode'))
    && document.getElementById('creditCardBrand').value !== ""
  ) {
    return true
  }

  validateCreditCard(document.querySelector('#creditCardNum'));
  validateCreditCardMonth(document.querySelector('#creditCardExpirationMonth'));
  validateCreditCardYear(document.querySelector('#creditCardExpirationYear'));
  validateCreditCardCode(document.querySelector('#creditCardCode'));

  return false;
}

/**
 * Return the value of 'el' without letters
 * @param {string} el
 * @returns {string}
 */
function removeLetters(el) {
  return el.replace(/[a-zA-Z]/g, '');

}

/**
 * Return the value of 'el' without numbers
 * @param {string} el
 * @returns {string}
 */
function removeNumbers(el) {
  return el.replace(/[0-9]/g, '');
}
