<?php
namespace twikilib\utils;

// tries to load a missing GD extension
if( ! extension_loaded('gd')) {
	dl('gd.so');
}

/**
 * Improved by Viliam Simko 2011-01-31
 *
 * Based on:
 *  Author: Simon Jarvis
 *  Copyright: 2006 Simon Jarvis
 *  Date: 08/11/06
 *  Link: http://www.white-hat-web-design.co.uk/articles/php-image-resizing.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details:
 * http://www.gnu.org/licenses/gpl.html
 */
class SimpleImage {

	var $image;
	var $image_type;

	function load($filename) {
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
		if( $this->image_type == IMAGETYPE_JPEG ) {
			$this->image = imagecreatefromjpeg($filename);
		} elseif( $this->image_type == IMAGETYPE_GIF ) {
			$this->image = imagecreatefromgif($filename);
		} elseif( $this->image_type == IMAGETYPE_PNG ) {
			$this->image = imagecreatefrompng($filename);
		}
	}

	/**
	 * Saves the image to the filesystem
	 * @param string $filename
	 * @param int $image_type IMAGETYPE_JPEG, IMAGETYPE_PNG, ...
	 * @param int $compression e.g. 75
	 * @param int $permissions chmod permissions
	 */
	function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image,$filename,$compression);
		} elseif( $image_type == IMAGETYPE_GIF ) {
			imagegif($this->image,$filename);
		} elseif( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image,$filename);
		}
		if( $permissions != null) {
			chmod($filename, $permissions);
		}
	}

	function output($image_type=IMAGETYPE_JPEG) {
		if( $image_type == IMAGETYPE_JPEG ) {
			imagejpeg($this->image);
		} elseif( $image_type == IMAGETYPE_GIF ) {
			imagegif($this->image);
		} elseif( $image_type == IMAGETYPE_PNG ) {
			imagepng($this->image);
		}
	}

	final public function getImageData($imageType = IMAGETYPE_PNG) {
		ob_start();
		$this->output($imageType);
		return ob_get_clean();
	}

	function getWidth() {
		return imagesx($this->image);
	}

	function getHeight() {
		return imagesy($this->image);
	}

	function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width,$height);
	}

	function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		$this->resize($width,$height);
	}

	function scale($scale) {
		$width = $this->getWidth() * $scale/100;
		$height = $this->getheight() * $scale/100;
		$this->resize($width,$height);
	}

	/**
	 * Resize the image without keeping the aspect ratio.
	 * You would probably need the cropToFit() function instead.
	 * @param integer $width
	 * @param integer $height
	 */
	function resize($width, $height) {
		$new_image = imagecreatetruecolor($width, $height);
		imagecopyresampled($new_image, $this->image,
		0, 0, 0, 0,
		$width, $height, $this->getWidth(), $this->getHeight());

		$this->image = $new_image;
	}

	/**
	 * Works on PHP5 with compiled-in GD library.
	 * TODO: emulate on systems where the function is not available
	 * @return boolean TRUE on success
	 */
	final public function convertToGrayscale() {
		if( function_exists('imagefilter') ) {
			return imagefilter($this->image, IMG_FILTER_GRAYSCALE);
		}
		return false;
	}

	/**
	 * Crop-to-fit algorithm.
	 *
	 * @param integer $desiredImageWidth
	 * @param integer $desiredImageHeight
	 */
	final public function cropToFit($desiredImageWidth, $desiredImageHeight) {
		$sourceAspectRatio = $this->getWidth() / $this->getHeight();
		$desiredAspectRatio = $desiredImageWidth / $desiredImageHeight;

		$resizeImageWidth = $desiredImageWidth;
		$resizeImageHeight = $desiredImageHeight;

		if ( $sourceAspectRatio > $desiredAspectRatio ) {
			// Triggered when source image is wider
			$resizeImageWidth = (int) ( $desiredImageHeight * $sourceAspectRatio );
		} else {
			// Triggered otherwise (i.e. source image is similar or taller)
			$resizeImageHeight = (int) ( $desiredImageWidth / $sourceAspectRatio );
		}

		$this->resize($resizeImageWidth, $resizeImageHeight);

		// crop the larger dimension
		$cropX = abs( $this->getWidth() - $desiredImageWidth ) / 2;
		$cropY = abs( $this->getHeight() - $desiredImageHeight ) / 2;

		// crop the image from the center
		$new_image = imagecreatetruecolor( $desiredImageWidth, $desiredImageHeight );
		imagecopy($new_image, $this->image, 0, 0, $cropX, $cropY, $desiredImageWidth, $desiredImageHeight );

		$this->image = $new_image;
	}

	/**
	 * Zoom-to-fit.
	 * TODO: transparency
	 * @param integer $desiredImageWidth
	 * @param integer $desiredImageHeight
	 */
	final public function zoomToFit($destination_width, $destination_height) {
		$source_width = $this->getWidth();
		$source_height = $this->getHeight();
		$source_ratio = $source_width / $source_height;
		$destination_ratio = $destination_width / $destination_height;

		if ($source_ratio < $destination_ratio) {
			// source has a taller ratio
			$temp_width = (int)($destination_height * $source_ratio);
			$temp_height = $destination_height;
			$destination_x = (int)(($destination_width - $temp_width) / 2);
			$destination_y = 0;
		} else {
			// source has a wider ratio
			$temp_width = $destination_width;
			$temp_height = (int)($destination_width / $source_ratio);
			$destination_x = 0;
			$destination_y = (int)(($destination_height - $temp_height) / 2);
		}
		$source_x = 0;
		$source_y = 0;
		$new_destination_width = $temp_width;
		$new_destination_height = $temp_height;

		$destination_image = imagecreatetruecolor($destination_width, $destination_height);

		// TODO: the transparency does not work for some reason
		$black = imagecolorexact($destination_image, 0,0,0);
		imagecolortransparent($destination_image, $black);
		imagesavealpha($destination_image, true);
		// ====================================================

		imagefill ($destination_image, 0, 0, imagecolorallocatealpha($destination_image, 0, 0, 0, 0));
		imagecopyresampled ($destination_image, $this->image, $destination_x, $destination_y, $source_x, $source_y, $new_destination_width, $new_destination_height, $source_width, $source_height);

		$this->image = $destination_image;
	}

	const WHITE		= 0xffffff;
	const BLACK		= 0x000000;
	const RED		= 0xff0000;
	const YELLOW	= 0xffff00;
	const NAVYBLUE	= 0x37677a;

	/**
	 * Creates an image from given text.
	 * The background will be transparent and the font color can be set as a second optional parameter.
	 *
	 * @param string $text
	 * @param int $textColor 0x(RED)(GREEN)(BLUE) e.g. 0xff8800 is orange
	 */
	final public function createFromText($text, $textColor = self::NAVYBLUE) {
		assert( is_string($text) );

		$fontid = 5;

		$width = strlen($text) * imagefontwidth($fontid);
		$height = imagefontheight($fontid);

		$this->image = imagecreatetruecolor($width, $height);

		$red	= ($textColor >> 16) & 0xff;
		$green	= ($textColor >> 8 ) & 0xff;
		$blue	= ($textColor >> 0 ) & 0xff;

		// make the image transparent (compute different color than the font color)
		$bgcolor = imagecolorallocate($this->image, ($red + 1) & 0xff, $green, $blue);
		imagefilledrectangle($this->image, 0, 0, $this->getWidth(), $this->getWidth(), $bgcolor);
		imagecolortransparent( $this->image, $bgcolor );

		// set the font color
		$imgTextColor = imagecolorallocate($this->image, $red, $green, $blue);

		imagestring($this->image, $fontid, 0, 0, $text, $imgTextColor);
	}
}