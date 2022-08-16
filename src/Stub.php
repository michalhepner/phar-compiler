<?php
declare(strict_types=1);

namespace MichalHepner\PharCompiler;

class Stub implements StubInterface
{
    public function __construct(protected string $packageName, protected string $entryPoint) {}

    public function toString(): string
    {
        $contents = <<<EOT
#!/usr/bin/env php
<?php

Phar::mapPhar('%1\$s');

require 'phar://%1\$s/%2\$s';

__HALT_COMPILER();

EOT;

        return sprintf($contents, $this->packageName, $this->entryPoint);
    }

    public function getPackageName(): string
    {
        return $this->packageName;
    }

    public function getEntryPoint(): string
    {
        return $this->entryPoint;
    }
}
