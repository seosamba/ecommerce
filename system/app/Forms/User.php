<?php
/**
 * User.php
 */
class Forms_User extends Zend_Form {

    public function init() {

        parent::init();

        $this->setDecorators(array('FormElements', 'Form'));

        $this->addElement(new Zend_Form_Element_Text(array(
            'name'       => 'newEmail',
            'label'      => 'New email',
            'validators' => array(
                array('EmailAddress')
            ),
            'required'   => true,
            'breakChainOnFailure' => true,
            'errorMessages' => array(
                'EmailAddress'=>'New email have not right format',
                'isEmpty' => 'New email have not right format'
             ),
	        'class'      => array('required')
        )));

        $this->addElement(new Zend_Form_Element_Text(array(
            'name'       => 'newEmailConfirm',
            'label'      => 'Confirm new email',
            'required'   => true,
            'breakChainOnFailure' => true,
            'validators' => array(
                array('EmailAddress'),
                new Zend_Validate_Identical('newEmail')
            ),
            'errorMessages' => array(
                'Identical' => 'Confirm email must match to new email',
                'EmailAddress'=>'Confirm new email have not right format',
                'isEmpty' => 'Confirm new email have not right format',
            ),
            'class'      => array('required')
        )));


        $this->addElement(new Zend_Form_Element_Password(array(
            'name'     => 'currentPassword',
            'id'       => 'current-password',
            'label'    => 'Current Password',
            'breakChainOnFailure' => true,
            'errorMessages' => array(
                'isEmpty' => 'Invalid password'
            ),
            'required' => true
        )));

        $this->addElement(new Zend_Form_Element_Password(array(
            'name'     => 'newPassword',
            'id'       => 'new-password',
            'label'    => 'New password',
            'breakChainOnFailure' => true,
            'validators' => array(
                new Zend_Validate_Identical('newPasswordConfirm'),
                new Zend_Validate_StringLength(array(
                    'encoding' => 'UTF-8',
                    'min'      => 4
                ))
            ),
            'required' => true,
            'errorMessages' => array(
                'Identical' => 'new password must match to confirm password',
                'isEmpty'   => 'Invalid new password',
                'StringLength' => 'password can\'t be less than 4 symbols'
            ),
        )));

        $this->addElement(new Zend_Form_Element_Password(array(
            'name'     => 'newPasswordConfirm',
            'id'       => 'new-password-confirm',
            'label'    => 'Confirm new password',
            'breakChainOnFailure' => true,
            'validators' => array(
                new Zend_Validate_Identical('newPassword'),
                new Zend_Validate_StringLength(array(
                    'encoding' => 'UTF-8',
                    'min'      => 4
                ))
            ),
            'required' => true,
            'errorMessages' => array(
                'isEmpty' => 'Invalid confirm new password',
                'Identical' => 'confirm password must match to new password',
                'StringLength' => 'password can\'t be less than 4 symbols'
            ),
        )));

        $this->addElement(new Zend_Form_Element_Button(array(
            'name'   => 'saveUser',
            'ignore' => true,
            'label'  => 'Save changes',
            'type'   => 'submit',
            'class'  => 'btn ticon-save',
            'decorators' => array('ViewHelper')
        )));

        $this->setElementDecorators(array(
            'ViewHelper',
            'Errors',
            'Label',
            array('HtmlTag', array('tag' => 'p'))
        ));

        $this->setElementFilters(array(
            new Zend_Filter_StripTags(),
            new Zend_Filter_StringTrim()
        ));

        $this->getElement('saveUser')->removeDecorator('Label');

    }

}
