<?php
declare(strict_types=1);

namespace MichalHepner\PharCompiler;

interface FileTransformerInterface
{
    public function shouldTransform(File $file): bool;
    public function transform(string $fileContents): string;
}
