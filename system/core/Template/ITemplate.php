<?php

namespace ErkinApp\Template;

interface ITemplate
{
    public function getTemplatePath(): string;

    public function getFileFullPath(): string;

    public function getFileExtension(): string;

    public function prepare(string $filename, array $data): self;

    public function resolve(): string;
}