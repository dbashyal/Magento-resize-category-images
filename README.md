Magento Resize Category Images
==============================

This is a free magento extension/module to resize category images.

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
