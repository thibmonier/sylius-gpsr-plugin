<?php

declare(strict_types=1);

namespace Tests\Tmonier\SyliusGpsrPlugin\Unit\Form;

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\Product;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Tmonier\SyliusGpsrPlugin\Entity\Manufacturer;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafety;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface;
use Tmonier\SyliusGpsrPlugin\Form\EventSubscriber\ProductSafetyFormSubscriber;
use Tmonier\SyliusGpsrPlugin\Form\Type\ProductSafetyType;
use Tmonier\SyliusGpsrPlugin\Repository\ProductSafetyRepositoryInterface;

final class ProductSafetyFormSubscriberTest extends TestCase
{
    private ProductSafetyRepositoryInterface&MockObject $repository;

    private ObjectManager&MockObject $manager;

    private ProductSafetyFormSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProductSafetyRepositoryInterface::class);
        $this->manager = $this->createMock(ObjectManager::class);

        /** @var FactoryInterface<ProductSafetyInterface>&MockObject $factory */
        $factory = $this->createMock(FactoryInterface::class);
        $factory->method('createNew')->willReturnCallback(static fn (): ProductSafety => new ProductSafety());

        $this->subscriber = new ProductSafetyFormSubscriber($this->repository, $factory, $this->manager, ProductSafetyType::class);
    }

    public function testItAddsTheExistingProductSafety(): void
    {
        $product = new Product();
        $existing = new ProductSafety();
        $this->repository->method('findOneByProduct')->with($product)->willReturn($existing);

        $form = $this->createMock(FormInterface::class);
        $form
            ->expects(self::once())
            ->method('add')
            ->with('productSafety', ProductSafetyType::class, self::callback(static fn (array $options): bool => false === $options['mapped'] && $existing === $options['data']))
        ;

        $this->subscriber->addProductSafetyField(new FormEvent($form, $product));
    }

    public function testItAddsANewProductSafetyLinkedToTheProduct(): void
    {
        $product = new Product();
        $this->repository->method('findOneByProduct')->willReturn(null);

        $form = $this->createMock(FormInterface::class);
        $form
            ->expects(self::once())
            ->method('add')
            ->with('productSafety', ProductSafetyType::class, self::callback(static fn (array $options): bool => $options['data'] instanceof ProductSafetyInterface && $product === $options['data']->getProduct()))
        ;

        $this->subscriber->addProductSafetyField(new FormEvent($form, $product));
    }

    public function testItIgnoresOtherData(): void
    {
        $form = $this->createMock(FormInterface::class);
        $form->expects(self::never())->method('add');

        $this->subscriber->addProductSafetyField(new FormEvent($form, null));
    }

    public function testItPersistsAFilledProductSafetyOfAValidForm(): void
    {
        $productSafety = new ProductSafety();
        $productSafety->setManufacturer(new Manufacturer());
        $product = new Product();

        $this->manager->expects(self::once())->method('persist')->with($productSafety);

        $this->subscriber->persistProductSafety(new FormEvent($this->submittedForm($product, $productSafety, true), $product));
        self::assertSame($product, $productSafety->getProduct());
    }

    public function testItDoesNotCreateEmptyRecords(): void
    {
        $this->manager->expects(self::never())->method('persist');

        $product = new Product();
        $this->subscriber->persistProductSafety(new FormEvent($this->submittedForm($product, new ProductSafety(), true), $product));
    }

    public function testItDoesNothingWhenTheFormIsInvalid(): void
    {
        $productSafety = new ProductSafety();
        $productSafety->setTypeDesignation('TX-1');

        $this->manager->expects(self::never())->method('persist');

        $product = new Product();
        $this->subscriber->persistProductSafety(new FormEvent($this->submittedForm($product, $productSafety, false), $product));
    }

    private function submittedForm(Product $product, ProductSafetyInterface $productSafety, bool $valid): FormInterface
    {
        $child = $this->createMock(FormInterface::class);
        $child->method('getData')->willReturn($productSafety);

        $form = $this->createMock(FormInterface::class);
        $form->method('getData')->willReturn($product);
        $form->method('has')->with('productSafety')->willReturn(true);
        $form->method('isValid')->willReturn($valid);
        $form->method('get')->with('productSafety')->willReturn($child);

        return $form;
    }
}
