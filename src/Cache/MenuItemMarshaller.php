<?php

namespace Wiredupdev\MenuManagerBundle\Cache;

use Symfony\Component\Cache\Marshaller\MarshallerInterface;
use Wiredupdev\MenuManagerBundle\Menu\MenuFactory;
use Wiredupdev\MenuManagerBundle\Menu\MenuItemInterface;

class MenuItemMarshaller implements MarshallerInterface
{
    public function __construct(private MenuFactory $menuFactory)
    {
    }

    public function marshall(array $values, ?array &$failed): array
    {
        $serializedValues = [];
        $failed = [];
        foreach ($values as $key => $value) {
            if ($value instanceof MenuItemInterface) {
                $serializedValues[$key] = serialize([
                    '__is_menu_item' => true,
                    '__menu' => $this->toArray($value),
                ]);
                continue;
            }
            $serializedValues[$key] = serialize($value);
        }

        return $serializedValues;
    }

    public function unmarshall(string $value): mixed
    {
        $unserializedValues = unserialize($value);

        if (\is_array($unserializedValues) && isset($unserializedValues['__is_menu_item'])) {
            return $this->menuFactory->create(
                $unserializedValues['__menu']['id'],
                $unserializedValues['__menu'],
            );
        }

        return $unserializedValues;
    }

    private function toArray(MenuItemInterface $data): array
    {
        return [
            'id' => $data->getId(),
            'label' => $data->getLabel(),
            'attributes' => $data->getAttributes(),
            'position' => $data->getPosition(),
            'uri' => !$data->getUriType() ? null : [
                'raw' => [
                    'value' => $data->getRawUri(),
                    'type' => $data->getUriType(),
                    'parameters' => $data->getUriParams(),
                ],
                'target' => $data->getUriTarget(),
            ],
            'children' => array_map(fn (MenuItemInterface $menuItem) => $this->toArray($menuItem), $data->getChildren()),
        ];
    }
}
