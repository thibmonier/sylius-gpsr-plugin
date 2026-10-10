<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Product\Model\ProductInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface;

class ProductSafetyRepository extends EntityRepository implements ProductSafetyRepositoryInterface
{
    public function findOneByProduct(ProductInterface $product): ?ProductSafetyInterface
    {
        if (null === $product->getId()) {
            return null;
        }

        $result = $this->createQueryBuilder('o')
            ->addSelect('manufacturer', 'responsiblePerson', 'defaultResponsiblePerson', 'translation')
            ->leftJoin('o.manufacturer', 'manufacturer')
            ->leftJoin('manufacturer.defaultResponsiblePerson', 'defaultResponsiblePerson')
            ->leftJoin('o.responsiblePerson', 'responsiblePerson')
            ->leftJoin('o.translations', 'translation')
            ->andWhere('o.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result instanceof ProductSafetyInterface ? $result : null;
    }
}
