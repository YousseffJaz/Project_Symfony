<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320111956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Suppression de la table reseller et de la colonne reseller_id';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64991E6A19D');
        $this->addSql('ALTER TABLE user DROP reseller_id');
        $this->addSql('DROP TABLE reseller');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE reseller (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, company_address VARCHAR(255) NOT NULL, zipcode VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, longitude DOUBLE PRECISION DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, snap VARCHAR(255) DEFAULT NULL, position INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD reseller_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64991E6A19D FOREIGN KEY (reseller_id) REFERENCES reseller (id)');
    }
}
