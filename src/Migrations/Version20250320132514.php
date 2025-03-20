<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320132514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F162CB942');
        $this->addSql('DROP TABLE folder');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F2989F1FD');
        $this->addSql('DROP INDEX IDX_17BDE61F2989F1FD ON upload');
        $this->addSql('DROP INDEX IDX_17BDE61F162CB942 ON upload');
        $this->addSql('ALTER TABLE upload ADD order_id INT DEFAULT NULL, DROP invoice_id, DROP folder_id');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('CREATE INDEX IDX_17BDE61F8D9F6D38 ON upload (order_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE folder (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F8D9F6D38');
        $this->addSql('DROP INDEX IDX_17BDE61F8D9F6D38 ON upload');
        $this->addSql('ALTER TABLE upload ADD folder_id INT DEFAULT NULL, CHANGE order_id invoice_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F162CB942 FOREIGN KEY (folder_id) REFERENCES folder (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F2989F1FD FOREIGN KEY (invoice_id) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_17BDE61F2989F1FD ON upload (invoice_id)');
        $this->addSql('CREATE INDEX IDX_17BDE61F162CB942 ON upload (folder_id)');
    }
}
