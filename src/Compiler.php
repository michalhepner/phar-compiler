<?php
declare(strict_types=1);

namespace MichalHepner\PharCompiler;

use LogicException;
use Phar;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

class Compiler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var File[]
     */
    protected $files = [];

    /**
     * @var FileTransformerInterface[]
     */
    protected array $fileTransformers = [];

    /**
     * @var Filesystem
     */
    protected $filesystem;

    public function __construct(
        array $files,
        protected StubInterface $stub,
        protected string $targetPath,
    ) {
        $this->addFiles($files);
        $this->filesystem = new Filesystem();
        $this->logger = new NullLogger();
    }

    public function compile(): void
    {
        if (file_exists($this->targetPath)) {
            throw new \RuntimeException(sprintf('File %s already exists', $this->targetPath));
        }

        $targetDir = dirname($this->targetPath);
        !file_exists($targetDir) && $this->filesystem->mkdir($targetDir);

        $phar = new Phar($this->targetPath, 0, $this->stub->getPackageName());
        $phar->setSignatureAlgorithm(Phar::SHA512);
        $phar->startBuffering();

        $files = $this->files;
        // This improves performance greatly.
        uasort($files, fn (File $a, File $b) => $a->getSize() - $b->getSize());

        foreach ($files as $file) {
            $this->logger->debug(sprintf("Processing file %s", $file->getTargetPath()));
            $fileContents = $file->getContents();

            foreach ($this->fileTransformers as $fileTransformer) {
                if ($fileTransformer->shouldTransform($file)) {
                    $this->logger->debug(sprintf(
                        "Transforming file %s by %s",
                        $file->getTargetPath(),
                        get_class($fileTransformer)
                    ));
                    $fileContents = $fileTransformer->transform($fileContents);
                }
            }

            $phar->addFromString($file->getTargetPath(), $fileContents);
        }

        $phar->setStub($this->stub->toString());

        $phar->stopBuffering();

        chmod($this->targetPath, 0755);
    }

    public function addFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    public function addFile(File $file): void
    {
        if (array_key_exists($file->getTargetPath(), $this->files)) {
            throw new LogicException(sprintf(
                'A file for target path %s has already been registered',
                $file->getTargetPath(),
            ));
        }

        $this->files[$file->getTargetPath()] = $file;
    }

    public function pushFileTransformer(FileTransformerInterface $fileTransformer): void
    {
        $this->fileTransformers[] = $fileTransformer;
    }

    public function getFileTransformers(): array
    {
        return $this->fileTransformers;
    }
}
