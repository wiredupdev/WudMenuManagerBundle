<?php

namespace Wiredupdev\MenuManagerBundle\Menu;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wiredupdev\MenuManagerBundle\Menu\UriGenerator\UriGeneratorFactory;

readonly class MenuFactory
{
    public function __construct(private UriGeneratorFactory $uriGeneratorFactory)
    {
    }

    public function create(string $id, array $options = []): MenuItemInterface
    {
        $optionResolver = new OptionsResolver();

        $this->setDefaults($optionResolver);

        $this->setChildren($optionResolver);

        $options = $optionResolver->resolve($options);

        $menu = Item::create($id, $options['label'], $this->uriGeneratorResolve($options));

        $this->configAttributes($menu, $options);

        $this->configChildren($options['children'], $menu);

        unset($options);

        return $menu;
    }

    private function setDefaults(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(defaults: [
            'id' => null,
            'label' => '',
            'uri' => null,
            'attributes' => [],
            'position' => 0,
            'children' => [],
        ]);

        $resolver->addAllowedTypes('uri', ['null', 'array',  UriGeneratorInterface::class]);
        $resolver->setAllowedTypes('label', 'string');
        $resolver->setAllowedTypes('attributes', 'array');
        $resolver->setAllowedTypes('position', 'int');

        $resolver->setNormalizer('attributes', function (Options $options, array $value): array {
            foreach ($value as $type => $fields) {
                if (!\is_string($type)) {
                    throw new \InvalidArgumentException('The attributes key must be a string.');
                }

                if (!\is_array($fields)) {
                    throw new \InvalidArgumentException('The attributes type must contain an array.');
                }
                foreach ($fields as $name => $fieldValue) {
                    if (!\is_string($name)) {
                        throw new \InvalidArgumentException('The attribute field name must be a string.');
                    }
                }
            }

            return $value;
        });
    }

    private function setChildren(OptionsResolver $resolver): void
    {
        $resolver->setOptions('children', nested: function (OptionsResolver $resolver): void {
            $resolver->setPrototype(true);
            $this->setDefaults($resolver);
            $resolver->setAllowedTypes('id', 'string');
            $resolver->setRequired(['label', 'id']);
            $this->setChildren($resolver);
        });
    }

    private function uriGeneratorResolve(array $options): ?UriGeneratorInterface
    {
        if ($options['uri'] instanceof UriGeneratorInterface) {
            return $options['uri'];
        }

        return $this->uriGeneratorFactory->createFromOptions($options['uri'] ?? []);
    }

    private function configAttributes(MenuItemInterface $menuItem, array $options): void
    {
        foreach ($options['attributes'] as $type => $attribute) {
            foreach ($attribute as $name => $value) {
                $menuItem->addAttribute($type, $name, $value);
            }
        }
    }

    private function configChildren($children, MenuItemInterface $menu): void
    {
        foreach ($children as $child) {
            $item = Item::create($child['id'], $child['label'], $this->uriGeneratorResolve($child));
            $this->configAttributes($item, $child);
            $this->configChildren($child['children'] ?? [], $item);
            $menu->addChild($item);
        }
    }
}
