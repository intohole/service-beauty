<?php
class Utils_Thumbnail {

	const SIZE_FIXED		= 1;
	const SIZE_FIT_LONGEST	= 2;
	const SIZE_FIT_SHORTEST	= 3;

	private $file;
	private $mime;
	
	private $src;
	private $dst;

	public function __construct($file, $mime=NULL) {
		$this->file = $file;
		$this->mime = $mime ? $mime : (new finfo())->file($this->file, FILEINFO_MIME_TYPE);
	}

	/**
	 * Creates a thumbnail from the original image resource.
	 *
	 * The original image must have been provided to the
	 * constructor of this class.
	 *
	 * @param	int		width					The width of the thumbnail
	 * @param	int		height					The height of the thumbnail
	 * @param	int		sizeTransformation		A flag which specifies what kind of
	 *											transformation will be applied to the
	 *											final size of the thumbnail. Currently
	 *											3 different values are supported: SIZE_FIXED
	 *											applies no transformation and the thumbnail will
	 *											have the exact size provided to the function;
	 *											SIZE_FIT_LONGEST will scale the thumbnail
	 *											so that the longest side fully fits within the specified size;
	 *											SIZE_FIT_SHORTEST will scale the thumbnail
	 *											so that the shortest side fully fits within the specified size.
	 *											Both SIZE_FIT_LONGEST and SIZE_FIT_SHORTEST
	 *											will keep the aspect ratio of the original image.
	 * @return	Thumbnailr\Thumbnailr	This instance of Thumbnailr to allow function chaining.
	 * @throws	Exception	If the size transformation flag contains an invalid value.
	 * @since	1.1
	 */
	public function buildThumbnail($width, $height, $sizeTransformation = self::SIZE_FIT_LONGEST) {
		$size = getimagesize($this->file);

		$oWidth = $size[0];
		$oHeight = $size[1];

		switch ($sizeTransformation)
		{
			case self::SIZE_FIXED:

				break;

			case self::SIZE_FIT_LONGEST:
			case self::SIZE_FIT_SHORTEST:
				$rHeight = $width * $oHeight / $oWidth;
				$rWidth = $height * $oWidth / $oHeight;

				if ($sizeTransformation == self::SIZE_FIT_LONGEST) {
					if ($rHeight <= $height)
						$height = $rHeight;
					else
						$width = $rWidth;
				} else if ($sizeTransformation == self::SIZE_FIT_SHORTEST) {
					if ($rHeight > $height)
						$height = $rHeight;
					else
						$width = $rWidth;
				}
				break;
			default:
				throw new Exception("Invalid size transformation modifier, expecting SIZE_FIXED, SIZE_FIT_LONGEST or SIZE_FIT_SHORTEST");
		}

		switch ($this->mime) {
			case 'image/gif':
				/*$old_image = ImageCreateFromGif($this->file);
				$alpha = imagecolortransparent($old_image);
				if($alpha >= 0){
					$trnprt_color    = imagecolorsforindex($old_image, $alpha);
					$trnprt_indx    = imagecolorallocate($new_image, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
					imagefill($new_image, 0, 0, $trnprt_indx);
					imagecolortransparent($new_image, $trnprt_indx);
				}*/
				$this->src = imagecreatefromgif($this->file);
				break;
			case 'image/jpeg':
				$this->src = imagecreatefromjpeg($this->file);
				break;
			case 'image/png':
				$this->src = imagecreatefrompng($this->file);
				/*$old_image = ImageCreateFromPng($this->file);
				imagealphablending($new_image, false);
				imagesavealpha($new_image, true);*/
				break;
			default:
				throw new Exception("Unrecognized file type");
		}
			
		$this->dst = imagecreatetruecolor($width, $height);
		imagecopyresampled($this->dst, $this->src, 0, 0, 0, 0, $width, $height, $oWidth, $oHeight);

		// to allow method chaining
		return $this;
	}

	/**
	 * Returns a base 64 representation of the thumbnail
	 * in PNG format.
	 *
	 * @param compressionLevel int the compression level
	 *			to use, 0 being no compression and 9 being
	 *			maximum compression
	 *
	 */
	public function toPngBase64($compressionLevel = 5)
	{
		ob_start();

		$this->toPngFile(NULL, $compressionLevel);
		$b64 = ob_get_contents();

		ob_end_clean();

		return base64_encode($b64);
	}

	/**
	 * Writes the thumbnail as a PNG file.
	 *
	 */
	public function toPngFile($file = NULL, $compressionLevel = 5)
	{
		return imagepng($this->dst, $file, $compressionLevel);
	}

	/**
	 * Returns a base 64 representation of the thumbnail
	 * in JPEG format.
	 *
	 * @param quality int the JPEG quality of the thumbnail,
	 *			0 being the worst quality and 100 being
	 *			the highest
	 *
	 */
	public function toJpegBase64($quality = 75)
	{
		ob_start();

		$this->toJpegFile(NULL, $quality);
		$b64 = ob_get_contents();

		ob_end_clean();

		return base64_encode($b64);
	}

	public function toJpegFile($file = NULL, $quality = 75) {
		if ($file == NULL) {
			list($prefix, $ext) = explode('.', $this->file);
			$file = $prefix.'_thumb.jpg';
		}
		
		return imagejpeg($this->dst, $file, $quality);
	}

}