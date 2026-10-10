<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Resolver;

use Sylius\Component\Product\Model\ProductInterface;
use Tmonier\SyliusGpsrPlugin\Model\ProductSafetyView;

interface ProductSafetyResolverInterface
{
    /**
     * Returns null when the product has no GPSR information at all.
     *
     * @param string|null $localeCode defaults to the current locale
     */
    public function resolve(ProductInterface $product, ?string $localeCode = null): ?ProductSafetyView;
}
