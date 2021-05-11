<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageUploader
{
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

            return $newFileName;
        }

        return null;
    }
}