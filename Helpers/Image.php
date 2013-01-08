<?php

namespace RPI\Framework\Helpers;

/**
 * Image helpers
 * @author Matt Dunn
 */
class Image
{
    private function __construct()
    {
    }

    /**
     * Resize and convert an image to GIF, JPEG or PNG
     * @param  string  $filename          Filename of image to convert
     * @param  ingeger $width             Width of the output image canvas
     * @param  integer $height            Height of the output image canvas. If not specified, the
     *                                    output image aspect ratio is maintained. If specified,
     *                                    the output image will always be this height, and the aspect ratio
     *                                    is maintained if $keepRatio = true, otherwise the image is compressed.
     * @param  boolean $keepRatio         Keep the aspect ration of the image when using fixed canvas size
     * @param  integer $outputImageFormat Required output image format. One of IMG_PNG (default), IMG_GIF, IMG_JPG
     * @param  string  $outputFilename
     * @return array   Associate array if image information on success, or false if there is an error.
     *											"mime" => Mime type of the output image
     *											"image" => Output image buffer
     *											"fileExtension"	=> File extension of the output file
     */
    public static function resizeAndConvert(
        $filename,
        $width,
        $height = null,
        $keepRatio = true,
        $outputImageFormat = IMG_PNG,
        $outputFilename = null
    ) {
        if (file_exists($filename)) {
            $info = getimagesize($filename);
            if ($info !== false) {
                $orig_width = $info[0];
                $orig_height = $info[1];
                $mime = $info["mime"];
                $type = explode("/", $mime);
                $type = $type[1];

                $imageCreateFunc = "imagecreatefrom".$type;

                $format = null;
                $extension = null;
                switch ($outputImageFormat) {
                    case IMG_PNG:
                        $format = $extension = "png";
                        break;
                    case IMG_GIF:
                        $format = $extension = "gif";
                        break;
                    case IMG_JPG:
                    case IMG_JPEG:
                        $format = "jpeg";
                        $extension = "jpg";
                        break;
                }

                $imageSaveFunction = "image".$format;

                if (isset($format) && function_exists($imageCreateFunc) && function_exists($imageSaveFunction)) {
                    if (!isset($height)) {
                        $height = ((float) $width / (float) $orig_width) * $orig_height;
                    }

                    $calcWidth = $width;
                    $calcHeight = $height;
                    if ($keepRatio) {
                        if ($height < $width) {
                            $calcWidth = ((float) $height / (float) $orig_height) * $orig_width;
                            if ($calcWidth > $width) {
                                $calcWidth = $width;
                                $calcHeight = ((float) $width / (float) $orig_width) * $orig_height;
                            }
                        } else {
                            $calcHeight = ((float) $width / (float) $orig_width) * $orig_height;
                            if ($calcHeight > $height) {
                                $calcHeight = $height;
                                $calcWidth = ((float) $height / (float) $orig_height) * $orig_width;
                            }
                        }
                    }

                    $imageSrc = $imageCreateFunc($filename);
                    $imageNew = imagecreatetruecolor($width, $height);

                    imagealphablending($imageNew, true);
                    imagesavealpha($imageNew, true);
                    //$transparent = imagecolorallocatealpha($imageNew, 255, 255,  255, 127);
                    $white = imagecolorallocate($imageNew, 255, 255, 255);
                    imagefilledrectangle($imageNew, 0, 0, $width, $height, $white);

                    imagecopyresampled(
                        $imageNew,
                        $imageSrc,
                        ($width - $calcWidth) / 2,
                        ($height - $calcHeight) / 2,
                        0,
                        0,
                        $calcWidth,
                        $calcHeight,
                        $orig_width,
                        $orig_height
                    );

                    $buffer = false;
                    if (isset($outputFilename)) {
                        if (!pathinfo($outputFilename, PATHINFO_EXTENSION)) {
                            $outputFilename .= ".".$extension;
                        }
                        $imageSaveFunction($imageNew, $outputFilename);
                    } else {
                        ob_start();
                        $imageSaveFunction($imageNew);
                        $buffer = ob_get_clean();
                    }

                    imagedestroy($imageNew);
                    imagedestroy($imageSrc);

                    return (object) array(
                        "mime" => "image/".$format,
                        "image" => $buffer,
                        "fileExtension" => $extension,
                        "savedFilename" => $outputFilename
                    );
                }
            }
        }

        return false;
    }
}
