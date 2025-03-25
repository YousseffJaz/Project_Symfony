<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250325153243 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE admin_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE customer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE flux_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE line_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "order_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE order_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE price_list_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE stock_list_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE transaction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE variant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE admin (id INT NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, role VARCHAR(255) DEFAULT NULL, archive BOOLEAN DEFAULT NULL, statistics BOOLEAN DEFAULT NULL, invoices BOOLEAN DEFAULT NULL, histories BOOLEAN DEFAULT NULL, products BOOLEAN DEFAULT NULL, accounting BOOLEAN DEFAULT NULL, price_list VARCHAR(255) DEFAULT NULL, stock_list VARCHAR(255) DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, archived_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, last_name VARCHAR(255) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_880E0D76E7927C74 ON admin (email)');
        $this->addSql('COMMENT ON COLUMN admin.archived_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE category (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE customer (id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, address TEXT NOT NULL, phone VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN customer.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE flux (id INT NOT NULL, name VARCHAR(255) NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE line_item (id INT NOT NULL, product_id INT DEFAULT NULL, order_id INT NOT NULL, variant_id INT DEFAULT NULL, stock_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, quantity INT NOT NULL, price DOUBLE PRECISION NOT NULL, price_list VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9456D6C74584665A ON line_item (product_id)');
        $this->addSql('CREATE INDEX IDX_9456D6C78D9F6D38 ON line_item (order_id)');
        $this->addSql('CREATE INDEX IDX_9456D6C73B69A9AF ON line_item (variant_id)');
        $this->addSql('CREATE INDEX IDX_9456D6C7DCD6110 ON line_item (stock_id)');
        $this->addSql('CREATE TABLE "order" (id INT NOT NULL, customer_id INT DEFAULT NULL, admin_id INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, total DOUBLE PRECISION NOT NULL, paid DOUBLE PRECISION NOT NULL, payment_method VARCHAR(255) NOT NULL, payment_type VARCHAR(255) NOT NULL, order_status VARCHAR(255) NOT NULL, note TEXT DEFAULT NULL, shipping_cost DOUBLE PRECISION DEFAULT NULL, discount DOUBLE PRECISION DEFAULT NULL, tracking_id VARCHAR(255) DEFAULT NULL, status INT NOT NULL, note2 TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F52993989395C3F3 ON "order" (customer_id)');
        $this->addSql('CREATE INDEX IDX_F5299398642B8210 ON "order" (admin_id)');
        $this->addSql('CREATE TABLE order_history (id INT NOT NULL, invoice_id INT DEFAULT NULL, admin_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D1C0D9002989F1FD ON order_history (invoice_id)');
        $this->addSql('CREATE INDEX IDX_D1C0D900642B8210 ON order_history (admin_id)');
        $this->addSql('CREATE TABLE price_list (id INT NOT NULL, variant_id INT NOT NULL, title VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_399A0AA23B69A9AF ON price_list (variant_id)');
        $this->addSql('CREATE TABLE product (id INT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, price DOUBLE PRECISION DEFAULT NULL, archive BOOLEAN DEFAULT NULL, alert INT DEFAULT NULL, purchase_price DOUBLE PRECISION DEFAULT NULL, digital BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D34A04AD12469DE2 ON product (category_id)');
        $this->addSql('CREATE TABLE stock_list (id INT NOT NULL, product_id INT NOT NULL, name VARCHAR(255) NOT NULL, quantity INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5DD2A0DC4584665A ON stock_list (product_id)');
        $this->addSql('CREATE TABLE transaction (id INT NOT NULL, invoice_id INT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, comment TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_723705D12989F1FD ON transaction (invoice_id)');
        $this->addSql('CREATE TABLE variant (id INT NOT NULL, product_id INT NOT NULL, title VARCHAR(255) NOT NULL, archive BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F143BFAD4584665A ON variant (product_id)');
        $this->addSql('ALTER TABLE line_item ADD CONSTRAINT FK_9456D6C74584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE line_item ADD CONSTRAINT FK_9456D6C78D9F6D38 FOREIGN KEY (order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE line_item ADD CONSTRAINT FK_9456D6C73B69A9AF FOREIGN KEY (variant_id) REFERENCES variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE line_item ADD CONSTRAINT FK_9456D6C7DCD6110 FOREIGN KEY (stock_id) REFERENCES stock_list (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_history ADD CONSTRAINT FK_D1C0D9002989F1FD FOREIGN KEY (invoice_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_history ADD CONSTRAINT FK_D1C0D900642B8210 FOREIGN KEY (admin_id) REFERENCES admin (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE price_list ADD CONSTRAINT FK_399A0AA23B69A9AF FOREIGN KEY (variant_id) REFERENCES variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE stock_list ADD CONSTRAINT FK_5DD2A0DC4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D12989F1FD FOREIGN KEY (invoice_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE variant ADD CONSTRAINT FK_F143BFAD4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE admin_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE customer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE flux_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE line_item_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "order_id_seq" CASCADE');
        $this->addSql('DROP SEQUENCE order_history_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE price_list_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE stock_list_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE transaction_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE variant_id_seq CASCADE');
        $this->addSql('ALTER TABLE line_item DROP CONSTRAINT FK_9456D6C74584665A');
        $this->addSql('ALTER TABLE line_item DROP CONSTRAINT FK_9456D6C78D9F6D38');
        $this->addSql('ALTER TABLE line_item DROP CONSTRAINT FK_9456D6C73B69A9AF');
        $this->addSql('ALTER TABLE line_item DROP CONSTRAINT FK_9456D6C7DCD6110');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F52993989395C3F3');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F5299398642B8210');
        $this->addSql('ALTER TABLE order_history DROP CONSTRAINT FK_D1C0D9002989F1FD');
        $this->addSql('ALTER TABLE order_history DROP CONSTRAINT FK_D1C0D900642B8210');
        $this->addSql('ALTER TABLE price_list DROP CONSTRAINT FK_399A0AA23B69A9AF');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE stock_list DROP CONSTRAINT FK_5DD2A0DC4584665A');
        $this->addSql('ALTER TABLE transaction DROP CONSTRAINT FK_723705D12989F1FD');
        $this->addSql('ALTER TABLE variant DROP CONSTRAINT FK_F143BFAD4584665A');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE flux');
        $this->addSql('DROP TABLE line_item');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE order_history');
        $this->addSql('DROP TABLE price_list');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE stock_list');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE variant');
    }
}
