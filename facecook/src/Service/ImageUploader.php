<?php

namespace App\Service;

use Liip\ImagineBundle\Service\FilterService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
    private $imagine;

    public function __construct(FilterService $imagine)
    {
        $this->imagine = $imagine;
    }

    public function rename(UploadedFile $image)
    {
        return uniqid() . '.' . $image->guessExtension();
    }

    public function uploadRecipePictures(?UploadedFile $image)
    {
        // If there is no picture uploaded, this doesn't apply
        if ($image !== null) {
            // The file will be renamed with a unique id
            $newFileName =  $this->rename($image);
            
            // The picture will be moved to a specific folder
            $image->move($_ENV['RECIPE_PICTURE'], $newFileName);

            // Filter the image
            $this->imagine->getUrlOfFilteredImage('images/recipes/' . $newFileName, 'recipe_image');

            // Remove the original image
            unlink('images/recipes/' . $newFileName);

            // Move the filter image
            rename('media/cache/recipe_image/images/recipes/' . $newFileName, 'images/recipes/' . $newFileName);

            return $newFileName;
        }

        return null;
    }

    public function uploadUserAvatar(?UploadedFile $avatar)
    {
        // If there is no picture uploaded, this doesn't apply
        if ($avatar !== null) {
            // The file will be renamed with a unique id
            $newFileName =  $this->rename($avatar);
            
            // The picture will be moved to a specific folder
            $avatar->move($_ENV['USER_AVATAR'], $newFileName);

            // Filter the image
            $this->imagine->getUrlOfFilteredImage('images/avatars/' . $newFileName, 'user_avatar');

            // Remove the original image
            unlink('images/avatars/' . $newFileName);

            // Move the filter image
            rename('media/cache/user_avatar/images/avatars/' . $newFileName, 'images/avatars/' . $newFileName);

            return $newFileName;
        }

        return null;
    }
}