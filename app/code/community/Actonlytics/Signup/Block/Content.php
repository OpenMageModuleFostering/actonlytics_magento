<?php
class Actonlytics_Signup_Block_Content extends Mage_Adminhtml_Block_System_Config_Form
{
    const ITEMBASE_SIGNUP_SERVICE = "signup";

    protected function _toHtml()
    {
        // including build information
        include_once Mage::getBaseDir('code') . '/community/Actonlytics/Plugin/etc/build.php';
        include_once Mage::getBaseDir('lib') . '/ac/autoload.php';

        $core = new Itembase\Psdk\Core();
        $core->init();

        /** @var \Itembase\Psdk\Platform\StorageInterface $storage */
        $storage = $core->getServiceContainer()->getService(\Itembase\Psdk\Core::SERVICE_STORAGE);

        $storage->setKeyMapping(array(
            \Itembase\Psdk\Core\OAuthClient::STORAGE_API_KEY => "actonlytics/signup/" . \Itembase\Psdk\Core\OAuthClient::STORAGE_API_KEY,
            \Itembase\Psdk\Core\OAuthClient::STORAGE_API_SECRET => "actonlytics/signup/" . \Itembase\Psdk\Core\OAuthClient::STORAGE_API_SECRET,
        ));

        $core->run();

        $content = "";

        if ($core->getServiceContainer()->hasService(self::ITEMBASE_SIGNUP_SERVICE)) {
            $registration = $core->getServiceContainer()->getService(self::ITEMBASE_SIGNUP_SERVICE);
            $content = $registration->htmlSignupPage(
                Mage::helper("adminhtml")->getUrl("adminhtml/actonlyticssignup/finish"),
                substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2)
            );
        }

        $core->done();

        $content .= parent::_toHtml();

        return $content;
    }
}
