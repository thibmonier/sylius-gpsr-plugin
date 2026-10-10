<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Twig;

use Sylius\Component\Product\Model\ProductInterface;
use Tmonier\SyliusGpsrPlugin\Model\ProductSafetyView;
use Tmonier\SyliusGpsrPlugin\Resolver\ProductSafetyResolverInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ProductSafetyExtension extends AbstractExtension
{
    public function __construct(private readonly ProductSafetyResolverInterface $productSafetyResolver)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('tmonier_gpsr_product_safety', $this->resolve(...)),
        ];
    }

    public function resolve(ProductInterface $product, ?string $localeCode = null): ?ProductSafetyView
    {
        return $this->productSafetyResolver->resolve($product, $localeCode);
    }
}
