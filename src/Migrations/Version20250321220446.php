<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321220446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE line_item DROP FOREIGN KEY FK_9456D6C7E415FB15');
        $this->addSql('DROP INDEX IDX_9456D6C7E415FB15 ON line_item');
        $this->addSql('ALTER TABLE line_item CHANGE order_item_id order_id INT NOT NULL');
        $this->addSql('ALTER TABLE line_item ADD CONSTRAINT FK_9456D6C78D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('CREATE INDEX IDX_9456D6C78D9F6D38 ON line_item (order_id)');
        $this->addSql('ALTER TABLE `order` ADD status INT NOT NULL, ADD note2 LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `order` DROP status, DROP note2');
        $this->addSql('ALTER TABLE line_item DROP FOREIGN KEY FK_9456D6C78D9F6D38');
        $this->addSql('DROP INDEX IDX_9456D6C78D9F6D38 ON line_item');
        $this->addSql('ALTER TABLE line_item CHANGE order_id order_item_id INT NOT NULL');
        $this->addSql('ALTER TABLE line_item ADD CONSTRAINT FK_9456D6C7E415FB15 FOREIGN KEY (order_item_id) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_9456D6C7E415FB15 ON line_item (order_item_id)');
    }
}
