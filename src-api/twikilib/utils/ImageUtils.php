<?php
namespace twikilib\utils;

/**
 * Encapsulates higher-level operations involving image manipulations.
 * @author Viliam Simko
 */
class ImageUtils {

	/**
	 * Creates an image reprsentation of an email address.
	 * This static method can be used as a callback in the caching mechanism.
	 * @param string $email
	 * @return mixed Binary representation of the image
	 */
	static final public function emailToImage($email) {
		$image = new SimpleImage();
		$image->createFromText($email);
		return $image->getImageData(IMAGETYPE_PNG);
	}

	/**
	 * This static method can be used as a callback in the caching mechanism.
	 * @param string $imgSrcFile
	 * @param integer $width
	 * @param integer $height
	 * @param boolean $useGrayscale
	 * @return mixed Binary representation of the image
	 */
	static final public function createImageThumbnail($imgSrcFile, $width, $height=0, $useGrayscale = false) {

		assert($width > 0 || $height > 0);
		assert( is_bool($useGrayscale) );
		assert( is_string($imgSrcFile) );

		$image = new SimpleImage();
		$image->load( $imgSrcFile );

		if($useGrayscale)
			$image->convertToGrayscale();

		if($width < 1)
			$image->resizeToHeight($height);
		elseif( $height < 1)
			$image->resizeToWidth($width);
		else
			$image->cropToFit($width, $height);

		return $image->getImageData(IMAGETYPE_PNG);
	}
}