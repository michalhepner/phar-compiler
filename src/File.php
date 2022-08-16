<?php
declare(strict_types=1);

namespace MichalHepner\PharCompiler;

class File
{
    public function __construct(protected string $sourcePath, protected string $targetPath) {}

    public function getContents(): string
    {
        return file_get_contents($this->sourcePath);
    }

    public function getSourcePath(): string
    {
        return $this->sourcePath;
    }

    public function getTargetPath(): string
    {
        return $this->targetPath;
    }

    public function getSize(): int
    {
        return filesize($this->sourcePath);
    }
}
