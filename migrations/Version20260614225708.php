<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260614225708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE settings ("key" VARCHAR(80) NOT NULL, value CLOB DEFAULT NULL, PRIMARY KEY ("key"))');
        $this->addSql('CREATE TEMPORARY TABLE __temp__projects AS SELECT id, reference, title, status, current_stage, priority, target_delivery_date, delivered_at, budget_target, selling_price, description, created_at, updated_at, client_id, assigned_designer_id, assigned_jeweler_id, assigned_setter_id FROM projects');
        $this->addSql('DROP TABLE projects');
        $this->addSql('CREATE TABLE projects (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference VARCHAR(20) NOT NULL, title VARCHAR(200) NOT NULL, status VARCHAR(20) NOT NULL, current_stage VARCHAR(30) NOT NULL, priority VARCHAR(10) NOT NULL, target_delivery_date DATE DEFAULT NULL, delivered_at DATETIME DEFAULT NULL, budget_target INTEGER NOT NULL, selling_price INTEGER NOT NULL, description CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, client_id INTEGER NOT NULL, assigned_designer_id INTEGER DEFAULT NULL, assigned_jeweler_id INTEGER DEFAULT NULL, assigned_setter_id INTEGER DEFAULT NULL, client_access_token VARCHAR(64) DEFAULT NULL, CONSTRAINT FK_5C93B3A419EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5C93B3A4E8FFAF62 FOREIGN KEY (assigned_designer_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5C93B3A42D3E3B7 FOREIGN KEY (assigned_jeweler_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5C93B3A44FFDC724 FOREIGN KEY (assigned_setter_id) REFERENCES users (id) ON UPDATE NO ACTION ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO projects (id, reference, title, status, current_stage, priority, target_delivery_date, delivered_at, budget_target, selling_price, description, created_at, updated_at, client_id, assigned_designer_id, assigned_jeweler_id, assigned_setter_id) SELECT id, reference, title, status, current_stage, priority, target_delivery_date, delivered_at, budget_target, selling_price, description, created_at, updated_at, client_id, assigned_designer_id, assigned_jeweler_id, assigned_setter_id FROM __temp__projects');
        $this->addSql('DROP TABLE __temp__projects');
        $this->addSql('CREATE UNIQUE INDEX projects_reference_uniq ON projects (reference)');
        $this->addSql('CREATE INDEX projects_status_stage_idx ON projects (status, current_stage)');
        $this->addSql('CREATE INDEX IDX_5C93B3A44FFDC724 ON projects (assigned_setter_id)');
        $this->addSql('CREATE INDEX IDX_5C93B3A42D3E3B7 ON projects (assigned_jeweler_id)');
        $this->addSql('CREATE INDEX IDX_5C93B3A4E8FFAF62 ON projects (assigned_designer_id)');
        $this->addSql('CREATE INDEX IDX_5C93B3A419EB6921 ON projects (client_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5C93B3A47A87F69 ON projects (client_access_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE settings');
        $this->addSql('CREATE TEMPORARY TABLE __temp__projects AS SELECT id, reference, title, status, current_stage, priority, target_delivery_date, delivered_at, budget_target, selling_price, description, created_at, updated_at, client_id, assigned_designer_id, assigned_jeweler_id, assigned_setter_id FROM projects');
        $this->addSql('DROP TABLE projects');
        $this->addSql('CREATE TABLE projects (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference VARCHAR(20) NOT NULL, title VARCHAR(200) NOT NULL, status VARCHAR(20) NOT NULL, current_stage VARCHAR(30) NOT NULL, priority VARCHAR(10) NOT NULL, target_delivery_date DATE DEFAULT NULL, delivered_at DATETIME DEFAULT NULL, budget_target INTEGER NOT NULL, selling_price INTEGER NOT NULL, description CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, client_id INTEGER NOT NULL, assigned_designer_id INTEGER DEFAULT NULL, assigned_jeweler_id INTEGER DEFAULT NULL, assigned_setter_id INTEGER DEFAULT NULL, CONSTRAINT FK_5C93B3A419EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5C93B3A4E8FFAF62 FOREIGN KEY (assigned_designer_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5C93B3A42D3E3B7 FOREIGN KEY (assigned_jeweler_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5C93B3A44FFDC724 FOREIGN KEY (assigned_setter_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO projects (id, reference, title, status, current_stage, priority, target_delivery_date, delivered_at, budget_target, selling_price, description, created_at, updated_at, client_id, assigned_designer_id, assigned_jeweler_id, assigned_setter_id) SELECT id, reference, title, status, current_stage, priority, target_delivery_date, delivered_at, budget_target, selling_price, description, created_at, updated_at, client_id, assigned_designer_id, assigned_jeweler_id, assigned_setter_id FROM __temp__projects');
        $this->addSql('DROP TABLE __temp__projects');
        $this->addSql('CREATE INDEX IDX_5C93B3A419EB6921 ON projects (client_id)');
        $this->addSql('CREATE INDEX IDX_5C93B3A4E8FFAF62 ON projects (assigned_designer_id)');
        $this->addSql('CREATE INDEX IDX_5C93B3A42D3E3B7 ON projects (assigned_jeweler_id)');
        $this->addSql('CREATE INDEX IDX_5C93B3A44FFDC724 ON projects (assigned_setter_id)');
        $this->addSql('CREATE INDEX projects_status_stage_idx ON projects (status, current_stage)');
        $this->addSql('CREATE UNIQUE INDEX projects_reference_uniq ON projects (reference)');
    }
}
