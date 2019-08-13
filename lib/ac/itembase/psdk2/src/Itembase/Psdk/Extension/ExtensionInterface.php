<?php
namespace Itembase\Psdk\Extension;

/**
 * Interface ExtensionInterface
 *
 * In order to be loaded extension must have Extension class in it's namespace and it should implement
 * ExtensionInterface interface.
 *
 * getExtensionName() is used by ExtensionLoader to set the name of the extension in ServiceContainer during loading.
 * Keep in mind that ExtensionLoading is checking if provided name is already registered in ServiceContainer.
 *
 * @package       Itembase\Psdk\Extension
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
interface ExtensionInterface
{
    /**
     * Getter which returns extension name.
     *
     * @return string
     */
    public function getExtensionName();
}
