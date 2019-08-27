<?php

class Widgets_Productparams_Productparams extends Widgets_User_Base {

    protected function _load()
    {
        if (empty($this->_options)) {
            throw new Exceptions_SeotoasterWidgetException('No options provided');
        }

        $method = strtolower(array_shift($this->_options));
        try {
            return $this->{'_render' . ucfirst($method)}();
        } catch (Exception $e) {
            return '<b>Method ' . $method . ' doesn\'t exist</b>';
        }
    }

    /**
     * @return string
     * return dropdown|radio default option title
     */
    private function _renderTitleoption()
    {
        if(is_numeric($this->_options[0]) && !empty($this->_options[1])) {
            $defaultOptionSelectionTitle = Models_Mapper_OptionMapper::getInstance()->findDefaultSelectionOptionByProductId($this->_options[0], $this->_options[1]);

            if(!empty($defaultOptionSelectionTitle)) {
                return $defaultOptionSelectionTitle['title'];
            } else {
                return '';
            }
        }
    }
}
