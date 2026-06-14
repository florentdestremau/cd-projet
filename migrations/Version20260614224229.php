<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260614224229 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE expenses (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, category VARCHAR(20) NOT NULL, amount_ht INTEGER NOT NULL, vat_amount INTEGER NOT NULL, occurred_at DATE NOT NULL, description VARCHAR(255) NOT NULL, supplier_name VARCHAR(160) DEFAULT NULL, project_id INTEGER NOT NULL, CONSTRAINT FK_2496F35B166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2496F35B166D1F9C ON expenses (project_id)');
        $this->addSql('CREATE TABLE invoice_items (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, quantity INTEGER NOT NULL, unit_price_ht INTEGER NOT NULL, invoice_id INTEGER NOT NULL, CONSTRAINT FK_DCC4B9F82989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_DCC4B9F82989F1FD ON invoice_items (invoice_id)');
        $this->addSql('CREATE TABLE invoices (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, vat_rate INTEGER NOT NULL, due_date DATE DEFAULT NULL, sent_at DATETIME DEFAULT NULL, paid_at DATETIME DEFAULT NULL, pdf_path VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, project_id INTEGER NOT NULL, quote_id INTEGER DEFAULT NULL, CONSTRAINT FK_6A2F2F95166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6A2F2F95DB805178 FOREIGN KEY (quote_id) REFERENCES quotes (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6A2F2F95166D1F9C ON invoices (project_id)');
        $this->addSql('CREATE INDEX IDX_6A2F2F95DB805178 ON invoices (quote_id)');
        $this->addSql('CREATE UNIQUE INDEX invoices_reference_uniq ON invoices (reference)');
        $this->addSql('CREATE TABLE payments (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, amount INTEGER NOT NULL, method VARCHAR(20) NOT NULL, received_at DATETIME NOT NULL, reference VARCHAR(100) DEFAULT NULL, invoice_id INTEGER NOT NULL, CONSTRAINT FK_65D29B322989F1FD FOREIGN KEY (invoice_id) REFERENCES invoices (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_65D29B322989F1FD ON payments (invoice_id)');
        $this->addSql('CREATE TABLE quote_items (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) NOT NULL, quantity INTEGER NOT NULL, unit_price_ht INTEGER NOT NULL, quote_id INTEGER NOT NULL, CONSTRAINT FK_ECE1642CDB805178 FOREIGN KEY (quote_id) REFERENCES quotes (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_ECE1642CDB805178 ON quote_items (quote_id)');
        $this->addSql('CREATE TABLE quotes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, valid_until DATE DEFAULT NULL, vat_rate INTEGER NOT NULL, sent_at DATETIME DEFAULT NULL, accepted_at DATETIME DEFAULT NULL, pdf_path VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, project_id INTEGER NOT NULL, CONSTRAINT FK_A1B588C5166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A1B588C5166D1F9C ON quotes (project_id)');
        $this->addSql('CREATE UNIQUE INDEX quotes_reference_uniq ON quotes (reference)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE expenses');
        $this->addSql('DROP TABLE invoice_items');
        $this->addSql('DROP TABLE invoices');
        $this->addSql('DROP TABLE payments');
        $this->addSql('DROP TABLE quote_items');
        $this->addSql('DROP TABLE quotes');
    }
}
