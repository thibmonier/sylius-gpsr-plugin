<?php

declare(strict_types=1);

namespace Tmonier\SyliusGpsrPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Written with the Schema API (not raw SQL) so that it runs on both MySQL and PostgreSQL.
 */
final class Version20261010160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create GPSR tables (tmonier/sylius-gpsr-plugin).';
    }

    public function up(Schema $schema): void
    {
        $person = $schema->createTable('tmonier_gpsr_responsible_person');
        $person->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $person->addColumn('code', Types::STRING, ['length' => 255]);
        $person->addColumn('name', Types::STRING, ['length' => 255]);
        $person->addColumn('role', Types::STRING, ['length' => 64]);
        $person->addColumn('street', Types::STRING, ['length' => 255]);
        $person->addColumn('postcode', Types::STRING, ['length' => 32]);
        $person->addColumn('city', Types::STRING, ['length' => 255]);
        $person->addColumn('country_code', Types::STRING, ['length' => 2]);
        $person->addColumn('email', Types::STRING, ['length' => 255]);
        $person->addColumn('phone', Types::STRING, ['length' => 64, 'notnull' => false]);
        $person->setPrimaryKey(['id']);
        $person->addUniqueIndex(['code'], 'UNIQ_C1B9B40C77153098');

        $manufacturer = $schema->createTable('tmonier_gpsr_manufacturer');
        $manufacturer->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $manufacturer->addColumn('default_responsible_person_id', Types::INTEGER, ['notnull' => false]);
        $manufacturer->addColumn('code', Types::STRING, ['length' => 255]);
        $manufacturer->addColumn('name', Types::STRING, ['length' => 255]);
        $manufacturer->addColumn('trade_mark', Types::STRING, ['length' => 255, 'notnull' => false]);
        $manufacturer->addColumn('street', Types::STRING, ['length' => 255]);
        $manufacturer->addColumn('postcode', Types::STRING, ['length' => 32]);
        $manufacturer->addColumn('city', Types::STRING, ['length' => 255]);
        $manufacturer->addColumn('country_code', Types::STRING, ['length' => 2]);
        $manufacturer->addColumn('email', Types::STRING, ['length' => 255]);
        $manufacturer->addColumn('website', Types::STRING, ['length' => 255, 'notnull' => false]);
        $manufacturer->setPrimaryKey(['id']);
        $manufacturer->addUniqueIndex(['code'], 'UNIQ_10BE061077153098');
        $manufacturer->addIndex(['default_responsible_person_id'], 'IDX_10BE0610D7A94552');
        $manufacturer->addForeignKeyConstraint('tmonier_gpsr_responsible_person', ['default_responsible_person_id'], ['id'], ['onDelete' => 'SET NULL'], 'FK_10BE0610D7A94552');

        $safety = $schema->createTable('tmonier_gpsr_product_safety');
        $safety->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $safety->addColumn('product_id', Types::INTEGER);
        $safety->addColumn('manufacturer_id', Types::INTEGER, ['notnull' => false]);
        $safety->addColumn('responsible_person_id', Types::INTEGER, ['notnull' => false]);
        $safety->addColumn('type_designation', Types::STRING, ['length' => 255, 'notnull' => false]);
        $safety->addColumn('other_identifier', Types::STRING, ['length' => 255, 'notnull' => false]);
        $safety->setPrimaryKey(['id']);
        $safety->addUniqueIndex(['product_id'], 'UNIQ_FC442654584665A');
        $safety->addIndex(['manufacturer_id'], 'IDX_FC44265A23B42D');
        $safety->addIndex(['responsible_person_id'], 'IDX_FC44265EF64F467');
        $safety->addForeignKeyConstraint('sylius_product', ['product_id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_FC442654584665A');
        $safety->addForeignKeyConstraint('tmonier_gpsr_manufacturer', ['manufacturer_id'], ['id'], ['onDelete' => 'SET NULL'], 'FK_FC44265A23B42D');
        $safety->addForeignKeyConstraint('tmonier_gpsr_responsible_person', ['responsible_person_id'], ['id'], ['onDelete' => 'SET NULL'], 'FK_FC44265EF64F467');

        $translation = $schema->createTable('tmonier_gpsr_product_safety_translation');
        $translation->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $translation->addColumn('translatable_id', Types::INTEGER);
        $translation->addColumn('locale', Types::STRING, ['length' => 255]);
        $translation->addColumn('warnings', Types::TEXT, ['notnull' => false]);
        $translation->addColumn('safety_information', Types::TEXT, ['notnull' => false]);
        $translation->setPrimaryKey(['id']);
        $translation->addIndex(['translatable_id'], 'IDX_7AEC2DAD2C2AC5D3');
        $translation->addUniqueIndex(['translatable_id', 'locale'], 'tmonier_gpsr_product_safety_translation_uniq_trans');
        $translation->addForeignKeyConstraint('tmonier_gpsr_product_safety', ['translatable_id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_7AEC2DAD2C2AC5D3');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('tmonier_gpsr_product_safety_translation');
        $schema->dropTable('tmonier_gpsr_product_safety');
        $schema->dropTable('tmonier_gpsr_manufacturer');
        $schema->dropTable('tmonier_gpsr_responsible_person');
    }
}
