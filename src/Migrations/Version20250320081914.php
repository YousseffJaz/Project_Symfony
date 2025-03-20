<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250320081914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, role VARCHAR(255) DEFAULT NULL, archive TINYINT(1) DEFAULT NULL, statistics TINYINT(1) DEFAULT NULL, invoices TINYINT(1) DEFAULT NULL, histories TINYINT(1) DEFAULT NULL, folders TINYINT(1) DEFAULT NULL, products TINYINT(1) DEFAULT NULL, accounting TINYINT(1) DEFAULT NULL, price_list VARCHAR(255) DEFAULT NULL, stock_list VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE flux (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, type TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE folder (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE line_item (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, order_item_id INT NOT NULL, variant_id INT DEFAULT NULL, stock_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, quantity INT NOT NULL, price DOUBLE PRECISION NOT NULL, price_list VARCHAR(255) DEFAULT NULL, INDEX IDX_9456D6C74584665A (product_id), INDEX IDX_9456D6C7E415FB15 (order_item_id), INDEX IDX_9456D6C73B69A9AF (variant_id), INDEX IDX_9456D6C7DCD6110 (stock_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, amount DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, admin_id INT NOT NULL, invoice_id INT DEFAULT NULL, seen TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_BF5476CA642B8210 (admin_id), INDEX IDX_BF5476CA2989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, admin_id INT NOT NULL, note2_id INT DEFAULT NULL, delivery_id INT DEFAULT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, identifier VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, status INT DEFAULT NULL, total DOUBLE PRECISION NOT NULL, shipping_cost DOUBLE PRECISION DEFAULT NULL, discount DOUBLE PRECISION DEFAULT NULL, paid DOUBLE PRECISION NOT NULL, payment_type INT DEFAULT NULL, payment_method INT DEFAULT NULL, note LONGTEXT DEFAULT NULL, shopify_note LONGTEXT DEFAULT NULL, address LONGTEXT DEFAULT NULL, order_status INT DEFAULT NULL, option1 TINYINT(1) DEFAULT NULL, option2 TINYINT(1) DEFAULT NULL, option3 TINYINT(1) DEFAULT NULL, option4 TINYINT(1) DEFAULT NULL, option5 TINYINT(1) DEFAULT NULL, option6 TINYINT(1) DEFAULT NULL, shopify_id VARCHAR(255) DEFAULT NULL, shopify_order_id VARCHAR(255) DEFAULT NULL, tracking_id VARCHAR(255) DEFAULT NULL, INDEX IDX_F5299398642B8210 (admin_id), INDEX IDX_F5299398DFFECA0E (note2_id), INDEX IDX_F529939812136921 (delivery_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_history (id INT AUTO_INCREMENT NOT NULL, invoice_id INT DEFAULT NULL, admin_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_D1C0D9002989F1FD (invoice_id), INDEX IDX_D1C0D900642B8210 (admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE preorder (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, title VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, quantity DOUBLE PRECISION NOT NULL, INDEX IDX_D9B775974584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE price_list (id INT AUTO_INCREMENT NOT NULL, variant_id INT NOT NULL, title VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, INDEX IDX_399A0AA23B69A9AF (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, price DOUBLE PRECISION DEFAULT NULL, archive TINYINT(1) DEFAULT NULL, alert INT DEFAULT NULL, purchase_price DOUBLE PRECISION DEFAULT NULL, digital TINYINT(1) DEFAULT NULL, INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reseller (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, company_address VARCHAR(255) NOT NULL, zipcode VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, longitude DOUBLE PRECISION DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, snap VARCHAR(255) DEFAULT NULL, position INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stock_list (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, name VARCHAR(255) NOT NULL, quantity INT DEFAULT NULL, INDEX IDX_5DD2A0DC4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, admin_id INT DEFAULT NULL, created_by_id INT NOT NULL, complete_by_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, complete TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_527EDB25642B8210 (admin_id), INDEX IDX_527EDB25B03A8386 (created_by_id), INDEX IDX_527EDB25FDFD524D (complete_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, note_id INT NOT NULL, invoice_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_723705D126ED0855 (note_id), UNIQUE INDEX UNIQ_723705D12989F1FD (invoice_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE upload (id INT AUTO_INCREMENT NOT NULL, invoice_id INT DEFAULT NULL, folder_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, name VARCHAR(255) DEFAULT NULL, INDEX IDX_17BDE61F2989F1FD (invoice_id), INDEX IDX_17BDE61F162CB942 (folder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, reseller_id INT DEFAULT NULL, pseudo VARCHAR(255) DEFAULT NULL, hash VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, push_token VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, note LONGTEXT DEFAULT NULL, INDEX IDX_8D93D64991E6A19D (reseller_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE variant (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, title VARCHAR(255) NOT NULL, archive TINYINT(1) DEFAULT NULL, INDEX IDX_F143BFAD4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE line_item ADD CONSTRAINT FK_9456D6C74584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE line_item ADD CONSTRAINT FK_9456D6C7E415FB15 FOREIGN KEY (order_item_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE line_item ADD CONSTRAINT FK_9456D6C73B69A9AF FOREIGN KEY (variant_id) REFERENCES variant (id)');
        $this->addSql('ALTER TABLE line_item ADD CONSTRAINT FK_9456D6C7DCD6110 FOREIGN KEY (stock_id) REFERENCES stock_list (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA2989F1FD FOREIGN KEY (invoice_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398DFFECA0E FOREIGN KEY (note2_id) REFERENCES note (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939812136921 FOREIGN KEY (delivery_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE order_history ADD CONSTRAINT FK_D1C0D9002989F1FD FOREIGN KEY (invoice_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_history ADD CONSTRAINT FK_D1C0D900642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE preorder ADD CONSTRAINT FK_D9B775974584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE price_list ADD CONSTRAINT FK_399A0AA23B69A9AF FOREIGN KEY (variant_id) REFERENCES variant (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE stock_list ADD CONSTRAINT FK_5DD2A0DC4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25B03A8386 FOREIGN KEY (created_by_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25FDFD524D FOREIGN KEY (complete_by_id) REFERENCES admin (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D126ED0855 FOREIGN KEY (note_id) REFERENCES note (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D12989F1FD FOREIGN KEY (invoice_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F2989F1FD FOREIGN KEY (invoice_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE upload ADD CONSTRAINT FK_17BDE61F162CB942 FOREIGN KEY (folder_id) REFERENCES folder (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64991E6A19D FOREIGN KEY (reseller_id) REFERENCES reseller (id)');
        $this->addSql('ALTER TABLE variant ADD CONSTRAINT FK_F143BFAD4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE line_item DROP FOREIGN KEY FK_9456D6C74584665A');
        $this->addSql('ALTER TABLE line_item DROP FOREIGN KEY FK_9456D6C7E415FB15');
        $this->addSql('ALTER TABLE line_item DROP FOREIGN KEY FK_9456D6C73B69A9AF');
        $this->addSql('ALTER TABLE line_item DROP FOREIGN KEY FK_9456D6C7DCD6110');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA642B8210');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA2989F1FD');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398642B8210');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398DFFECA0E');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939812136921');
        $this->addSql('ALTER TABLE order_history DROP FOREIGN KEY FK_D1C0D9002989F1FD');
        $this->addSql('ALTER TABLE order_history DROP FOREIGN KEY FK_D1C0D900642B8210');
        $this->addSql('ALTER TABLE preorder DROP FOREIGN KEY FK_D9B775974584665A');
        $this->addSql('ALTER TABLE price_list DROP FOREIGN KEY FK_399A0AA23B69A9AF');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE stock_list DROP FOREIGN KEY FK_5DD2A0DC4584665A');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25642B8210');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25B03A8386');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25FDFD524D');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D126ED0855');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D12989F1FD');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F2989F1FD');
        $this->addSql('ALTER TABLE upload DROP FOREIGN KEY FK_17BDE61F162CB942');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64991E6A19D');
        $this->addSql('ALTER TABLE variant DROP FOREIGN KEY FK_F143BFAD4584665A');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE flux');
        $this->addSql('DROP TABLE folder');
        $this->addSql('DROP TABLE line_item');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_history');
        $this->addSql('DROP TABLE preorder');
        $this->addSql('DROP TABLE price_list');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE reseller');
        $this->addSql('DROP TABLE stock_list');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE upload');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE variant');
    }
}
