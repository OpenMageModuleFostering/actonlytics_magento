<?php

class Actonlytics_Signup_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function registerCheck()
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

        /** @var \Itembase\Psdk\Platform\MultiShop\MultishopAbstract $ms */
        $ms    = $core->getServiceContainer()->getService(\Itembase\Psdk\Core::SERVICE_MULTISHOP);
        $shops = $ms->getList();

        /** @var \Itembase\Psdk\Platform\MultiShop\Shop $shop */
        foreach ($shops as $shop) {
            if ($shop->registered) {
                return true;
            }
        }

        $content = sprintf(
            '<div class="entry-edit">
                <div class="section-config">
                    <div class="entry-edit-head">
                        <h4 class="icon-head head-edit-form fieldset-legend">Activation is required</h4>
                    </div>
                    <div id="base_fieldset" class="fieldset ">
                        <div class="notification-global" style="font-size: larger;">
                            To use that service please <a href="%s">signup</a> your shop with actonlytics!
                        </div>
                    </div>
                </div>
            </div>',
            Mage::helper("adminhtml")->getUrl("adminhtml/system_config/edit/section/actonlytics")
        );

        $core->done();

        return $content;
    }
}
