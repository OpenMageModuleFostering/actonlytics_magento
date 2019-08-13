<?php
namespace Itembase\PsdkExtension\RemoteFS;

use Itembase\Psdk\Extension\ExtensionInterface;
use Itembase\Psdk\Http\Request;
use Itembase\Psdk\Http\RequestAwareInterface;
use Itembase\Psdk\Http\Response;

/**
 * Class Extension
 *
 * @package       Itembase\PsdkExtension\RemoteFS
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class Extension implements ExtensionInterface, RequestAwareInterface
{
    /**
     * @return string
     */
    public function getExtensionName()
    {
        return 'remotefs';
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @throws \Exception
     */
    public function handleRequest(Request $request, Response $response)
    {
        $supportedActions = array('rfs-limits', 'rfs-deploy', 'rfs-delete', 'rfs-rmdir');

        if (!$request->matchActions($supportedActions)) {
            return;
        }

        $fs     = new Filesystem();
        $fsResp = null;

        switch ($request->getAction()) {
            case 'rfs-limits':
                $fsResp = array('limits' => $fs->getLimits());
                break;
            case 'rfs-deploy':
                $fsResp = array('status_deploy' => $fs->upload());
                break;
            case 'rfs-delete':
                $fsResp = array('status_delete' => $fs->delete());
                break;
            case 'rfs-rmdir':
                $fsResp = array('status_delete_folder', $fs->deleteFolder());
                break;
        }

        $response->setData($fsResp);
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
     * @return string
     */
    public function getRequestVersion()
    {
        return '1.0';
    }
}
