<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private $targetDirectory;
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function setTargetDirectory(string $targetDirectory): void
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file, string $prefix = ''): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = sprintf(
            '%s-%s-%s.%s',
            $prefix,
            $safeFilename,
            uniqid(),
            $file->guessExtension()
        );

        try {
            $file->move($this->targetDirectory, $fileName);
        } catch (FileException $e) {
            throw new FileException('Failed to upload file: ' . $e->getMessage());
        }

        return $fileName;
    }

    public function remove(string $filename): bool
    {
        $filePath = $this->targetDirectory . '/' . $filename;
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}