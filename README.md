Magento Resize Category Images
==============================

This is a free magento extension/module to resize category images. 
Join the converstation at: 
[How to resize Magento e-Commerce category images](http://dltr.org/blog/magento/121/How-to-resize-Magento-e-Commerce-category-images)

## How to use this?

	/*
	 * You can pass width and height and much more, see helper for details.
	 * echo $this->helper('timage')->init($_category->getImageUrl())->resize(null, 120)
	 * echo $this->helper('timage')->init($_category->getImageUrl())->resize(120, null)
	 * echo $this->helper('timage')->init($_category->getImageUrl())->resize(120, 120)
	 */
	<div class="product-image">
		<a href="<?php echo $_category->getURL() ?>" title="<?php echo $this->htmlEscape($_category->getName()) ?>">
			<img src="<?php echo $this->helper('timage')->init($_category->getImageUrl())->resize(null, 120) ?>" alt="<?php echo $this->htmlEscape($_category->getName()) ?>"/>
		</a>
	</div>
	
# How to crop image?
`
<img src="<?php echo $this->helper('timage')->init($v->getImageUrl())->setWidth(230)->setHeight(200)->crop()->resize() ?>" alt="..."/>
`

###visit: dltr.org for more [Magento Tips, Tricks and Free Extensions](http://dltr.org/).
