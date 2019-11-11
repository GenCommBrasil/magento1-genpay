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
 //call events before magento payment.save() event
Payment.prototype.save = Payment.prototype.save.wrap(function(save) {
  if (payment.currentMethod === "rakutenpay_credit_card" || payment.currentMethod === "rakutenpay_boleto") {
    var validator = new Validation(this.form);
    if (this.validate() && validator.validate()) {
      // Do form validations
      // if (validateRakutenPayActiveMethod()) {
      //   save();
      // }
      validateRakutenPayActiveMethod(save);
    }
  } else {
    save();

    return true;
  }
});

/**
 * Validate the active payment method before magento save payment
 * @returns {Boolean}
 */
function validateRakutenPayActiveMethod(save) {
  switch (payment.currentMethod) {
    case "rakutenpay_credit_card":
      return validateCreditCardForm(save);
      break;
    case "rakutenpay_boleto":
      return validateBoletoForm(save);
      break;
    default:
      return true;
      break;
  }
}
