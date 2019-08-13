<?php

namespace Itembase\Psdk\Platform\Frontend;

use Itembase\Psdk\Http\Request;

/**
 * Interface ControllerInterface
 *
 * ControllerInterface is an interface to provide developer possibility to make a platform specific renderer of the SDK
 * response or to handle response manually.
 *
 * It should be available in ServiceContainer by tag Itembase\Psdk\Http\HttpHandler::FRONTEND_RESPONSE_HANDLER.
 *
 * It's important to use predefined list of platform names, because backend service is depending on correct platform
 * name
 *
 * @package       Itembase\Psdk\Platform\Frontend
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
interface ControllerInterface
{
    public function renderResponse(Request $request, $responses);
}
