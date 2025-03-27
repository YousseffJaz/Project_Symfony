<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250327093807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE variant ADD price DOUBLE PRECISION DEFAULT 0.0
        SQL);

        // Copier les prix depuis la table price_list vers variant
        $this->addSql(<<<'SQL'
            UPDATE variant v 
            SET price = pl.price 
            FROM price_list pl 
            WHERE v.id = pl.variant_id
        SQL);

        // Ajouter la contrainte NOT NULL
        $this->addSql(<<<'SQL'
            ALTER TABLE variant ALTER COLUMN price SET NOT NULL
        SQL);

        // Supprimer la table price_list
        $this->addSql(<<<'SQL'
            DROP SEQUENCE price_list_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE price_list DROP CONSTRAINT fk_399a0aa23b69a9af
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE price_list
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE price_list_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE price_list (id INT NOT NULL, variant_id INT NOT NULL, title VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_399a0aa23b69a9af ON price_list (variant_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE price_list ADD CONSTRAINT fk_399a0aa23b69a9af FOREIGN KEY (variant_id) REFERENCES variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE variant DROP price
        SQL);
    }
}
