<?php

/**
 * @category   Technooze/Modules/magento-how-tos
 * @package    Technooze_Timage
 * @author     Damodar Bashyal (http://dltr.org/)
 * @link       http://j.mp/resizeImage
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * ref: /lib/Varien/Image.php
 */
class Technooze_Timage_Helper_Data extends Mage_Core_Helper_Abstract
{
    /* @var $imageOb Varien_Image */
    private $imageObj = '';

    var
        $width = null,
        $height = null,
        $rawImg = '',
        $img = false,
        $cacheDir = '',
        $croppedCacheDir = '',
        $croppedImage = '',
        $cachedImage = '',
        $cachedImageUrl = '',
        $ext = '',
        $bgColor = array(255, 255, 255),
        $baseUrl = '',
        $placeHolder = false,

        // image settings
        $centerCrop = false,
        $keepTransparency = true,
        $aspectRatio = true,
        $constrainOnly = true,
        $keepFrame = true,
        $keepFilename = false,
        $quality,

        // support ssl/non-ssl urls
        // set to false ip using IP
        // see issue: #20
        $removeHttp = true
;

    /**
     * Reset all previous data
     */
    protected function _reset()
    {
        $this->width = null;
        $this->height = null;
        $this->rawImg = '';
        $this->img = false;
        $this->cachedImage = '';
        $this->croppedImage = '';
        $this->cachedImageUrl = '';
        $this->ext = '';
        $this->bgColor = array(255, 255, 255);
        $this->imageObj = '';
        $this->baseUrl = $this->getBaseUrl();
        $this->keepTransparency = true;
        $this->aspectRatio = true;
        $this->constrainOnly = true;
        $this->keepFrame = true;
        $this->keepFilename = false;
        $this->quality = null;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isRemoveHttp()
    {
        return $this->removeHttp;
    }

    /**
     * @param boolean $removeHttp
     * @return Technooze_Timage_Helper_Data
     */
    public function setRemoveHttp($removeHttp)
    {
        $this->removeHttp = $removeHttp;
        return $this;
    }

    /**
     * @return string
     */
    private function getBaseUrl()
    {
        $baseUrl = Mage::getStoreConfig('web/unsecure/base_media_url');

        $removeHttp = $this->isRemoveHttp();

        // fix for warning "page contains secure and nonsecure items"
        if($removeHttp){
            $baseUrl = preg_replace('#^https?://#', '//', $baseUrl);
        }
        return $baseUrl;
    }

    /**
     * Get magento theme or default placeholder by @sergy-gzh
     *
     * @return string
     */
    public function getPlaceholderFile()
    {
        if (empty($this->placeHolder)) {

            $skinBaseDir = Mage::getDesign()->getSkinBaseDir();
            $skinPlaceholder = "/images/catalog/product/placeholder/image.jpg";
            if (file_exists($skinBaseDir . $skinPlaceholder)) {
                $baseDir = $skinBaseDir;
            } else {
                $baseDir = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default'));
                if (!file_exists($baseDir . $skinPlaceholder)) {
                    $baseDir = Mage::getDesign()->getSkinBaseDir(array('_theme' => 'default', '_package' => 'base'));
                }
            }

            $this->placeHolder = $baseDir . $skinPlaceholder;
        }

        return $this->placeHolder;
    }

    /**
     * @param string $img
     * @return $this
     */
    public function init($img = '')
    {
        Varien_Profiler::start('TImage::helper::data:init');
        $this->_reset();

        if ($img) {
            $this->rawImg = $img;
        }

        $this->imagePath($this->rawImg);

        $path_parts = pathinfo($this->img);

        $this->ext = $path_parts['extension'];

        $this->cacheDir();
        Varien_Profiler::stop('TImage::helper::data:init');
        return $this;
    }

    /**
     * @return Varien_Image
     */
    protected function _getImageObj()
    {
        if (empty($this->imageObj)) {
            $this->imageObj = new Varien_Image($this->img);
        }

        return $this->imageObj;
    }

    /**
     * @param null|int $width
     * @return $this
     */
    public function setWidth($width = null)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @param null|int $height
     * @return $this
     */
    public function setHeight($height = null)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @param null|int $width
     * @param null|int $height
     * @return string
     */
    public function resize($width = null, $height = null)
    {
        if ($width) {
            $this->width = $width;
        }

        if ($height) {
            $this->height = $height;
        }

        $this->cacheIt();

        return $this->cachedImageUrl();
    }

    /**
     * @return string
     */
    public function cachedImageUrl()
    {
        $img = str_replace(array(BP . DS . 'media', BP), '', $this->cachedImage);
        $img = trim(str_replace('\\', '/', $img), '/');

        return $this->baseUrl . $img;
    }

    /**
     * @return string
     */
    public function getCroppedCache()
    {
        $this->croppedImage = $this->croppedCacheDir . md5($this->img . $this->width . $this->height) . '.' . $this->ext;

        if (file_exists($this->croppedImage)) {
            return $this->croppedImage;
        }

        $this->cropIt();

        return '';
    }

    /**
     * @return string
     */
    public function cacheIt()
    {
        if( !$this->keepFilename ){
            $this->cachedImage = $this->cacheDir . md5($this->img . $this->width . $this->height) . '.' . $this->ext;
        }else{
            $parts = pathinfo($this->img);
            $this->cachedImage = $this->cacheDir . $parts['filename'] . '_' . $this->width . '_' . $this->height . '.' . $this->ext;
        }

        if (file_exists($this->cachedImage)) {
            return $this->cachedImage;
        }

        $this->resizer();

        return '';
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     * @param $quality
     * @return $this
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * Guarantee, that image picture width/height will not be distorted.
     * Applicable before calling resize()
     * It is true by default.
     * @param bool $bool
     * @return $this
     */
    public function keepAspectRatio($bool = true)
    {
        $this->aspectRatio = $bool;
        return $this;
    }

    /**
     * Guarantee, that image will have dimensions, set in $width/$height
     * Applicable before calling resize()
     * Not applicable, if keepAspectRatio(false)
     * @param bool $bool
     * @return $this
     */
    public function keepFrame($bool = true)
    {
        $this->keepFrame = $bool;
        return $this;
    }

    /**
     * If set to true, filename will not be md5 hashed
     * @param bool $bool
     * @return $this
     */
    public function keepFilename($bool = false)
    {
        $this->keepFilename = $bool;
        return $this;
    }

    /**
     * Guarantee, that image picture will not be bigger, than it was.
     * Applicable before calling resize()
     * It is false by default
     * @param bool $bool
     * @return $this
     */
    public function constrainOnly($bool = false)
    {
        $this->constrainOnly = $bool;
        return $this;
    }

    /**
     * Guarantee, that image will not lose transparency if any.
     * Applicable before calling resize()
     * It is true by default.
     *
     * $alphaOpacity - TODO, not used for now
     */
    public function keepTransparency($flag, $alphaOpacity = null)
    {
        $this->keepTransparency = $flag;
        return $this;
    }

    /**
     * @param int $top
     * @param int $left
     * @param int $right
     * @param int $bottom
     */
    private function cropIt($top = 0, $left = 0, $right = 0, $bottom = 0)
    {
        Varien_Profiler::start('TImage::helper::data:cropIt');
        try {
            $this->_getImageObj()->crop($top, $left, $right, $bottom);
            $this->_getImageObj()->save($this->croppedImage);
            $this->img = $this->croppedImage;
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }
        Varien_Profiler::stop('TImage::helper::data:cropIt');
    }

    /**
     * Crop an image from the center
     * Using original image size and desired size
     * @todo: Possibly we can merge centerCrop() & crop()
     */
    public function centerCrop()
    {

        $this->centerCrop = true;

        $cache = $this->getCroppedCache();
        if (!empty($cache)) {
            $this->img = $cache;
        } else {
            try {
                $width = $this->width;
                $height = $this->height;
                $origWidth = $this->getOriginalWidth();
                $origHeight = $this->getOriginalHeight();

                $ratio = max($width / $origWidth, $height / $origHeight);
                $y = ($origHeight - $height / $ratio) / 2;
                $newHeight = $height / $ratio;
                $x = ($origWidth - $width / $ratio) / 2;
                $newWidth = $width / $ratio;

                if ($origHeight > $newHeight) {
                    $bottom = $top = ($origHeight - $newHeight) / 2;
                } else {
                    $bottom = $top = 0;
                }

                if ($origWidth > $newWidth) {
                    $left = $right = ($origWidth - $newWidth) / 2;
                } else {
                    $left = $right = 0;
                }

                $this->cropIt($top, $left, $right, $bottom);

            } catch (Exception $e) {
                Mage::throwException($e->getMessage());
            }
        }
        return $this;

    }

    /**
     * Crop an image.
     *
     * @param int $top . Default value is 0
     * @param int $left . Default value is 0
     * @param int $right . Default value is 0
     * @param int $bottom . Default value is 0
     * @access public
     * @return $this
     */
    public function crop($top = 0, $left = 0, $right = 0, $bottom = 0)
    {
        $this->centerCrop = false;
        $cache = $this->getCroppedCache();
        if (!empty($cache)) {
            $this->img = $cache;
        } else {
            try {
                $width = $this->width;
                $height = $this->height;
                $origWidth = $this->getOriginalWidth();
                $origHeight = $this->getOriginalHeight();
                $cropHeightTrim = $cropWidthTrim = 0;

                if ($width && $height) {

                    $origRatio = $origWidth / $origHeight;
                    $cropRatio = $width / $height;

                    if ($origRatio >= $cropRatio) { // trim width
                        $cropWidth = $origHeight * $cropRatio;
                        $cropWidthTrim = 0 - ($cropWidth - $origWidth) / 2;
                    } else { // trim height
                        $cropHeight = $origWidth / $cropRatio;
                        $cropHeightTrim = 0 - ($cropHeight - $origHeight) / 2;
                    }
                }

                if (!$top && !$left && !$right && !$bottom) {
                    if ($cropWidthTrim) {
                        $right = $left = $cropWidthTrim;
                    } elseif ($cropHeightTrim) {
                        $top = $bottom = $cropHeightTrim;
                    }
                }
                $this->cropIt($top, $left, $right, $bottom);
            } catch (Exception $e) {
                Mage::throwException($e->getMessage());
            }
        }
        return $this;
    }

    /**
     *
     */
    public function resizer()
    {
        try {
            Varien_Profiler::start('TImage::helper::data:resizer');
            $this->_getImageObj()->quality($this->quality);
            $this->_getImageObj()->constrainOnly($this->constrainOnly);
            $this->_getImageObj()->keepAspectRatio($this->aspectRatio);
            $this->_getImageObj()->keepFrame($this->keepFrame);
            $this->_getImageObj()->keepTransparency($this->keepTransparency);
            $this->_getImageObj()->backgroundColor($this->bgColor);
            $this->_getImageObj()->resize($this->width, $this->height);
            $this->_getImageObj()->save($this->cachedImage);
            Varien_Profiler::stop('TImage::helper::data:resizer');
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }
    }

    /**
     * @param string $img
     */
    public function imagePath($img = '')
    {
        $mediaDir = Mage::getBaseDir('media');
        $mediaUrl = Mage::getBaseUrl('media');

        $img = trim(str_replace(array($mediaUrl, '/', '\\'), DS, $img), DS);
        $this->img = $mediaDir . DS . $img;

        /**
         * First check this file on FS
         * If it doesn't exist - try to download it from DB
         */
        if (!file_exists($this->img)) {
            Mage::helper('core/file_storage_database')->saveFileToFilesystem($this->img);
        }

        if ((!file_exists($this->img) || !is_file($this->img)) && $this->getPlaceholderFile()) {
            $this->img = $this->getPlaceholderFile();
        }
    }

    /**
     *
     */
    public function cacheDir()
    {
        $cache = BP . DS . 'media' . DS . 'catalog' . DS . 'cache' . DS;
        $cropCache = $cache . 'cropped' . DS;

        if (!is_dir($cache)) {
            @mkdir($cache, 0775, true);
        }

        if (!is_dir($cropCache)) {
            @mkdir($cropCache, 0775, true);
        }

        $this->cacheDir = $cache;
        $this->croppedCacheDir = $cropCache;
    }

    /**
     * Retrieve original image width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        return $this->_getImageObj()->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->_getImageObj()->getOriginalHeight();
    }
}
