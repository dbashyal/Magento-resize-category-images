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
    public function cleanCache($observer)
    {
        $cacheDir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'cache';
        if (!is_dir($cacheDir)){
            mkdir($cacheDir, 0775, true);
        } else {
            $this->timageDeleteCacheFiles($cacheDir);
        }
        //@mkdir($cacheDir);
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

    /**
     * C3 function:
     * Fixes the issue where mageDelTree($path) would delete the cache directory
     * Then this module would recreate it, resulting in incorrect permissions/ownership
     *
     * @param $path
     */
    protected function timageDeleteCacheFiles($path) {
        if (is_dir($path)) {
            $entries = scandir($path);
            foreach ($entries as $entry) {
                if ($entry != '.' && $entry != '..') {
                    if (is_dir($path . DS . $entry)){
                        self::timageDeleteCacheFiles($path . DS . $entry);
                    } else {
                        @unlink($path . DS . $entry);
                    }
                }
            }
            /* Removed the deletion of directories as we don't actually have to remove them
               to clear the cache. We only need to remove the cached image files. */
            //@rmdir($path);
        } else {
            @unlink($path);
        }
    }
}
