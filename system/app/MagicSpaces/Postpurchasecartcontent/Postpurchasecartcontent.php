<?php
/**
 * MAGICSPACE: postpurchasecartcontent
 * {postpurchasecartcontent[:somename]} ... {/postpurchasecartcontent} - Postpurchasecartcontent magic space is used to specify place where to display
 * for each element of purchase. You should provide optional name if you want use several magicspaces on one page.
 * You can use 'clean' param if you want to receive result without html, currency etc..
 * You can use 'withoutax' param if want to receive result without tax (even if display with tax enabled)
 * {$postpurchase:ipAddress} -> ip address
 * {$postpurchase:userId} -> system user id
 * {$postpurchase:status} -> status of purchase
 * {$postpurchase:gateway} -> payment gateway name
 * {$postpurchase:shippingPrice[:clean[:withouttax]]} -> shipping price (with tax if tax enabled)
 * {$postpurchase:shippingService} -> shipping service name
 * {$postpurchase:subTotal[:clean[:withouttax]]} -> subtotal price (with tax if tax enabled)
 * {$postpurchase:totalTax[:clean]} -> total tax
 * {$postpurchase:total[:clean]} ->  cart total
 * {$postpurchase:referer} -> referer link
 * {$postpurchase:createdAt} -> date when purchase created in d-M-Y format
 * {$postpurchase:updatedAt} -> date when purchase updated in d-M-Y format
 * {$postpurchase:notes} -> customer notes
 * {$postpurchase:discount[:clean[:withouttax]]} -> purchase discount (with tax if tax enabled)
 * {$postpurchase:shippingTax[:clean]} -> shipping tax
 * {$postpurchase:discountTax[:clean]} -> discount tax
 * {$postpurchase:subTotalTax[:clean]} -> subtotal tax
 * {$postpurchase:id} -> cart id
 *
 * ######### Billing information #############
 * {$postpurchase:billing:lastname} -> billing lastname
 * {$postpurchase:billing:firstname} -> billing firstname
 * {$postpurchase:billing:address1} -> billing address
 * {$postpurchase:billing:address2} -> billing address
 * {$postpurchase:billing:city}     -> billing address city
 * {$postpurchase:billing:state}   -> billing address state
 * {$postpurchase:billing:zip} -> billing address zip
 * {$postpurchase:billing:country} -> billing address country
 * {$postpurchase:billing:phone} -> billing address phone
 * {$postpurchase:billing:mobile} -> billing address mobile
 * {$postpurchase:billing:email} -> billing address email
 *
 * ######### Shipping information #############
 * {$postpurchase:shipping:lastname} -> billing lastname
 * {$postpurchase:shipping:firstname} -> billing firstname
 * {$postpurchase:shipping:address1} -> billing address
 * {$postpurchase:shipping:address2} -> billing address
 * {$postpurchase:shipping:city}     -> billing address city
 * {$postpurchase:shipping:state}   -> billing address state
 * {$postpurchase:shipping:zip} -> billing address zip
 * {$postpurchase:shipping:country} -> billing address country
 * {$postpurchase:shipping:phone} -> billing address phone
 * {$postpurchase:shipping:mobile} -> billing address mobile
 * {$postpurchase:shipping:email} -> billing address email
 *
 * This type of widgets you can use inside 'postpurchasecartcontent' magic space
 * It will return result for each product inside your cart
 *
 * {$postpurchase:cartitem:photo[:small|medium|large|original|product]} -> product photo (by default from product folder)
 * {$postpurchase:cartitem:price[:clean]} -> product price without tax (if product freebies return text 'freebies')
 * {$postpurchase:cartitem:tax[:clean]} -> product tax
 * {$postpurchase:cartitem:taxprice[:clean]} -> product price with tax
 * {$postpurchase:cartitem:sku} -> product sku
 * {$postpurchase:cartitem:mpn} -> product mpn
 * {$postpurchase:cartitem:name} -> product name
 * {$postpurchase:cartitem:qty} -> product quantity
 * {$postpurchase:cartitem:cartId} -> cart id
 * {$postpurchase:cartitem:total[:clean]} -> total price with tax
 * {$postpurchase:cartitem:options[:email[:cleanOptionPrice]} -> <div class="options">some options info</div>
 * {$postpurchase:cartitem:producturl} -> product url
 *
 * If you want to use it with action email system add param 'email' for magic space {postpurchasecartcontent:email}
 */

class MagicSpaces_Postpurchasecartcontent_Postpurchasecartcontent extends Tools_MagicSpaces_Abstract
{

    protected function _run()
    {
        $registry = Zend_Registry::getInstance();
        if ($registry->isRegistered('postPurchaseCart')) {
            $content = '';
            $tmpPageContent = $this->_content;
            $cartSession = $registry->get('postPurchaseCart');
            if (!in_array('email', $this->_params)) {
                $this->_content = $this->_findPageTemplateContent();
            } else {
                $session = Zend_Controller_Action_HelperBroker::getStaticHelper('session');
                $session->storeCartSessionConversionKey = $cartSession->getId();
            }
            $spaceContent = $this->_parse();
            $this->_content = $tmpPageContent;
            if (!$spaceContent) {
                $spaceContent = $this->_parse();
            }
            if ($cartSession instanceof Models_Model_CartSession) {
                $cartContent = $cartSession->getCartContent();
                if (!empty($cartContent)) {
                    foreach ($cartContent as $sid => $cartItem) {
                        $content .= preg_replace_callback(
                            '~{\$postpurchase:(cartitem:(.+))}~uU',
                            function ($matches) use ($sid) {
                                $options = array_merge(array($sid), explode(':', $matches[1]));
                                return Tools_Factory_WidgetFactory::createWidget('Postpurchase', $options)->render();
                            },
                            $spaceContent
                        );
                    }
                }
                return $content;
            }
            return '';
        }
    }

    protected function _findPageTemplateContent()
    {
        $page = Application_Model_Mappers_PageMapper::getInstance()->find($this->_toasterData['id']);
        $template = Application_Model_Mappers_TemplateMapper::getInstance()->find($page->getTemplateId());
        unset($page);
        if (!$template instanceof Application_Model_Models_Template) {
            return false;
        }
        return $template->getContent();
    }

}
