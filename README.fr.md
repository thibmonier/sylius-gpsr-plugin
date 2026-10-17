# Sylius GPSR Plugin

> English: [README.md](README.md) <!-- vitrine:lang -->

[![Build](https://github.com/thibmonier/sylius-gpsr-plugin/actions/workflows/build.yaml/badge.svg)](https://github.com/thibmonier/sylius-gpsr-plugin/actions/workflows/build.yaml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

**Les informations du règlement européen relatif à la sécurité générale des produits (GPSR) sur les pages produit de votre boutique Sylius 2.**

Depuis le **13 décembre 2024**, toute boutique en ligne qui vend des produits de consommation
dans l'Union européenne doit afficher, dans chaque offre de produit, le fabricant, la personne
responsable dans l'UE lorsque le fabricant est établi hors de l'Union, l'identification du
produit et les avertissements ou informations de sécurité
([article 19 du règlement (UE) 2023/988][gpsr]).

Ce plugin ajoute ces données à Sylius 2.2+ et les affiche sur la page produit, gratuitement et en
open source.

## Fonctionnalités

| Exigence (art. 19 GPSR) | Ce que fait le plugin |
|---|---|
| (a) Nom du fabricant, raison sociale ou marque déposée, adresse postale et électronique | Back-office *Fabricants* (nom, marque, adresse, pays, e-mail, site web), lié aux produits |
| (b) Personne responsable dans l'UE lorsque le fabricant n'est pas établi dans l'Union | Back-office *Personnes responsables* (importateur, mandataire ou prestataire de services d'exécution des commandes, adresse dans l'UE obligatoire) ; valeur par défaut par fabricant, remplaçable par produit ; avertissements en cas d'absence |
| (c) Identification du produit : image, type, autre identifiant | Images de produit de Sylius + *type / modèle* et *autre identifiant* (par exemple GTIN/EAN) par produit |
| (d) Avertissements et informations de sécurité dans la langue du consommateur | *Avertissements* et *informations de sécurité* traduisibles par produit, avec repli sur la locale par défaut |
| De manière claire et visible, dans l'offre | Section *Sécurité du produit* affichée sur la page produit elle-même, sans clic nécessaire ; rien n'est affiché lorsqu'un produit n'a pas de données |

Sont également inclus :

- **Onglet dans le formulaire produit** : un onglet *Sécurité du produit* sur les pages de
  création et de modification d'un produit dans l'administration. Votre entité `Product` n'est
  **pas** modifiée (le plugin possède la relation un-à-un).
- **Aides à la conformité** : badges *Hors UE*, un avertissement pour les fabricants hors UE sans
  personne responsable (l'enregistrement n'est jamais bloqué), et un **filtre « Données RSGP
  manquantes »** sur la grille des produits de l'administration.
- **Traductions** en anglais, français, allemand, espagnol, italien, néerlandais et polonais.
- **Fixtures de démonstration** (`tmonier_gpsr_responsible_person`, `tmonier_gpsr_manufacturer`,
  `tmonier_gpsr_product_safety`).
- **Personnalisable** via les Twig Hooks et la surcharge des classes de ressources ; prise en
  charge de MySQL, MariaDB et PostgreSQL.

## Captures d'écran

![Bloc de sécurité du produit sur la page produit](docs/images/shop-product-safety.png)

![Onglet Sécurité du produit dans le formulaire produit de l'administration](docs/images/admin-product-safety.png)

## Prérequis

| | Version |
|---|---|
| PHP | 8.2+ |
| Sylius | 2.2, 2.3 |
| Symfony | 6.4, 7.4, 8.0 |

## Installation

1. Installez le paquet :

    ```bash
    composer require tmonier/sylius-gpsr-plugin
    ```

2. Enregistrez le bundle dans `config/bundles.php` (Symfony Flex le fait automatiquement dès qu'une recette existe) :

    ```php
    Tmonier\SyliusGpsrPlugin\TmonierSyliusGpsrPlugin::class => ['all' => true],
    ```

3. Importez la configuration dans `config/packages/tmonier_sylius_gpsr.yaml` :

    ```yaml
    imports:
        - { resource: "@TmonierSyliusGpsrPlugin/config/config.yaml" }
    ```

4. Importez les routes d'administration dans `config/routes/tmonier_sylius_gpsr.yaml` :

    ```yaml
    tmonier_sylius_gpsr:
        resource: "@TmonierSyliusGpsrPlugin/config/routes.yaml"
    ```

5. Exécutez les migrations :

    ```bash
    bin/console doctrine:migrations:migrate
    ```

6. Dans le back-office, créez vos personnes responsables et vos fabricants sous
   **Catalogue → Personnes responsables / Fabricants**, puis remplissez l'onglet **Sécurité du produit** de
   chaque produit. Utilisez le filtre **Données RSGP manquantes** de la grille des produits pour repérer les manques.

## Configuration

Il n'y a rien à configurer. Chaque ressource peut être étendue comme n'importe quelle ressource Sylius :

```yaml
# config/packages/tmonier_sylius_gpsr.yaml
tmonier_sylius_gpsr:
    resources:
        manufacturer:
            classes:
                model: App\Entity\Gpsr\Manufacturer          # extends Tmonier\SyliusGpsrPlugin\Entity\Manufacturer
        responsible_person:
            classes:
                model: App\Entity\Gpsr\ResponsiblePerson
        product_safety:
            classes:
                model: App\Entity\Gpsr\ProductSafety
            translation:
                classes:
                    model: App\Entity\Gpsr\ProductSafetyTranslation
```

« Établi dans l'UE » désigne l'un des 27 États membres
(`Tmonier\SyliusGpsrPlugin\Model\EuMemberStates`). L'Islande, le Liechtenstein et la Norvège
(EEE) ne sont volontairement pas inclus.

### Données de démonstration

```bash
bin/console sylius:fixtures:load tmonier_gpsr -n
```

avec une suite comme celle de [`tests/TestApplication/config/config.yaml`](tests/TestApplication/config/config.yaml).

## Personnalisation

Les parties de la boutique et de l'administration sont construites avec les [Twig Hooks](https://stack.sylius.com/twig-hooks/getting-started) :

| Hook | Contenu |
|---|---|
| `sylius_shop.product.show.content.info.overview` → `tmonier_gpsr_product_safety` | Section *Sécurité du produit* de la page produit |
| `sylius_admin.product.{create,update}.content.form.side_navigation` → `tmonier_gpsr_product_safety` | Bouton de l'onglet *Sécurité du produit* |
| `sylius_admin.product.{create,update}.content.form.sections` → `tmonier_gpsr_product_safety` | Contenu de l'onglet *Sécurité du produit* |
| `tmonier_gpsr.admin.manufacturer.{create,update}.content.form.sections.general` | Formulaire du fabricant |
| `tmonier_gpsr.admin.manufacturer.update.content` → `responsible_person_warning` | Avertissement de personne responsable manquante |
| `tmonier_gpsr.admin.responsible_person.{create,update}.content.form.sections.general` | Formulaire de la personne responsable |

Les gabarits peuvent être surchargés dans `templates/bundles/TmonierSyliusGpsrPlugin/`. Pour
afficher les données ailleurs (panier, PDF, flux), utilisez la fonction Twig ou le service de résolution :

```twig
{% set safety = tmonier_gpsr_product_safety(product) %}
{% if safety is not null and safety.manufacturer is not null %}
    {{ safety.manufacturer.name }} – {{ safety.manufacturer.email }}
{% endif %}
```

```php
public function __construct(private ProductSafetyResolverInterface $resolver) {}

$view = $this->resolver->resolve($product, 'de_DE'); // ?ProductSafetyView
```

> [!IMPORTANT]
> Si vous déplacez ou restylez la section de sécurité du produit, gardez-la **sur la page produit
> elle-même et visible sans interaction** : l'art. 19 exige que les informations soient
> affichées de manière claire et visible dans l'offre.

## Ce qu'il vous reste à faire

Le plugin affiche les informations que vous saisissez. En tant qu'opérateur économique, vous
restez responsable de :

- la saisie de données exactes sur le fabricant et la personne responsable, ainsi que
  d'**avertissements et d'informations de sécurité dans les langues des États membres dans
  lesquels vous vendez** ;
- le produit lui-même : étiquetage sur le produit ou l'emballage, notices, documentation
  technique, analyse de risques interne, traçabilité (numéros de lot / de série) ;
- les incidents de sécurité des produits, les rappels et les notifications via le Safety Business
  Gateway ;
- les règles sectorielles (jouets, cosmétiques, équipements électriques, etc.) qui peuvent exiger
  davantage d'informations en ligne ;
- la vérification des produits créés ou importés hors du formulaire d'administration (API,
  imports) : le filtre *Données RSGP manquantes* aide à le faire.

Ce plugin ne constitue pas un conseil juridique.

## Développement

```bash
docker compose up -d mysql   # or your own MySQL
composer install
(cd vendor/sylius/test-application && yarn install && yarn build)
vendor/bin/console doctrine:database:create
vendor/bin/console doctrine:migrations:migrate -n
vendor/bin/console sylius:fixtures:load -n
vendor/bin/console sylius:fixtures:load tmonier_gpsr -n

vendor/bin/phpunit --testsuite unit
APP_ENV=test vendor/bin/phpunit --testsuite non-unit   # needs a test database with fixtures
vendor/bin/phpstan analyse
vendor/bin/ecs check
```

<!-- vitrine:demo-b2b:start -->
## Démo B2B

Ce plugin est utilisé dans la [démo B2B Sylius](https://github.com/thibmonier/sylius-b2b-demo) : une boutique Sylius 2.3 avec des données d'exemple fictives, où les plugins B2B fonctionnent ensemble. Le code source, l'installation et les fixtures de la démo sont dans ce dépôt.

La démo est un exemple, pas une configuration de production.
<!-- vitrine:demo-b2b:end -->

## Licence

MIT. Voir [LICENSE](LICENSE).

[gpsr]: https://eur-lex.europa.eu/legal-content/EN/TXT/HTML/?uri=CELEX:32023R0988
