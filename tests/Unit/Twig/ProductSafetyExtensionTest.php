<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Product\Model\ProductInterface;
use Tmonier\SyliusGpsrPlugin\Model\ProductSafetyView;
use Tmonier\SyliusGpsrPlugin\Resolver\ProductSafetyResolverInterface;
use Tmonier\SyliusGpsrPlugin\Twig\ProductSafetyExtension;

final class ProductSafetyExtensionTest extends TestCase
{
    public function testItRegistersTwigFunction(): void
    {
        $resolver = $this->createMock(ProductSafetyResolverInterface::class);
        $extension = new ProductSafetyExtension($resolver);

        $functions = $extension->getFunctions();

        self::assertCount(1, $functions);
        self::assertSame('tmonier_gpsr_product_safety', $functions[0]->getName());
    }

    public function testResolveDelegatesToTheResolver(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $view = new ProductSafetyView(null, null, false, null, null, null, null);

        $resolver = $this->createMock(ProductSafetyResolverInterface::class);
        $resolver->expects(self::once())
            ->method('resolve')
            ->with($product, 'fr_FR')
            ->willReturn($view);

        $extension = new ProductSafetyExtension($resolver);
        $result = $extension->resolve($product, 'fr_FR');

        self::assertSame($view, $result);
    }

    public function testResolveReturnsNullWhenResolverReturnsNull(): void
    {
        $product = $this->createMock(ProductInterface::class);

        $resolver = $this->createMock(ProductSafetyResolverInterface::class);
        $resolver->expects(self::once())
            ->method('resolve')
            ->with($product, null)
            ->willReturn(null);

        $extension = new ProductSafetyExtension($resolver);
        $result = $extension->resolve($product);

        self::assertNull($result);
    }
}
