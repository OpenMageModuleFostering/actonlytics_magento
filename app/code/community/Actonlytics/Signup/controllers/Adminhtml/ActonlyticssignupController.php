<?php

class Actonlytics_Signup_Adminhtml_ItembasesignupController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Display success page if everything is good
     */
    public function finishAction()
    {
        $success = $this->getRequest()->getParam('success');
        $error = $this->getRequest()->getParam('error');

        $session = Mage::getSingleton('adminhtml/session');

        if (!empty($success)) {
            $session->addSuccess($success);
        }

        if (!empty($error)) {
            $session->addError($error);
        }

        $this->_redirect("adminhtml/system_config/edit/section/actonlytics");
    }
}
