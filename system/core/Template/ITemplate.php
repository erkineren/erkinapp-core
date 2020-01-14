<?php

namespace ErkinApp\Template;

interface ITemplate
{
    public function getName(): string;
    
    public function getArea(): string;
    
    public function getTemplatePath(): string;

    public function getThemeAssetsPath(): string;

    public function getFileFullPath(): string;

    public function getFileExtension(): string;

    public function getAssetManager(): AssetManager;

    public function prepare(string $filename, array $data): self;

    public function resolve(): string;
}