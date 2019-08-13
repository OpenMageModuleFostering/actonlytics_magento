<?php

/**
 * Class Actonlytics_Plugin_IndexController
 */
class Actonlytics_Plugin_IndexController extends Mage_Core_Controller_Front_Action
{
    public function apiAction()
    {
        include_once Mage::getBaseDir('code') . '/community/Actonlytics/Plugin/etc/build.php';
        include_once Mage::getBaseDir('lib')  . '/ac/autoload.php';

        $core = new Itembase\Psdk\Core();
        $core->init();

        /** @var \Itembase\Psdk\Platform\StorageInterface $storage */
        $storage = $core->getServiceContainer()->getService(\Itembase\Psdk\Core::SERVICE_STORAGE);

        $storage->setKeyMapping(array(
            \Itembase\Psdk\Core\OAuthClient::STORAGE_API_KEY => "actonlytics/signup/" . \Itembase\Psdk\Core\OAuthClient::STORAGE_API_KEY,
            \Itembase\Psdk\Core\OAuthClient::STORAGE_API_SECRET => "actonlytics/signup/" . \Itembase\Psdk\Core\OAuthClient::STORAGE_API_SECRET,
        ));

        $core->run()->done();

        exit;
    }
}
