<?php
/**
 * Copyright (C) 2015 itembase GmbH - All Rights Reserved
 */

namespace Itembase\PsdkExtension\SysInfo;

use Itembase\Psdk\Container\ContainerAwareInterface;
use Itembase\Psdk\Container\ServiceContainer;
use Itembase\Psdk\Core;
use Itembase\Psdk\Extension\ExtensionInterface;
use Itembase\Psdk\Http\Request;
use Itembase\Psdk\Http\RequestAwareInterface;
use Itembase\Psdk\Http\Response;
use Itembase\Psdk\Platform\MultiShop\MultishopAbstract;

/**
 * Class Extension
 *
 * @package       Itembase\PsdkExtension\SysInfo
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class Extension implements ExtensionInterface, ContainerAwareInterface, RequestAwareInterface
{
    /** @var ServiceContainer $serviceContainer */
    protected $serviceContainer;

    /**
     * @return string
     */
    public function getExtensionName()
    {
        return 'sysinfo';
    }

    /**
     * @param ServiceContainer $container
     */
    public function setContainer(ServiceContainer $container)
    {
        $this->serviceContainer = $container;
    }

    /**
     * @return string
     */
    public function getResponseType()
    {
        return $this->getExtensionName();
    }

    /**
     * @return string
     */
    public function getResponseVersion()
    {
        return '1.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestVersion()
    {
        return '1.0';
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @throws \Exception
     */
    public function handleRequest(Request $request, Response $response)
    {
        if (!$request->matchActions(array("getPluginData", "sysinfo"))) {
            return;
        }

        $platform = $this->serviceContainer->getService(Core::SERVICE_PLATFORM);

        $response->add("platform", $platform->getName());
        $response->add("platform_version", $platform->getVersion());
        $response->add("plugin_build", ITEMBASE_PLUGIN_BUILD);
        $response->add("plugin_version", "5.0");
        $response->add("vendor_path", ITEMBASE_VENDOR_DIR);
        $response->add('php_info', $this->phpinfoToArray());

        $index         = 0;
        $serviceList   = array();
        $extensionList = array();

        foreach ($this->serviceContainer->listServices() as $tag) {
            $service = $this->serviceContainer->getService($tag);

            if ($service instanceof ExtensionInterface && $service instanceof RequestAwareInterface) {
                $extensionList[$index++] = array(
                    'name'             => $service->getExtensionName(),
                    'tag'              => $tag,
                    'request_version'  => $service->getRequestVersion(),
                    'response_version' => $service->getResponseVersion(),
                );
            } else {
                $serviceList[] = $tag;
            }
        }

        $response->add("services", $serviceList);
        $response->add("extensions", $extensionList);

        if ($this->serviceContainer->hasService('multishop')) {
            /** @var MultishopAbstract $multiShop */
            $multiShop = $this->serviceContainer->getService('multishop');

            $response->add('support_multi_shop', $multiShop->isMultiShop());
            $response->add('shops', $multiShop->getList());
        } else {
            $response->add('support_multi_shop', false);
            $response->add('shops', null);
        }
    }

    /**
     * Handle phpinfo output and translate it to array
     *
     * @return array|mixed
     */
    private function phpinfoToArray()
    {
        ob_start();
        phpinfo(INFO_MODULES);

        $pi = preg_replace(
            array(
                '#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
                '#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
                "#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
                '#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
                .'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
                '#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
                '#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
                "# +#", '#<tr>#', '#</tr>#'
            ),
            array(
                '$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
                '<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
                "\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
                '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
                '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
                '<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'
            ),
            ob_get_clean()
        );

        $sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
        unset($sections[0]);

        $pi = array();
        foreach($sections as $section) {
            $sectionName = substr($section, 0, strpos($section, '</h2>'));

            preg_match_all(
                '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
                $section,
                $matches,
                PREG_SET_ORDER
            );

            $sectionName = strtolower(str_replace(' ', '_', $sectionName));

            foreach($matches as $match) {
                $sectionKey = strtolower(str_replace(' ', '_', $match[1]));

                if (!isset($match[3]) || $match[2] == $match[3]) {
                    $pi[$sectionName][$sectionKey] = $match[2];
                }
            }
        }

        return $pi;
    }
}
