<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Resolver;

use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Locale\Context\LocaleNotFoundException;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Resource\Translation\Provider\TranslationLocaleProviderInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyInterface;
use Tmonier\SyliusGpsrPlugin\Entity\ProductSafetyTranslationInterface;
use Tmonier\SyliusGpsrPlugin\Model\ProductSafetyView;
use Tmonier\SyliusGpsrPlugin\Repository\ProductSafetyRepositoryInterface;

final class ProductSafetyResolver implements ProductSafetyResolverInterface
{
    public function __construct(
        private readonly ProductSafetyRepositoryInterface $productSafetyRepository,
        private readonly LocaleContextInterface $localeContext,
        private readonly TranslationLocaleProviderInterface $translationLocaleProvider,
    ) {
    }

    public function resolve(ProductInterface $product, ?string $localeCode = null): ?ProductSafetyView
    {
        $productSafety = $this->productSafetyRepository->findOneByProduct($product);
        if (null === $productSafety) {
            return null;
        }

        $localeCode ??= $this->getCurrentLocaleCode();
        $fallbackLocaleCode = $this->translationLocaleProvider->getDefaultLocaleCode();

        $manufacturer = $productSafety->getManufacturer();

        $view = new ProductSafetyView(
            $manufacturer,
            $productSafety->getResponsiblePerson() ?? $manufacturer?->getDefaultResponsiblePerson(),
            null !== $manufacturer && $manufacturer->requiresResponsiblePerson(),
            $productSafety->getTypeDesignation(),
            $productSafety->getOtherIdentifier(),
            $this->translate($productSafety, $localeCode, $fallbackLocaleCode, static fn (ProductSafetyTranslationInterface $translation): ?string => $translation->getWarnings()),
            $this->translate($productSafety, $localeCode, $fallbackLocaleCode, static fn (ProductSafetyTranslationInterface $translation): ?string => $translation->getSafetyInformation()),
        );

        return $view->isEmpty() ? null : $view;
    }

    /**
     * Reads the translations collection directly: TranslatableTrait::getTranslation() would
     * create (and attach) an empty translation when none exists.
     *
     * @param callable(ProductSafetyTranslationInterface): ?string $getter
     */
    private function translate(ProductSafetyInterface $productSafety, ?string $localeCode, string $fallbackLocaleCode, callable $getter): ?string
    {
        foreach (array_unique(array_filter([$localeCode, $fallbackLocaleCode])) as $code) {
            $translation = $productSafety->getTranslations()->get($code);
            if ($translation instanceof ProductSafetyTranslationInterface && null !== $value = $getter($translation)) {
                return $value;
            }
        }

        return null;
    }

    private function getCurrentLocaleCode(): ?string
    {
        try {
            return $this->localeContext->getLocaleCode();
        } catch (LocaleNotFoundException) {
            return null;
        }
    }
}
