<?php
/**
 * Customer.php
 * @author Vitaly Vyrodov <vitaly.vyrodov@gmail.com>
 */

class Widgets_Customer_Customer extends Widgets_User_Base
{

    protected $_fieldType = 'text';

    protected $_fieldLable = '';

    protected $_customerId = null;

    protected $_checkCart = null;

    protected function _init()
    {
        parent::_init();
        $this->_view->addScriptPath(__DIR__ . '/views');
    }

    protected function _load()
    {
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('No options provided');
        }

        $this->_checkCart  = Tools_ShoppingCart::getInstance()->getContent();
        $this->_customerId = Tools_ShoppingCart::getInstance()->getCustomer()->getId();

        if (empty($this->_customerId) || empty($this->_checkCart)) {
            return '';
        } else {
            if (!empty($this->_options[0])) {
                $this->_fieldLable = $this->_options[0];
                if (!empty($this->_options[1])) {
                    $this->_fieldType = $this->_options[1];
                    unset($this->_options[1]);
                }
            }
            $this->_user = Application_Model_Mappers_UserMapper::getInstance()->find($this->_customerId);
        }

        $this->_user->loadAttributes();

        if (!empty($this->_customerId)) {
            $this->_editableMode = true;
            Zend_Layout::getMvcInstance()->getView()->headScript()->appendFile(
                $this->_websiteHelper->getUrl() . 'plugins/shopping/web/js/customer-attributes.js'
            );
        }

        $method = 'customer_' . strtolower(array_shift($this->_options));
        try {
            return $this->{'_render' . ucfirst($method)}();
        } catch (Exception $e) {
            return '<b>Method ' . $method . ' doesn\'t exist</b>';
        }
    }

    public function __call($attrName, $arguments)
    {
        if (preg_match('/^_render/', $attrName)) {
            $attrName = mb_strtolower(mb_strcut($attrName, 7));
            if (!empty($this->_options)) {
                $attrName = array_merge(array($attrName), $this->_options);
                $attrName = implode('_', $attrName);
            }
            $attrName = preg_replace('/[^\w\d-_]/ui', '_', $attrName);

            // check if we have a getter for this property
            $getter = 'get' . ucfirst($attrName);
            if (method_exists($this->_user, $getter)) {
                $value = $this->_user->$getter();
            } else {
                // or try to get attribute value
                $value = $this->_user->getAttribute($attrName);
            }

            if ($this->_editableMode) {
                $this->_view->fieldLable = $this->_fieldLable;
                $this->_view->attribute = $attrName;
                $this->_view->value = $value;
                $this->_view->userId = $this->_user->getId();
                return $this->_view->render('customer-attribute.phtml');
            }
            return $value;
        }
    }

}
