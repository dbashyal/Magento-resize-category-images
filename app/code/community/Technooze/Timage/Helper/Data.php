<?php
/**
* @category   Technooze/Modules/magento-how-tos
* @package    Technooze_Timage
* @author     Damodar Bashyal (http://dltr.org/)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * ref: /lib/Varien/Image.php
*/
class Technooze_Timage_Helper_Data extends Mage_Core_Helper_Abstract
{
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
        $imageObj = '',
        $baseUrl = '',
        $placeHolder = false,

        // image settings
        $keepTransparency = true,
        $aspectRatio = true,
        $constrainOnly = true,
        $keepFrame = true,
        $quality
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
        $this->baseUrl = substr(Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL), 5);
        $this->keepTransparency = true;
        $this->aspectRatio = true;
        $this->constrainOnly = true;
        $this->keepFrame = true;
        $this->quality = null;
        return $this;
    }

    public function init($img=false)
    {
        $this->_reset();

        if(empty($this->placeHolder))
        {
            $this->placeHolder = Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/image.jpg');
        }

        if($img)
        {
            $this->rawImg = $img;
        }
        
        $this->imagePath($this->rawImg);
        
        $this->imageObj = new Varien_Image($this->img);
        
        $path_parts = pathinfo($this->img);
        
        $this->ext = $path_parts['extension'];
        
        $this->cacheDir();
        
        return $this;
    }

    public function setWidth($width=null)
    {
        $this->width = $width;
        return $this;
    }

    public function setHeight($height=null)
    {
        $this->height = $height;
        return $this;
    }

    public function resize($width=null, $height=null)
    {
        if($width)
        {
            $this->width = $width;
        }

        if($height)
        {
            $this->height = $height;
        }

        $this->cacheIt();
        
        return $this->cachedImageUrl();
    }
    
    public function cachedImageUrl()
    {
        $img = str_replace(BP, '', $this->cachedImage);
        $img = trim(str_replace('\\', '/', $img), '/');
        
        return $this->baseUrl . $img;
    }

    /**
     * @return string|void
     */
    public function getCroppedCache()
    {
        $this->croppedImage = $this->croppedCacheDir . md5($this->img . $this->width . $this->height) . '.' .$this->ext;
        
        if(file_exists($this->cachedImage))
        {
            return $this->cachedImage;
        }
        
        $this->cropIt();
    }

    /**
     * @return string|void
     */
    public function cacheIt()
    {
        $this->cachedImage = $this->cacheDir . md5($this->img . $this->width . $this->height) . '.' .$this->ext;

        if(file_exists($this->cachedImage))
        {
            return $this->cachedImage;
        }

        $this->resizer();
    }

    /**
     * Set image quality, values in percentage from 0 to 100
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
     */
    public function keepAspectRatio($bool=true)
    {
        $this->aspectRatio = $bool;
        return $this;
    }

    /**
     * Guarantee, that image will have dimensions, set in $width/$height
     * Applicable before calling resize()
     * Not applicable, if keepAspectRatio(false)
     */
    public function keepFrame($bool=true)
    {
        $this->keepFrame = $bool;
        return $this;
    }

    /**
     * Guarantee, that image picture will not be bigger, than it was.
     * Applicable before calling resize()
     * It is false by default
     */
    public function constrainOnly($bool=false)
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

    private function cropIt($top=0, $left=0, $right=0, $bottom=0){
        $this->imageObj->crop($top, $left, $right, $bottom);
        $this->imageObj->save($this->croppedImage);
        $this->img = $this->croppedImage;
    }

    /**
     * Crop an image.
     *
     * @param int $top. Default value is 0
     * @param int $left. Default value is 0
     * @param int $right. Default value is 0
     * @param int $bottom. Default value is 0
     * @access public
     * @return Technooze_Timage_Helper_Data
     */
    public function crop($top=0, $left=0, $right=0, $bottom=0)
    {
        $cache = $this->getCroppedCache();
        if($cache){
            $this->img = $cache;
        } else {
            try{
                $width = $this->width;
                $height = $this->height;
                $origWidth = $this->getOriginalWidth();
                $origHeight = $this->getOriginalHeight();
                $cropWidth = $origWidth;
                $cropHeight = $origHeight;
                if($width && $height){
                    // original height / original width x new width = new height
                    $wRatio = $width / $height;
                    $hRatio = $height / $width;
                    if($origHeight >= $origWidth){
                        if($height > $width){
                            $cropHeight = $origWidth * $wRatio;
                        } else {
                            $cropHeight = $origWidth * $hRatio;
                        }
                    } else {
                        if($width > $height){
                            $cropWidth = $origHeight * $wRatio;
                        } else {
                            $cropWidth = $origHeight * $hRatio;
                        }
                    }
                }
                if(!$top && !$left && !$right && !$bottom){
                    $right = $origWidth - $cropWidth;
                    $bottom = $origHeight - $cropHeight;
                }
                $this->cropIt($top, $left, $right, $bottom);
            } catch(Exception $e){
                Mage::throwException($e->getMessage());
            }
        }
        return $this;
    }

    public function resizer()
    {
        try{
            $this->imageObj->quality($this->quality);
            $this->imageObj->constrainOnly($this->aspectRatio);
            $this->imageObj->keepAspectRatio($this->aspectRatio);
            $this->imageObj->keepFrame($this->keepFrame);
            $this->imageObj->keepTransparency($this->keepTransparency);
            $this->imageObj->backgroundColor($this->bgColor);
            $this->imageObj->resize($this->width, $this->height);
            $this->imageObj->save($this->cachedImage);
        } catch(Exception $e){
            Mage::throwException($e->getMessage());
        }
    }

    public function imagePath($img='')
    {
		$img = str_replace(array(Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL), Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL)), '', $img);
        $img = trim(str_replace('/', DS, $img), DS);
        $this->img = BP . DS . $img;
        
        if((!file_exists($this->img) || !is_file($this->img)) && !empty($this->placeHolder))
        {
            $this->imagePath($this->placeHolder);
            $this->placeHolder = false;
        }
    }

    public function cacheDir()
    {
        $cache = BP . DS . 'media' . DS . 'catalog' . DS . 'cache' . DS;
        $cropCache = $cache . 'cropped' . DS;

        if(!is_dir($cache))
        {
            mkdir($cache);
        }

        if(!is_dir($cropCache))
        {
            mkdir($cropCache);
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
        return $this->imageObj->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->imageObj->getOriginalHeight();
    }
}
