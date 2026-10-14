# Audit qualité — sylius-gpsr-plugin

> Généré le 2026-10-14 · Branche `chore/quality-tooling` · Revue qualitative par l'orchestrateur (lecture directe des sources). Outillage ECS, PHPStan, Rector, PHPUnit exécuté via Docker `ghcr.io/sylius/sylius-php:8.4-alpine`.

## 1. Audit factuel (outils)

| Contrôle | Commande | Résultat |
|---|---|---|
| Style (ECS) | `vendor/bin/ecs check` | ✅ 0 erreur |
| Analyse statique (PHPStan **level max**) | `vendor/bin/phpstan analyse` | ✅ 0 erreur |
| Tests unitaires | `vendor/bin/phpunit --testsuite=unit` | ✅ 56 tests, 593 assertions |
| Validation composer | `composer validate --strict` | ✅ valide |
| Modernisation (Rector, dry-run) | `vendor/bin/rector process --dry-run` | ⚠️ 11 fichiers avec suggestions |
| Tests `non-unit` (functional) | — | ⏭️ non exécutés localement (DB + app Sylius requise) — couverts par le CI |
| Vulnérabilités dépendances | `composer audit` | ⚠️ 2 paquets abandonnés (transitifs) |

**Rector** vient d'être ajouté (`rector/rector ^2.0`, `rector.php`, cibles `make rector` / `make rector-fix`). Les 11 fichiers signalés relèvent de `FlipTypeControlToUseExclusiveTypeRector` et `ReadOnlyClassRector` — suggestions de modernisation non bloquantes, non appliquées. Lancer `make rector-fix` puis relancer ECS/PHPStan/tests pour les intégrer.

**Paquets abandonnés** : `behat/transliterator` et `php-http/message-factory` sont des dépendances transitives de Sylius — non actionnables au niveau du plugin.

**Harmonisation `ecs.php`** : le chemin `tests/Integration` était absent de la liste `paths` alors que le dossier existe. Aligné sur le modèle de référence.

## 2. Audit qualitatif (revue par l'orchestrateur)

| Dimension | Score | Synthèse |
|---|---|---|
| PHP / Clean Archi / SOLID | **8/10** | Typage strict, `final` sur les classes de service, `ProductSafetyView` immutable (readonly), séparation entité/read-model propre. |
| Intégration Symfony / Sylius | **7/10** | `AbstractResourceExtension` + `PrependExtensionInterface` bien utilisés, form subscriber avec priorité correcte, filtre de grille documenté. Aucun test d'intégration. |
| Sécurité (OWASP 2025) | **8/10** | Requêtes paramétrées, validation contrainte sur tous les champs, `warnings|nl2br` auto-échappé par Twig, URL protégée par `@Assert\Url(protocols: ['http','https'])`. |

### Points forts

- `declare(strict_types=1)` sur tous les fichiers ; `final` sur `ProductSafetyResolver`, `GpsrDataMissingFilter`, `ProductSafetyView`, `TmonierSyliusGpsrPlugin`, `EuMemberStates`, `ProductTypeExtension`, `ProductSafetyExtension`.
- `ProductSafetyView` est un read-model immuable (toutes les propriétés `readonly`) — CQRS-lite correct.
- `EuMemberStates` : classe utilitaire bien conçue (constructeur privé, liste EU-27 stricte, `contains()` paramétrique).
- `ProductSafetyResolver::translate()` lit la collection de traductions directement (évite la création d'entrées vides par `TranslatableTrait::getTranslation()`).
- `ProductSafetyFormSubscriber` : priorité `-100` sur `POST_SUBMIT` (après la validation), `isEmpty()` empêche la création d'enregistrements vides.
- Sécurité XSS shop : `warnings|nl2br` et `safetyInformation|nl2br` bénéficient de l'auto-escaping Twig (échappement HTML avant conversion `\n` → `<br>`). Le champ `website` est validé `@Assert\Url(protocols: ['http', 'https'])` — protocole `javascript:` impossible.
- `GpsrDataMissingFilter` : classe DQL injectée via DI (pas d'entrée utilisateur), `setParameter()` pour les codes EU — aucun risque d'injection.

## 3. Recommandations priorisées

### 🟠 Majeur

1. **DRY — méthode `normalize()` dupliquée** (`src/Entity/ProductSafety.php:148-153` et `src/Entity/ProductSafetyTranslation.php:48-53`). La même logique `trim` + `null` sur chaîne vide est copiée dans deux classes. → Extraire dans un trait `NormalizableTrait` ou une classe statique interne partagée.

2. **Absence de tests d'intégration** (`tests/Integration/` : dossier vide). Les comportements clés — résolution de `ProductSafetyView` depuis la base de données, persistence du formulaire admin, déclenchement du filtre de grille — ne sont couverts que par les tests fonctionnels (application complète). Un test d'intégration `ProductSafetyRepositoryTest` vérifierait `findOneByProduct()` sans charger toute la stack Sylius.

3. **`ProductSafetyFormSubscriber` : ProductSafety crée avec `product` avant soumission** (`src/Form/EventSubscriber/ProductSafetyFormSubscriber.php:58`). Sur `PRE_SET_DATA`, un nouveau `ProductSafety` est instancié et son `product` est assigné. Si le formulaire est annulé ou non persisté pour une autre raison, cet objet détaché portant une référence au produit peut induire des effets de bord dans des listeners Doctrine. → Déférer l'assignation du `product` au `POST_SUBMIT`.

### 🟡 Mineur

- **Normalisation incohérente dans `AddressableTrait`** (`src/Entity/AddressableTrait.php:55,65...`). `setName()`, `setEmail()`, `setStreet()`, etc. ne `trim()` pas l'entrée, contrairement à `ProductSafety::normalize()`. Un espace de fin dans `code` ne déclenche pas le `UniqueEntity` si la base de données n'est pas configurée en mode strict (`TRIM_SPACE`). → Appliquer `trim()` dans les setters du trait, au minimum sur `code` et `email`.

- **`ProductSafetyRepository` non `final`** (`src/Repository/ProductSafetyRepository.php:11`). La classe est extensible par défaut. Sylius permet la surcharge par configuration, mais documenter l'intention (finale sauf surcharge intentionnelle) serait utile.

- **`GpsrDataMissingFilter::apply()` : complexité DQL** (`src/Grid/Filter/GpsrDataMissingFilter.php:40-42`). La sous-requête EXISTS imbriquée avec trois aliases concaténés est correcte mais difficile à maintenir. → Extraire la construction DQL dans une méthode privée nommée `buildExistsExpression()`.

- **`Manufacturer::isMissingResponsiblePerson()` non utilisé** en dehors des templates via `hookable_metadata` — vérifier si ce helper est nécessaire ou peut être délégué au `ProductSafetyView`.

- Appliquer les 11 suggestions Rector (`make rector-fix`) — principalement `ReadOnlyClassRector` sur les value objects et `FlipTypeControlToUseExclusiveTypeRector`.

### ℹ️ Pour information (choix de conception assumés)

- `ProductSafety` et `ProductSafetyTranslation` non `final` : requis par le mécanisme de surcharge de ressource Sylius (`tmonier_gpsr.model.product_safety.class`).
- EU-27 strict (EEA/EFTA exclus) : décision légale documentée dans `EuMemberStates` et `CLAUDE.md`.
- `warnings` et `safetyInformation` sont du texte brut (pas de HTML riche) : l'absence de sanitiseur HTML (HTMLPurifier) est cohérente avec `nl2br` et l'auto-escaping Twig.
- Données personnelles fabricant/responsable (email, adresse) : pas de mécanisme RGPD de suppression dans le plugin — responsabilité déléguée à l'intégrateur.

## 4. Verdict

Plugin **bien structuré et outillage au vert** : ECS ✅, PHPStan level max ✅, 56 tests unitaires ✅, composer valid ✅. Les mentions GPSR (warnings, safetyInformation, URL fabricant) sont correctement protégées contre le XSS par l'auto-escaping Twig et la validation Symfony des URLs. Les axes d'amélioration prioritaires sont la **suppression de la duplication `normalize()`** et l'**ajout de tests d'intégration** sur le repository. Le finding sur le formulaire (`product` assigné trop tôt) mérite correction avant release pour éviter des effets Doctrine imprévus sur des stacks avec Doctrine listeners actifs.
