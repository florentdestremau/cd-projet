<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260614225228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE documents (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, storage_path VARCHAR(255) NOT NULL, mime_type VARCHAR(120) NOT NULL, size INTEGER NOT NULL, category VARCHAR(20) NOT NULL, uploaded_at DATETIME NOT NULL, project_id INTEGER NOT NULL, uploaded_by_id INTEGER DEFAULT NULL, CONSTRAINT FK_A2B07288166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_A2B07288A2B28FE8 FOREIGN KEY (uploaded_by_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_A2B07288166D1F9C ON documents (project_id)');
        $this->addSql('CREATE INDEX IDX_A2B07288A2B28FE8 ON documents (uploaded_by_id)');
        $this->addSql('CREATE TABLE materials (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(120) NOT NULL, type VARCHAR(20) NOT NULL, price_per_gram INTEGER NOT NULL, supplier_id INTEGER DEFAULT NULL, CONSTRAINT FK_9B1716B52ADD6D8C FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_9B1716B52ADD6D8C ON materials (supplier_id)');
        $this->addSql('CREATE TABLE stones (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(20) NOT NULL, carat_weight INTEGER NOT NULL, quality VARCHAR(60) DEFAULT NULL, color VARCHAR(40) DEFAULT NULL, certificate_ref VARCHAR(120) DEFAULT NULL, cost_price INTEGER NOT NULL, supplier_id INTEGER DEFAULT NULL, CONSTRAINT FK_C0AC26D82ADD6D8C FOREIGN KEY (supplier_id) REFERENCES suppliers (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_C0AC26D82ADD6D8C ON stones (supplier_id)');
        $this->addSql('CREATE TABLE suppliers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(160) NOT NULL, contact_email VARCHAR(180) DEFAULT NULL, contact_phone VARCHAR(40) DEFAULT NULL, specialty VARCHAR(20) NOT NULL, notes CLOB DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE documents');
        $this->addSql('DROP TABLE materials');
        $this->addSql('DROP TABLE stones');
        $this->addSql('DROP TABLE suppliers');
    }
}
