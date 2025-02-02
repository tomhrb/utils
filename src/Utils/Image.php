<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * Basic manipulation with images.
 *
 * <code>
 * $image = Image::fromFile('nette.jpg');
 * $image->resize(150, 100);
 * $image->sharpen();
 * $image->send();
 * </code>
 *
 * @method void alphaBlending(bool $on)
 * @method void antialias(bool $on)
 * @method void arc($x, $y, $w, $h, $start, $end, $color)
 * @method void char(int $font, $x, $y, string $char, $color)
 * @method void charUp(int $font, $x, $y, string $char, $color)
 * @method int colorAllocate($red, $green, $blue)
 * @method int colorAllocateAlpha($red, $green, $blue, $alpha)
 * @method int colorAt($x, $y)
 * @method int colorClosest($red, $green, $blue)
 * @method int colorClosestAlpha($red, $green, $blue, $alpha)
 * @method int colorClosestHWB($red, $green, $blue)
 * @method void colorDeallocate($color)
 * @method int colorExact($red, $green, $blue)
 * @method int colorExactAlpha($red, $green, $blue, $alpha)
 * @method void colorMatch(Image $image2)
 * @method int colorResolve($red, $green, $blue)
 * @method int colorResolveAlpha($red, $green, $blue, $alpha)
 * @method void colorSet($index, $red, $green, $blue)
 * @method array colorsForIndex($index)
 * @method int colorsTotal()
 * @method int colorTransparent($color = NULL)
 * @method void convolution(array $matrix, float $div, float $offset)
 * @method void copy(Image $src, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH)
 * @method void copyMerge(Image $src, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH, $opacity)
 * @method void copyMergeGray(Image $src, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH, $opacity)
 * @method void copyResampled(Image $src, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH)
 * @method void copyResized(Image $src, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH)
 * @method Image cropAuto(int $mode = -1, float $threshold = .5, int $color = -1)
 * @method void dashedLine($x1, $y1, $x2, $y2, $color)
 * @method void ellipse($cx, $cy, $w, $h, $color)
 * @method void fill($x, $y, $color)
 * @method void filledArc($cx, $cy, $w, $h, $s, $e, $color, $style)
 * @method void filledEllipse($cx, $cy, $w, $h, $color)
 * @method void filledPolygon(array $points, $numPoints, $color)
 * @method void filledRectangle($x1, $y1, $x2, $y2, $color)
 * @method void fillToBorder($x, $y, $border, $color)
 * @method void filter($filtertype)
 * @method void flip(int $mode)
 * @method array ftText($size, $angle, $x, $y, $col, string $fontFile, string $text, array $extrainfo = NULL)
 * @method void gammaCorrect(float $inputgamma, float $outputgamma)
 * @method int interlace($interlace = NULL)
 * @method bool isTrueColor()
 * @method void layerEffect($effect)
 * @method void line($x1, $y1, $x2, $y2, $color)
 * @method void paletteCopy(Image $source)
 * @method void paletteToTrueColor()
 * @method void polygon(array $points, $numPoints, $color)
 * @method array psText(string $text, $font, $size, $color, $backgroundColor, $x, $y, $space = NULL, $tightness = NULL, float $angle = NULL, $antialiasSteps = NULL)
 * @method void rectangle($x1, $y1, $x2, $y2, $col)
 * @method Image rotate(float $angle, $backgroundColor)
 * @method void saveAlpha(bool $saveflag)
 * @method Image scale(int $newWidth, int $newHeight = -1, int $mode = IMG_BILINEAR_FIXED)
 * @method void setBrush(Image $brush)
 * @method void setPixel($x, $y, $color)
 * @method void setStyle(array $style)
 * @method void setThickness($thickness)
 * @method void setTile(Image $tile)
 * @method void string($font, $x, $y, string $s, $col)
 * @method void stringUp($font, $x, $y, string $s, $col)
 * @method void trueColorToPalette(bool $dither, $ncolors)
 * @method array ttfText($size, $angle, $x, $y, $color, string $fontfile, string $text)
 * @property-read int $width
 * @property-read int $height
 * @property-read resource $imageResource
 */
class Image extends Nette\Object
{
	/** {@link resize()} only shrinks images */
	const SHRINK_ONLY = 0b0001;

	/** {@link resize()} will ignore aspect ratio */
	const STRETCH = 0b0010;

	/** {@link resize()} fits in given area so its dimensions are less than or equal to the required dimensions */
	const FIT = 0b0000;

	/** {@link resize()} fills given area so its dimensions are greater than or equal to the required dimensions */
	const FILL = 0b0100;

	/** {@link resize()} fills given area exactly */
	const EXACT = 0b1000;

	/** image types */
	const JPEG = IMAGETYPE_JPEG,
		PNG = IMAGETYPE_PNG,
		GIF = IMAGETYPE_GIF;

	const EMPTY_GIF = "GIF89a\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\x00\x00\x00!\xf9\x04\x01\x00\x00\x00\x00,\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x02D\x01\x00;";

	/** @deprecated */
	const ENLARGE = 0;

	/** @var resource */
	private $image;


	/**
	 * Returns RGB color.
	 * @param  int  red 0..255
	 * @param  int  green 0..255
	 * @param  int  blue 0..255
	 * @param  int  transparency 0..127
	 * @return array
	 */
	public static function rgb($red, $green, $blue, $transparency = 0)
	{
		return [
			'red' => max(0, min(255, (int) $red)),
			'green' => max(0, min(255, (int) $green)),
			'blue' => max(0, min(255, (int) $blue)),
			'alpha' => max(0, min(127, (int) $transparency)),
		];
	}


	/**
	 * Opens image from file.
	 * @param  string
	 * @param  mixed  detected image format
	 * @throws Nette\NotSupportedException if gd extension is not loaded
	 * @throws UnknownImageFileException if file not found or file type is not known
	 * @return self
	 */
	public static function fromFile($file, & $format = NULL)
	{
		if (!extension_loaded('gd')) {
			throw new Nette\NotSupportedException('PHP extension GD is not loaded.');
		}

		static $funcs = [
			self::JPEG => 'imagecreatefromjpeg',
			self::PNG => 'imagecreatefrompng',
			self::GIF => 'imagecreatefromgif',
		];
		$format = @getimagesize($file)[2]; // @ - files smaller than 12 bytes causes read error

		if (!isset($funcs[$format])) {
			throw new UnknownImageFileException(is_file($file) ? "Unknown type of file '$file'." : "File '$file' not found.");
		}
		return new static(Callback::invokeSafe($funcs[$format], [$file], function ($message) {
			throw new ImageException($message);
		}));
	}


	/**
	 * Create a new image from the image stream in the string.
	 * @param  string
	 * @param  mixed  detected image format
	 * @return self
	 * @throws ImageException
	 */
	public static function fromString($s, & $format = NULL)
	{
		if (!extension_loaded('gd')) {
			throw new Nette\NotSupportedException('PHP extension GD is not loaded.');
		}

		if (func_num_args() > 1) {
			$tmp = @getimagesizefromstring($s)[2]; // @ - strings smaller than 12 bytes causes read error
			$format = in_array($tmp, [self::JPEG, self::PNG, self::GIF], TRUE) ? $tmp : NULL;
		}

		return new static(Callback::invokeSafe('imagecreatefromstring', [$s], function ($message) {
			throw new ImageException($message);
		}));
	}


	/**
	 * Creates blank image.
	 * @param  int
	 * @param  int
	 * @param  array
	 * @return self
	 */
	public static function fromBlank($width, $height, $color = NULL)
	{
		if (!extension_loaded('gd')) {
			throw new Nette\NotSupportedException('PHP extension GD is not loaded.');
		}

		$width = (int) $width;
		$height = (int) $height;
		if ($width < 1 || $height < 1) {
			throw new Nette\InvalidArgumentException('Image width and height must be greater than zero.');
		}

		$image = imagecreatetruecolor($width, $height);
		if (is_array($color)) {
			$color += ['alpha' => 0];
			$color = imagecolorresolvealpha($image, $color['red'], $color['green'], $color['blue'], $color['alpha']);
			imagealphablending($image, FALSE);
			imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $color);
			imagealphablending($image, TRUE);
		}
		return new static($image);
	}


	/**
	 * Wraps GD image.
	 * @param  resource
	 */
	public function __construct($image)
	{
		$this->setImageResource($image);
		imagesavealpha($image, TRUE);
	}


	/**
	 * Returns image width.
	 * @return int
	 */
	public function getWidth()
	{
		return imagesx($this->image);
	}


	/**
	 * Returns image height.
	 * @return int
	 */
	public function getHeight()
	{
		return imagesy($this->image);
	}


	/**
	 * Sets image resource.
	 * @param  resource
	 * @return self
	 */
	protected function setImageResource($image)
	{
		if (!is_resource($image) || get_resource_type($image) !== 'gd') {
			throw new Nette\InvalidArgumentException('Image is not valid.');
		}
		$this->image = $image;
		return $this;
	}


	/**
	 * Returns image GD resource.
	 * @return resource
	 */
	public function getImageResource()
	{
		return $this->image;
	}


	/**
	 * Resizes image.
	 * @param  mixed  width in pixels or percent
	 * @param  mixed  height in pixels or percent
	 * @param  int    flags
	 * @return self
	 */
	public function resize($width, $height, $flags = self::FIT)
	{
		if ($flags & self::EXACT) {
			return $this->resize($width, $height, self::FILL)->crop('50%', '50%', $width, $height);
		}

		list($newWidth, $newHeight) = static::calculateSize($this->getWidth(), $this->getHeight(), $width, $height, $flags);

		if ($newWidth !== $this->getWidth() || $newHeight !== $this->getHeight()) { // resize
			$newImage = static::fromBlank($newWidth, $newHeight, self::RGB(0, 0, 0, 127))->getImageResource();
			imagecopyresampled(
				$newImage, $this->image,
				0, 0, 0, 0,
				$newWidth, $newHeight, $this->getWidth(), $this->getHeight()
			);
			$this->image = $newImage;
		}

		if ($width < 0 || $height < 0) {
			imageflip($this->image, $width < 0 ? ($height < 0 ? IMG_FLIP_BOTH : IMG_FLIP_HORIZONTAL) : IMG_FLIP_VERTICAL);
		}
		return $this;
	}


	/**
	 * Calculates dimensions of resized image.
	 * @param  mixed  source width
	 * @param  mixed  source height
	 * @param  mixed  width in pixels or percent
	 * @param  mixed  height in pixels or percent
	 * @param  int    flags
	 * @return array
	 */
	public static function calculateSize($srcWidth, $srcHeight, $newWidth, $newHeight, $flags = self::FIT)
	{
		if (is_string($newWidth) && substr($newWidth, -1) === '%') {
			$newWidth = (int) round($srcWidth / 100 * abs($newWidth));
			$percents = TRUE;
		} else {
			$newWidth = (int) abs($newWidth);
		}

		if (is_string($newHeight) && substr($newHeight, -1) === '%') {
			$newHeight = (int) round($srcHeight / 100 * abs($newHeight));
			$flags |= empty($percents) ? 0 : self::STRETCH;
		} else {
			$newHeight = (int) abs($newHeight);
		}

		if ($flags & self::STRETCH) { // non-proportional
			if (empty($newWidth) || empty($newHeight)) {
				throw new Nette\InvalidArgumentException('For stretching must be both width and height specified.');
			}

			if ($flags & self::SHRINK_ONLY) {
				$newWidth = (int) round($srcWidth * min(1, $newWidth / $srcWidth));
				$newHeight = (int) round($srcHeight * min(1, $newHeight / $srcHeight));
			}

		} else {  // proportional
			if (empty($newWidth) && empty($newHeight)) {
				throw new Nette\InvalidArgumentException('At least width or height must be specified.');
			}

			$scale = [];
			if ($newWidth > 0) { // fit width
				$scale[] = $newWidth / $srcWidth;
			}

			if ($newHeight > 0) { // fit height
				$scale[] = $newHeight / $srcHeight;
			}

			if ($flags & self::FILL) {
				$scale = [max($scale)];
			}

			if ($flags & self::SHRINK_ONLY) {
				$scale[] = 1;
			}

			$scale = min($scale);
			$newWidth = (int) round($srcWidth * $scale);
			$newHeight = (int) round($srcHeight * $scale);
		}

		return [max($newWidth, 1), max($newHeight, 1)];
	}


	/**
	 * Crops image.
	 * @param  mixed  x-offset in pixels or percent
	 * @param  mixed  y-offset in pixels or percent
	 * @param  mixed  width in pixels or percent
	 * @param  mixed  height in pixels or percent
	 * @return self
	 */
	public function crop($left, $top, $width, $height)
	{
		list($r['x'], $r['y'], $r['width'], $r['height'])
			= static::calculateCutout($this->getWidth(), $this->getHeight(), $left, $top, $width, $height);
		if (PHP_VERSION_ID > 50611) { // PHP bug #67447
			$this->image = imagecrop($this->image, $r);
		} else {
			$newImage = static::fromBlank($r['width'], $r['height'], self::RGB(0, 0, 0, 127))->getImageResource();
			imagecopy($newImage, $this->image, 0, 0, $r['x'], $r['y'], $r['width'], $r['height']);
			$this->image = $newImage;
		}
		return $this;
	}


	/**
	 * Calculates dimensions of cutout in image.
	 * @param  mixed  source width
	 * @param  mixed  source height
	 * @param  mixed  x-offset in pixels or percent
	 * @param  mixed  y-offset in pixels or percent
	 * @param  mixed  width in pixels or percent
	 * @param  mixed  height in pixels or percent
	 * @return array
	 */
	public static function calculateCutout($srcWidth, $srcHeight, $left, $top, $newWidth, $newHeight)
	{
		if (is_string($newWidth) && substr($newWidth, -1) === '%') {
			$newWidth = (int) round($srcWidth / 100 * $newWidth);
		}
		if (is_string($newHeight) && substr($newHeight, -1) === '%') {
			$newHeight = (int) round($srcHeight / 100 * $newHeight);
		}
		if (is_string($left) && substr($left, -1) === '%') {
			$left = (int) round(($srcWidth - $newWidth) / 100 * $left);
		}
		if (is_string($top) && substr($top, -1) === '%') {
			$top = (int) round(($srcHeight - $newHeight) / 100 * $top);
		}
		if ($left < 0) {
			$newWidth += $left;
			$left = 0;
		}
		if ($top < 0) {
			$newHeight += $top;
			$top = 0;
		}
		$newWidth = min($newWidth, $srcWidth - $left);
		$newHeight = min($newHeight, $srcHeight - $top);
		return [$left, $top, $newWidth, $newHeight];
	}


	/**
	 * Sharpen image.
	 * @return self
	 */
	public function sharpen()
	{
		imageconvolution($this->image, [ // my magic numbers ;)
			[-1, -1, -1],
			[-1, 24, -1],
			[-1, -1, -1],
		], 16, 0);
		return $this;
	}


	/**
	 * Puts another image into this image.
	 * @param  Image
	 * @param  mixed  x-coordinate in pixels or percent
	 * @param  mixed  y-coordinate in pixels or percent
	 * @param  int  opacity 0..100
	 * @return self
	 */
	public function place(Image $image, $left = 0, $top = 0, $opacity = 100)
	{
		$opacity = max(0, min(100, (int) $opacity));

		if (is_string($left) && substr($left, -1) === '%') {
			$left = (int) round(($this->getWidth() - $image->getWidth()) / 100 * $left);
		}

		if (is_string($top) && substr($top, -1) === '%') {
			$top = (int) round(($this->getHeight() - $image->getHeight()) / 100 * $top);
		}

		if ($opacity === 100) {
			imagecopy(
				$this->image, $image->getImageResource(),
				$left, $top, 0, 0, $image->getWidth(), $image->getHeight()
			);

		} elseif ($opacity != 0) {
			$cutting = imagecreatetruecolor($image->getWidth(), $image->getHeight());
			imagecopy(
				$cutting, $this->image,
				0, 0, $left, $top, $image->getWidth(), $image->getHeight()
			);
			imagecopy(
				$cutting, $image->getImageResource(),
				0, 0, 0, 0, $image->getWidth(), $image->getHeight()
			);

			imagecopymerge(
				$this->image, $cutting,
				$left, $top, 0, 0, $image->getWidth(), $image->getHeight(),
				$opacity
			);
		}
		return $this;
	}


	/**
	 * Saves image to the file.
	 * @param  string  filename
	 * @param  int  quality 0..100 (for JPEG and PNG)
	 * @param  int  optional image type
	 * @return bool TRUE on success or FALSE on failure.
	 */
	public function save($file = NULL, $quality = NULL, $type = NULL)
	{
		if ($type === NULL) {
			switch (strtolower($ext = pathinfo($file, PATHINFO_EXTENSION))) {
				case 'jpg':
				case 'jpeg':
					$type = self::JPEG;
					break;
				case 'png':
					$type = self::PNG;
					break;
				case 'gif':
					$type = self::GIF;
					break;
				default:
					throw new Nette\InvalidArgumentException("Unsupported file extension '$ext'.");
			}
		}

		switch ($type) {
			case self::JPEG:
				$quality = $quality === NULL ? 85 : max(0, min(100, (int) $quality));
				return imagejpeg($this->image, $file, $quality);

			case self::PNG:
				$quality = $quality === NULL ? 9 : max(0, min(9, (int) $quality));
				return imagepng($this->image, $file, $quality);

			case self::GIF:
				return imagegif($this->image, $file);

			default:
				throw new Nette\InvalidArgumentException("Unsupported image type '$type'.");
		}
	}


	/**
	 * Outputs image to string.
	 * @param  int  image type
	 * @param  int  quality 0..100 (for JPEG and PNG)
	 * @return string
	 */
	public function toString($type = self::JPEG, $quality = NULL)
	{
		ob_start(NULL, 0, PHP_OUTPUT_HANDLER_REMOVABLE);
		$this->save(NULL, $quality, $type);
		return ob_get_clean();
	}


	/**
	 * Outputs image to string.
	 * @return string
	 */
	public function __toString()
	{
		try {
			return $this->toString();
		} catch (\Throwable $e) {
		} catch (\Exception $e) {
		}
		if (isset($e)) {
			if (func_num_args()) {
				throw $e;
			}
			trigger_error("Exception in " . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
		}
	}


	/**
	 * Outputs image to browser.
	 * @param  int  image type
	 * @param  int  quality 0..100 (for JPEG and PNG)
	 * @return bool TRUE on success or FALSE on failure.
	 */
	public function send($type = self::JPEG, $quality = NULL)
	{
		if (!in_array($type, [self::JPEG, self::PNG, self::GIF], TRUE)) {
			throw new Nette\InvalidArgumentException("Unsupported image type '$type'.");
		}
		header('Content-Type: ' . image_type_to_mime_type($type));
		return $this->save(NULL, $quality, $type);
	}


	/**
	 * Call to undefined method.
	 *
	 * @param  string  method name
	 * @param  array   arguments
	 * @return mixed
	 * @throws Nette\MemberAccessException
	 */
	public function __call($name, $args)
	{
		$function = 'image' . $name;
		if (!function_exists($function)) {
			return parent::__call($name, $args);
		}

		foreach ($args as $key => $value) {
			if ($value instanceof self) {
				$args[$key] = $value->getImageResource();

			} elseif (is_array($value) && isset($value['red'])) { // rgb
				$args[$key] = imagecolorallocatealpha(
					$this->image,
					$value['red'], $value['green'], $value['blue'], $value['alpha']
				) ?: imagecolorresolvealpha(
					$this->image,
					$value['red'], $value['green'], $value['blue'], $value['alpha']
				);
			}
		}
		array_unshift($args, $this->image);

		$res = $function(...$args);
		return is_resource($res) && get_resource_type($res) === 'gd' ? $this->setImageResource($res) : $res;
	}


	public function __clone()
	{
		ob_start(NULL, 0, PHP_OUTPUT_HANDLER_REMOVABLE);
		imagegd2($this->image);
		$this->setImageResource(imagecreatefromstring(ob_get_clean()));
	}

}
