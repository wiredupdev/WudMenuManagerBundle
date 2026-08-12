<?php

namespace Wiredupdev\MenuManagerBundle\Menu;

interface MenuItemInterface
{
    public function isActive(): bool;

    public function isVisible(): bool;

    public function show(): self;

    public function hide(): self;

    public function addAttribute(string $type, string $name, mixed $value): self;

    public function getAttribute(string $type, string $name): mixed;

    public function removeAttribute(string $type, string $name): self;

    public function hasAttribute(string $type, string $name): bool;

    public function getAttributes(): array;

    public function addChild(self $child): self;

    public function getChild(string $id): ?self;

    public function getChildren(): array;

    public function removeChild(string $id): self;

    public function getId(): string;

    public function getLabel(): string;

    public function getUri(): ?string;

    public function getUriParams(): ?array;

    public function getUriTarget(): ?string;

    public function getUriType(): ?string;

    public function getRawUri(): ?string;

    public function getPosition(): int;
}
