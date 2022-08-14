<?php

namespace App\Service\Validation;

use App\Service\SettingsInterface;
use App\Util\ValidationResult;
use Intervention\Image\Image;
use Slim\Psr7\UploadedFile;

/**
 * Class ImageValidation
 */
class ImageFileValidation extends AppValidation
{
    private array $imagePreferences;

    /**
     * Constructor
     *
     * @param SettingsInterface $settings
     */
    public function __construct(SettingsInterface $settings)
    {
        $this->imagePreferences = $settings->get(Image::class);
    }

    /**
     * Validate an uploaded image
     *
     * @param UploadedFile $file
     */
    public function validateUpload(UploadedFile $file)
    {
        $validationResult = new ValidationResult(__('Image cannot be used'));

        $this->validateFileType($file, $validationResult);
        $this->validateFileSize($file, $validationResult);
        $this->validateFilename($file, $validationResult);

        $this->throwOnError($validationResult);
    }

    /**
     * Validate the file type
     *
     * @param UploadedFile     $file
     * @param ValidationResult $validationResult
     */
    private function validateFileType(UploadedFile $file, ValidationResult $validationResult)
    {
        $mimeType = $file->getClientMediaType();
        if (!isset($this->imagePreferences['allowed_mime_types'][$mimeType])) {
            $types = implode(', ', array_unique(array_values($this->imagePreferences['allowed_mime_types'])));

            $errorMessage = __(
                'Only {types} are allowed file types',
                ['types' => $types]
            );
            $validationResult->setError('filetype', $errorMessage);
        }
    }

    /**
     * Validate the file size
     *
     * @param UploadedFile     $file
     * @param ValidationResult $validationResult
     */
    private function validateFileSize(UploadedFile $file, ValidationResult $validationResult)
    {
        $maximumFileSize = $this->imagePreferences['max_file_size'];
        if ($file->getSize() > $maximumFileSize) {
            $maximumFileSizeInMB = ($maximumFileSize / 1024) / 1024;
            $validationResult->setError('filesize',
                __(
                    'The maximum allowed file size is {size}MB',
                    ['size' => $maximumFileSizeInMB])
            );
        }
    }

    /**
     * Validate the file name given by the client
     *
     * @param UploadedFile     $file
     * @param ValidationResult $validationResult
     */
    private function validateFilename(UploadedFile $file, ValidationResult $validationResult)
    {
        $illegalCharacters = ['\\', '/', ':', '*', '?', '"', '<', '>', '|'];
        $clientFileName = $file->getClientFilename();
        foreach ($illegalCharacters as $illegalCharacter) {
            if (strpos($clientFileName, $illegalCharacter) !== false) {
                $validationResult->setError('filename',
                    __('The following characters are NOT allowed in the file name: "\\" "/" ":" "*" "?" "<" ">" "|" and " (quotation mark). Please rename your file'));
                break;
            }
        }
    }
}