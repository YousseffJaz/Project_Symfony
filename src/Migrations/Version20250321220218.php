<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250321220218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F8D9F6D38');
        $this->addSql('DROP TABLE upload');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939812136921');
        $this->addSql('DROP INDEX IDX_F529939812136921 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP delivery_id, DROP identifier, DROP status, DROP shopify_note, DROP option1, DROP option2, DROP option3, DROP option4, DROP option5, DROP option6, DROP shopify_id, DROP shopify_order_id, CHANGE admin_id admin_id INT DEFAULT NULL, CHANGE firstname firstname VARCHAR(255) NOT NULL, CHANGE lastname lastname VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE payment_type payment_type VARCHAR(255) NOT NULL, CHANGE payment_method payment_method VARCHAR(255) NOT NULL, CHANGE address address LONGTEXT NOT NULL, CHANGE order_status order_status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE upload (id INT AUTO_INCREMENT NOT NULL, order_id INT DEFAULT NULL, filename VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_17BDE61F8D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE `order` ADD delivery_id INT DEFAULT NULL, ADD identifier VARCHAR(255) DEFAULT NULL, ADD status INT DEFAULT NULL, ADD shopify_note LONGTEXT DEFAULT NULL, ADD option1 TINYINT(1) DEFAULT NULL, ADD option2 TINYINT(1) DEFAULT NULL, ADD option3 TINYINT(1) DEFAULT NULL, ADD option4 TINYINT(1) DEFAULT NULL, ADD option5 TINYINT(1) DEFAULT NULL, ADD option6 TINYINT(1) DEFAULT NULL, ADD shopify_id VARCHAR(255) DEFAULT NULL, ADD shopify_order_id VARCHAR(255) DEFAULT NULL, CHANGE admin_id admin_id INT NOT NULL, CHANGE firstname firstname VARCHAR(255) DEFAULT NULL, CHANGE lastname lastname VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE address address LONGTEXT DEFAULT NULL, CHANGE payment_method payment_method INT DEFAULT NULL, CHANGE payment_type payment_type INT DEFAULT NULL, CHANGE order_status order_status INT DEFAULT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939812136921 FOREIGN KEY (delivery_id) REFERENCES admin (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_F529939812136921 ON `order` (delivery_id)');
    }
}
