<?php

/**
 * @category   Technooze/Modules/magento-how-tos
 * @package    Technooze_Timage
 * @author     Damodar Bashyal (http://dltr.org/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Technooze_Timage_Model_Observer
{
    /**
     * Is Enabled timage category image cache
     *
     * @var bool
     */
    protected $_isEnabled;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_isEnabled = Mage::app()->useCache('timage');
    }

    /**
     * Check if full page cache is enabled
     *
     * @return bool
     */
    public function isCacheEnabled()
    {
        return $this->_isEnabled;
    }

    /**
     * Clean full category image cache in response to catalog (product) image cache clean
     *
     * @param $observer
     *
     * @return Technooze_Timage_Model_Observer
     */
    public function cleanCache(Varien_Event_Observer $observer)
    {
        $cacheDir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'cache';
        mageDelTree($cacheDir);
        return $this;
    }

    /**
     * Invalidate timage cache @todo
     * @return Technooze_Timage_Model_Observer
     */
    public function invalidateCache()
    {
        Mage::app()->getCacheInstance()->invalidateType('timage');
        return $this;
    }
}
