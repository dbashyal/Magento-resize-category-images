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
        $this->height = false;
        $this->rawImg = '';
        $this->img = false;
        $this->cacheDir = '';
        $this->cachedImage = '';
        $this->cachedImageUrl = '';
        $this->ext = '';
        $this->bgColor = array(255, 255, 255);
        $this->imageObj = '';
        $this->baseUrl = '';
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
            $this->placeHolder = Mage::getDesign()->getSkinUrl() . 'images/catalog/product/placeholder/image.jpg';
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

    public function resize($width=false, $height=false)
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
            //return $e->getMessage();
        }
    }

    public function imagePath($img='')
    {
        $this->baseUrl = str_replace('index.php/', '', Mage::getBaseUrl());
        $img = str_replace($this->baseUrl, '', $img);
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
        
        if(!is_dir($cache))
        {
            mkdir($cache);
        }
        $this->cacheDir = $cache;
    }
}