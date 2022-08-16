<?php
declare(strict_types=1);

namespace MichalHepner\PharCompiler;

interface StubInterface
{
    public function getPackageName(): string;
    public function getEntryPoint(): string;
    public function toString(): string;
}
