<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Form\EventSubscriber;

use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Valid;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface;
use Tmonier\SyliusGpsrPlugin\Repository\ProductSafetyRepositoryInterface;

/**
 * Adds an unmapped "productSafety" sub-form to the product form: the product entity is left
 * untouched, the ProductSafety (owning side of the relation) is loaded before the form is
 * populated and persisted once the product form is valid. The flush is left to the controller.
 */
final readonly class ProductSafetyFormSubscriber implements EventSubscriberInterface
{
    public const FIELD = 'productSafety';

    /**
     * @param FactoryInterface<ProductSafetyInterface> $productSafetyFactory
     * @param class-string $formType
     */
    public function __construct(
        private ProductSafetyRepositoryInterface $productSafetyRepository,
        private FactoryInterface $productSafetyFactory,
        private ObjectManager $productSafetyManager,
        private string $formType,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'addProductSafetyField',
            // After the validation listener (priority 0), so that the form validity is known.
            FormEvents::POST_SUBMIT => ['persistProductSafety', -100],
        ];
    }

    public function addProductSafetyField(FormEvent $event): void
    {
        $product = $event->getData();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $event->getForm()->add(self::FIELD, $this->formType, [
            'mapped' => false,
            'label' => false,
            'required' => false,
            'data' => $this->findOrCreate($product),
            'constraints' => [new Valid(groups: ['sylius'])],
        ]);
    }

    public function persistProductSafety(FormEvent $event): void
    {
        $form = $event->getForm();
        $product = $form->getData();
        if (!$product instanceof ProductInterface || !$form->has(self::FIELD) || !$form->isValid()) {
            return;
        }

        $productSafety = $form->get(self::FIELD)->getData();
        if (!$productSafety instanceof ProductSafetyInterface) {
            return;
        }

        $productSafety->setProduct($product);

        // Do not create empty records; existing ones are managed and flushed with the product.
        if (null === $productSafety->getId() && $productSafety->isEmpty()) {
            return;
        }

        $this->productSafetyManager->persist($productSafety);
    }

    private function findOrCreate(ProductInterface $product): ProductSafetyInterface
    {
        $productSafety = $this->productSafetyRepository->findOneByProduct($product);
        if (!$productSafety instanceof \Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface) {
            $productSafety = $this->productSafetyFactory->createNew();
            $productSafety->setProduct($product);
        }

        return $productSafety;
    }
}
