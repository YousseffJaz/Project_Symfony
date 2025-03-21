<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321214325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398DFFECA0E');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D126ED0855');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP INDEX IDX_F5299398DFFECA0E ON `order`');
        $this->addSql('ALTER TABLE `order` DROP note2_id');
        $this->addSql('ALTER TABLE transaction DROP INDEX UNIQ_723705D12989F1FD, ADD INDEX IDX_723705D12989F1FD (invoice_id)');
        $this->addSql('DROP INDEX IDX_723705D126ED0855 ON transaction');
        $this->addSql('ALTER TABLE transaction DROP note_id, DROP updated_at');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, amount DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE `order` ADD note2_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398DFFECA0E FOREIGN KEY (note2_id) REFERENCES note (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F5299398DFFECA0E ON `order` (note2_id)');
        $this->addSql('ALTER TABLE transaction DROP INDEX IDX_723705D12989F1FD, ADD UNIQUE INDEX UNIQ_723705D12989F1FD (invoice_id)');
        $this->addSql('ALTER TABLE transaction ADD note_id INT NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D126ED0855 FOREIGN KEY (note_id) REFERENCES note (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_723705D126ED0855 ON transaction (note_id)');
    }
}
