<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Repository;

use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface;

/**
 * @extends RepositoryInterface<ProductSafetyInterface>
 */
interface ProductSafetyRepositoryInterface extends RepositoryInterface
{
    public function findOneByProduct(ProductInterface $product): ?ProductSafetyInterface;
}
