# Functional specification — GPSR product safety information

Implements the information that online offers must display under **Article 19 of
Regulation (EU) 2023/988** on general product safety (GPSR), applicable since
**13 December 2024**.

Legal source: https://eur-lex.europa.eu/legal-content/EN/TXT/HTML/?uri=CELEX:32023R0988

> This plugin is a technical tool. It does not constitute legal advice; merchants remain
> responsible for the accuracy of the data they enter and for their own compliance
> (product labelling, risk assessment, recalls, sector-specific legislation).

## 1. Legal requirements → plugin features

Art. 19: economic operators selling online must indicate **in the offer**, clearly and visibly,
at least the following information.

| Art. 19 | Requirement | Plugin |
|---|---|---|
| (a) | Name, registered trade name or registered trade mark of the **manufacturer**, and the **postal and electronic address** at which it can be contacted | `Manufacturer` resource (name, trade mark, street, postcode, city, country, e-mail, website). Shown in the *Product safety* block of the product page. |
| (b) | Where the manufacturer is **not established in the Union**: name, postal and electronic address of the **responsible person** (Art. 16(1) GPSR / Art. 4(1) Reg. (EU) 2019/1020: importer, authorised representative or fulfilment service provider) | `ResponsiblePerson` resource with role; default per manufacturer, override per product. The EU check uses the EU-27 country list. Admin warning (manufacturer form and grid, product tab) and a *GPSR data missing* product filter when none is set. |
| (c) | Information allowing the **identification of the product**: picture, type and any other product identifier | Picture: standard Sylius product images. `typeDesignation` (type / model / reference) and `otherIdentifier` (e.g. GTIN/EAN) per product. |
| (d) | **Warnings or safety information** to be affixed on or accompany the product, in a language easily understood by consumers | Translatable `warnings` and `safetyInformation` per product, shown in the current locale with fallback to the default locale. |
| — | Clearly and visibly, in the offer | Rendered on the product page itself (below the description/attributes accordion), not behind a link or a collapsed panel. |

Not required online and not handled: batch or serial numbers (they identify a physical item,
not the offer).

## 2. Data model

| Entity | Table | Notes |
|---|---|---|
| `ResponsiblePerson` | `tmonier_gpsr_responsible_person` | code (unique), name, role (`importer`, `authorised_representative`, `fulfilment_service_provider`), street, postcode, city, country (must be EU-27), e-mail (required), phone |
| `Manufacturer` | `tmonier_gpsr_manufacturer` | code (unique), name, trade mark, street, postcode, city, country (ISO alpha-2), e-mail (required), website, default responsible person (`ON DELETE SET NULL`) |
| `ProductSafety` | `tmonier_gpsr_product_safety` | one-to-one with the product, **owning side in the plugin** (unique `product_id`, `ON DELETE CASCADE`): the application's Product entity is not modified. manufacturer, responsible person override, type designation, other identifier |
| `ProductSafetyTranslation` | `tmonier_gpsr_product_safety_translation` | `warnings`, `safety_information` (text) per locale |

All entities are Sylius resources mapped as mapped superclasses (XML, `config/doctrine`), so
they can be extended through `tmonier_sylius_gpsr.resources.*.classes`.

"Established in the EU" = country in the 27 Member States
(`Tmonier\SyliusGpsrPlugin\Model\EuMemberStates`). EEA EFTA states (IS, LI, NO) are **not**
treated as EU: a manufacturer there is flagged as needing a responsible person, and a
responsible person there is rejected. Adapt the list if your legal analysis differs.

## 3. Back office

- *Catalog → Manufacturers* and *Catalog → Responsible persons*: grids (search, country with
  EU badge), create / update / delete.
- A manufacturer established outside the EU without a default responsible person **is saved**
  but shows a warning on its edit page and a *Missing* badge in the grid.
- A responsible person outside the EU is **rejected** (validation error).
- Product create / edit form: *Product safety* tab with manufacturer, responsible person
  override, type, identifier and per-locale warnings / safety information. Implemented with a
  form type extension on `Sylius\Bundle\AdminBundle\Form\Type\ProductType` adding an unmapped
  `productSafety` sub-form; an event subscriber loads the record on `PRE_SET_DATA` and
  persists it on `POST_SUBMIT` when the product form is valid (no empty records are created).
- Products grid: filter *GPSR data missing* (yes / no). Missing = no record, no manufacturer,
  or a non-EU manufacturer without any responsible person. Requires the Doctrine ORM grid driver.

## 4. Shop

The product page (`sylius_shop.product.show.content.info.overview` hook) shows a *Product
safety* section when at least one value is set:

- manufacturer: name, trade mark, postal address, country, e-mail (mailto), website;
- responsible person in the EU (with role) whenever one is resolved: product override, else
  manufacturer default. It is shown even for EU manufacturers when set;
- type / model and other identifier;
- warnings and safety information in the current locale, falling back to the default locale
  **per field**.

Nothing is rendered when the product has no GPSR data. The read model is exposed as
`ProductSafetyResolverInterface::resolve(product, ?locale)` and the Twig function
`tmonier_gpsr_product_safety(product)`.

## 5. Out of scope for v1

- Per-variant data (the GPSR information is per product).
- Import/export, API Platform endpoints.
- Online marketplace obligations (Art. 22) and recall notices (Art. 36).
- Showing the block in other places (cart, product listing).
