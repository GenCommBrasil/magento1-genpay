<?php
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

class Rakuten_RakutenPay_Model_Adminhtml_Config
{
    private $css;
    private $jquery;
    private $js;
    private $jsColorbox;
    private $logo;
    private $skin;
    private $version;

    public function __construct()
    {
        $this->library = new Rakuten_RakutenPay_Model_Library();
        //Set skin path
        $this->skin = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
        //Set Skin URL/
        $skinUrl = $this->skin.'adminhtml/default/default/rakuten/rakutenpay/';
        $configCss = $skinUrl.'css/rakutenpay-module-config.css';
        //Set headers
        $this->css = '<link rel="stylesheet" type="text/css" href="'.$configCss.'" />';
        $this->jquery = $skinUrl.'js/jquery-1.11.1.js';
        $this->js = $skinUrl.'js/rakutenpay-module.js';
        $this->jsColorbox = $skinUrl.'js/jquery.colorbox-min.js';
        //Set images
        $this->logo = $skinUrl.'images/logo.png';
        //Set version
        $this->version = Mage::helper('rakutenpay')->getVersion();
    }

    /**
     * Generates the layout of content of settings screen
     *
     * @return string $comment - Contains the comment field in layout format
     */
    public function getCommentText()
    {
        /** @var Rakuten_RakutenPay_Helper_Credential $credential */
        $credential = Mage::helper('rakutenpay/credential');
        $helper = Mage::helper('rakutenpay');
        $redirect = Mage::getBaseUrl().'checkout/onepage/success/';
        $backgroundCss = '#fff';
        $html = Mage::helper('rakutenpay/html');
        $alertEnvironment = $helper->__('Suas transações serão feitas em um ambiente de testes.').'<br />';
        $alertEnvironment .= $helper->__('Nenhuma das transações realizadas nesse ambiente tem valor monetário.');
        $alertEmailToken = $helper->__('Certifique-se de que o e-mail e os dados de vendedor foram preenchidos.');
        $interface = '<div class="rakutenpay-comment">
                        '.$this->css.'
                        '.$html->getHeader($this->logo).'
                     </div>';
        $init = Mage::getStoreConfig('payment/rakutenpay/init');
        $cnpj = $credential->getDocument();
        $api_key = $credential->getApiKey();
        $signature_key = $credential->getSignatureKey();
        $email = $credential->getEmail();
        $comment = '<script src="'.$this->jquery.'"></script>';
        $comment .= '<script src="'.$this->js.'"></script>';
        $comment .= '<script src="'.$this->jsColorbox.'"></script>';
        $comment .= '<script type="text/javascript">
                        var jQuery = jQuery.noConflict();
                        jQuery(document).ready(function(){
                            var content = jQuery(".rakutenpay-comment").html();
                            jQuery("#payment_rakutenpay").prepend(content);

                            if (!jQuery("#payment_rakutenpay_redirect").val()) {
                                jQuery("#payment_rakutenpay_redirect").attr("value","'.$redirect.'");
                            }

                            jQuery("#row_payment_rakutenpay_comment").remove();
                            jQuery("#payment_rakutenpay").css("background", "'.$backgroundCss.'");

                            jQuery("#payment_rakutenpay_environment").change(function(){
                                if (jQuery("#payment_rakutenpay_environment").val() == "sandbox") {
                                 Modal.message("warning", "'.$alertEnvironment.'");
                                }
                            });
                            var init  = "'.$init.'";
                            var cnpj  = "'.$cnpj.'";
                            var api_key  = "'.$api_key.'";
                            var signature_key  = "'.$signature_key.'";
                            var email  = "'.$email.'";

                            if (init) {
                                if (!email || !cnpj || !api_key || !signature_key) {
                                    Modal.message("error", "'.$alertEmailToken.'");
                                }
                            }
                        });
                     </script>';
        $comment .= $interface;

        return $comment;
    }
}
