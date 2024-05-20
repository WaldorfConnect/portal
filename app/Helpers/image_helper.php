<?php

namespace App\Helpers;

use App\Entities\Image;
use App\Models\ImageModel;
use CodeIgniter\Files\File;
use Ramsey\Uuid\Uuid;

/**
 * Having omitted the 'uploaded[$inputName]' rule, it is optional that with the given input a file as been uploaded.
 * Size is limited to 2MB, which is also the `upload_max_filesize` of the Apache (see `/etc/php/8.2/apache2/php.ini`)
 * @see https://codeigniter.com/user_guide/libraries/validation.html?highlight=validation#rules-for-file-uploads
 *
 * @param $inputName string the name attribute value of the <input type="file" name="...">
 * @param int $maxFileSizeKb int the maximum file size of the image
 * @param bool $svgAllowed whether the svg format is allowed or not
 * @return array an image validation rule, ensuring it is a common image format
 */
function createImageValidationRule(string $inputName, int $maxFileSizeKb = 2000, bool $svgAllowed = false): array
{
    return [
        $inputName => [
            'label' => 'Image File',
            'rules' => [
                "is_image[$inputName]",
                "mime_in[$inputName,image/png,image/jpg,image/jpeg,image/gif,image/webp" . ($svgAllowed ? ',image/svg+xml' : '') . "]",
                "max_size[$inputName,$maxFileSizeKb]",
            ],
        ],
    ];
}

/**
 * Convert the image to WEBP format and save it under the specified imageId
 * @see https://www.php.net/manual/de/function.exif-imagetype.php | https://www.php.net/manual/de/function.imagewebp.php
 *
 * @param File $file an image file of either one of the allowed formats (see above function)
 * @param string $author
 * @param int $quality of the resulting image
 * @param int $newWidth
 * @param int $newHeight
 * @return string
 */
function saveImage(File $file, string $author = '', int $quality = 100, int $newWidth = 0, int $newHeight = 0): string
{
    $imageId = Uuid::uuid4()->toString();
    insertImage($imageId, $author);

    $inputFilePath = $file->getTempName();

    // Create the output directory if it doesn't exist
    if (!is_dir(UPLOADED_IMAGES_DIR)) mkdir(UPLOADED_IMAGES_DIR);

    // If it's a logo file in SVG format, move it to its destination and quit
    if (mime_content_type($inputFilePath) == 'image/svg+xml') {
        rename($inputFilePath, UPLOADED_IMAGES_DIR . $imageId . '.svg');
        return $imageId;
    }

    $outputFilePath = UPLOADED_IMAGES_DIR . $imageId . '.webp';

    // Determine image format from Exif data
    $fileType = exif_imagetype($inputFilePath);

    // Load the image - or if it's a WEBP already just move it to its destination and quit
    switch ($fileType) {
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($file);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($file);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($file);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case IMAGETYPE_WEBP:
            rename($inputFilePath, $outputFilePath);
            return $imageId;
        default:
            return $imageId;
    }

    // Resize image
    if ($newWidth > 0 && $newHeight > 0) {
        $width = imagesx($image);
        $height = imagesy($image);

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresized($canvas, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $image = $canvas;
    }

    // Convert the image to WEBP and save (create/overwrite) it
    imagewebp($image, $outputFilePath, $quality);

    // Free up memory
    imagedestroy($image);

    return $imageId;
}

function deleteImage(string $id): void
{
    getImageModel()->delete($id);
}

function getImageUrlById(?string $id, string $placeholder): string
{
    if (is_null($id)) {
        return base_url($placeholder);
    }

    $path = UPLOADED_IMAGES_DIR . $id . '.svg';
    if (is_file($path)) {
        return base_url(UPLOADED_IMAGE_URL . $id . '.svg');
    }

    $path = UPLOADED_IMAGES_DIR . $id . '.webp';
    if (is_file($path)) {
        return base_url(UPLOADED_IMAGE_URL . $id . '.webp');
    }

    return base_url($placeholder);
}

function getImagePathById(?string $id): ?string
{
    if (is_null($id)) {
        return null;
    }

    $path = UPLOADED_IMAGES_DIR . $id . '.svg';
    if (is_file($path)) {
        return $path;
    }

    $path = UPLOADED_IMAGES_DIR . $id . '.webp';
    if (is_file($path)) {
        return $path;
    }

    return null;
}

function getImageAuthorById(?string $id): string
{
    if (is_null($id)) {
        return "";
    }

    $image = getImageModel()->find($id);
    return $image->getAuthor();
}

function insertImage(string $id, string $author): void
{
    $image = new Image();
    $image->setId($id);
    $image->setAuthor($author);

    getImageModel()->insert($image);
}

function getImageModel(): ImageModel
{
    return new ImageModel();
}