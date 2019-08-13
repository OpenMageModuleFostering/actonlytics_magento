<?php
namespace Itembase\Psdk\Platform\MultiShop;

/**
 * Class Shop
 *
 * Shop class represents instance of this shop in multi-shop.
 *
 * @package       Itembase\Psdk\MultiShop
 * @author        Serghei Ilin <si@itembase.biz>
 * @copyright (c) 2016 itembase GmbH
 */
class Shop
{
    /** @var int Platform specific ID of the shop */
    public $id;

    /** @var string URL of the shop */
    public $url;

    /** @var string Name of the shop */
    public $name;

    /** @var string Main currency of the shop */
    public $currency;

    /** @var string Default language of the shop. Should be provided as ISO 639-1 code */
    public $defaultLanguage;

    /** @var string Timezone of the shop. Should PHP timezone value, like: Europe/Berlin */
    public $timezone;

    /** @var boolean Value is "true" if current request is "targeted" to that shop. */
    public $currentlyActive;

    /** @var boolean Value is "true" if shop is main/default in shopping system */
    public $default;

    /** @var boolean Value is "true" if shop is already registered (has REGISTERED_STORAGE_FLAG set in storage)*/
    public $registered;

    /**
     * Developer can use constructor to set data about shop. All parameters are optional
     *
     * @param int    $id
     * @param string $url
     * @param string $name
     * @param string $currency
     * @param string $defaultLanguage
     * @param string $timezone
     * @param bool   $currentlyActive
     * @param bool   $default
     * @param bool   $registered
     */
    public function __construct($id = null, $url = null, $name = null, $currency = null, $defaultLanguage = null,
                                $timezone = null, $currentlyActive = false, $default = false, $registered = false)
    {
        $this->id              = $id;
        $this->url             = $url;
        $this->name            = $name;
        $this->currency        = $currency;
        $this->defaultLanguage = $defaultLanguage;
        $this->timezone        = $timezone;
        $this->currentlyActive = $currentlyActive;
        $this->default         = $default;
        $this->registered      = $registered;
    }
}
