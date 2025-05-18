<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250328091009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial database schema for MySQL/MariaDB';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE admin (
                id INT AUTO_INCREMENT NOT NULL,
                first_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                hash VARCHAR(255) NOT NULL,
                role VARCHAR(255) DEFAULT NULL,
                archive BOOLEAN DEFAULT NULL,
                statistics BOOLEAN DEFAULT NULL,
                invoices BOOLEAN DEFAULT NULL,
                histories BOOLEAN DEFAULT NULL,
                products BOOLEAN DEFAULT NULL,
                accounting BOOLEAN DEFAULT NULL,
                stock_list VARCHAR(255) DEFAULT NULL,
                is_active BOOLEAN DEFAULT true NOT NULL,
                archived_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                last_name VARCHAR(255) DEFAULT NULL,
                phone VARCHAR(20) DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_880E0D76E7927C74 ON admin (email)
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE category (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE customer (
                id INT AUTO_INCREMENT NOT NULL,
                firstname VARCHAR(255) NOT NULL,
                lastname VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                address TEXT NOT NULL,
                phone VARCHAR(255) DEFAULT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE flux (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                amount DOUBLE PRECISION NOT NULL,
                created_at DATETIME NOT NULL,
                type BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE line_item (
                id INT AUTO_INCREMENT NOT NULL,
                product_id INT DEFAULT NULL,
                order_id INT NOT NULL,
                variant_id INT DEFAULT NULL,
                stock_id INT DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                quantity INT NOT NULL,
                price DOUBLE PRECISION NOT NULL,
                PRIMARY KEY(id),
                INDEX IDX_9456D6C74584665A (product_id),
                INDEX IDX_9456D6C78D9F6D38 (order_id),
                INDEX IDX_9456D6C73B69A9AF (variant_id),
                INDEX IDX_9456D6C7DCD6110 (stock_id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE `order` (
                id INT AUTO_INCREMENT NOT NULL,
                customer_id INT DEFAULT NULL,
                admin_id INT DEFAULT NULL,
                created_at DATETIME NOT NULL,
                total DOUBLE PRECISION NOT NULL,
                paid DOUBLE PRECISION NOT NULL,
                payment_method VARCHAR(255) NOT NULL,
                payment_type VARCHAR(255) NOT NULL,
                order_status VARCHAR(255) NOT NULL,
                note TEXT DEFAULT NULL,
                shipping_cost DOUBLE PRECISION DEFAULT NULL,
                discount DOUBLE PRECISION DEFAULT NULL,
                tracking_id VARCHAR(255) DEFAULT NULL,
                status INT NOT NULL,
                note2 TEXT DEFAULT NULL,
                PRIMARY KEY(id),
                INDEX IDX_F52993989395C3F3 (customer_id),
                INDEX IDX_F5299398642B8210 (admin_id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE order_history (
                id INT AUTO_INCREMENT NOT NULL,
                invoice_id INT DEFAULT NULL,
                admin_id INT DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY(id),
                INDEX IDX_D1C0D9002989F1FD (invoice_id),
                INDEX IDX_D1C0D900642B8210 (admin_id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE product (
                id INT AUTO_INCREMENT NOT NULL,
                category_id INT DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                price DOUBLE PRECISION DEFAULT NULL,
                archive BOOLEAN DEFAULT NULL,
                alert INT DEFAULT NULL,
                purchase_price DOUBLE PRECISION DEFAULT NULL,
                digital BOOLEAN DEFAULT NULL,
                PRIMARY KEY(id),
                INDEX IDX_D34A04AD12469DE2 (category_id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE stock_list (
                id INT AUTO_INCREMENT NOT NULL,
                product_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                quantity INT DEFAULT NULL,
                PRIMARY KEY(id),
                INDEX IDX_5DD2A0DC4584665A (product_id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE transaction (
                id INT AUTO_INCREMENT NOT NULL,
                invoice_id INT DEFAULT NULL,
                amount DOUBLE PRECISION NOT NULL,
                created_at DATETIME NOT NULL,
                comment TEXT DEFAULT NULL,
                PRIMARY KEY(id),
                INDEX IDX_723705D12989F1FD (invoice_id)
            )
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE variant (
                id INT AUTO_INCREMENT NOT NULL,
                product_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                archive BOOLEAN DEFAULT NULL,
                price DOUBLE PRECISION NOT NULL,
                PRIMARY KEY(id),
                INDEX IDX_F143BFAD4584665A (product_id)
            )
        SQL);

        // Foreign keys
        $this->addSql(<<<'SQL'
            ALTER TABLE line_item 
            ADD CONSTRAINT FK_9456D6C74584665A FOREIGN KEY (product_id) REFERENCES product (id),
            ADD CONSTRAINT FK_9456D6C78D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id),
            ADD CONSTRAINT FK_9456D6C73B69A9AF FOREIGN KEY (variant_id) REFERENCES variant (id),
            ADD CONSTRAINT FK_9456D6C7DCD6110 FOREIGN KEY (stock_id) REFERENCES stock_list (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE `order`
            ADD CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id),
            ADD CONSTRAINT FK_F5299398642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE order_history
            ADD CONSTRAINT FK_D1C0D9002989F1FD FOREIGN KEY (invoice_id) REFERENCES `order` (id),
            ADD CONSTRAINT FK_D1C0D900642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE product
            ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE stock_list
            ADD CONSTRAINT FK_5DD2A0DC4584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE transaction
            ADD CONSTRAINT FK_723705D12989F1FD FOREIGN KEY (invoice_id) REFERENCES `order` (id)
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE variant
            ADD CONSTRAINT FK_F143BFAD4584665A FOREIGN KEY (product_id) REFERENCES product (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS line_item');
        $this->addSql('DROP TABLE IF EXISTS `order`');
        $this->addSql('DROP TABLE IF EXISTS order_history');
        $this->addSql('DROP TABLE IF EXISTS admin');
        $this->addSql('DROP TABLE IF EXISTS category');
        $this->addSql('DROP TABLE IF EXISTS customer');
        $this->addSql('DROP TABLE IF EXISTS flux');
        $this->addSql('DROP TABLE IF EXISTS product');
        $this->addSql('DROP TABLE IF EXISTS stock_list');
        $this->addSql('DROP TABLE IF EXISTS transaction');
        $this->addSql('DROP TABLE IF EXISTS variant');
    }
}
