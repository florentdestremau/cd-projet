<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260614213820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity_logs (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, event_type VARCHAR(60) NOT NULL, payload CLOB NOT NULL, created_at DATETIME NOT NULL, project_id INTEGER DEFAULT NULL, actor_id INTEGER NOT NULL, CONSTRAINT FK_F34B1DCE166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_F34B1DCE10DAF24A FOREIGN KEY (actor_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_F34B1DCE166D1F9C ON activity_logs (project_id)');
        $this->addSql('CREATE INDEX IDX_F34B1DCE10DAF24A ON activity_logs (actor_id)');
        $this->addSql('CREATE INDEX activity_logs_created_idx ON activity_logs (created_at)');
        $this->addSql('CREATE TABLE clients (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, display_name VARCHAR(160) NOT NULL, company_name VARCHAR(160) DEFAULT NULL, contact_email VARCHAR(180) DEFAULT NULL, contact_phone VARCHAR(40) DEFAULT NULL, address CLOB DEFAULT NULL, notes CLOB DEFAULT NULL, created_at DATETIME NOT NULL, linked_user_id INTEGER DEFAULT NULL, CONSTRAINT FK_C82E74CC26EB02 FOREIGN KEY (linked_user_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C82E74CC26EB02 ON clients (linked_user_id)');
        $this->addSql('CREATE TABLE comments (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, created_at DATETIME NOT NULL, edited_at DATETIME DEFAULT NULL, project_id INTEGER NOT NULL, author_id INTEGER NOT NULL, CONSTRAINT FK_5F9E962A166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5F9E962AF675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5F9E962A166D1F9C ON comments (project_id)');
        $this->addSql('CREATE INDEX IDX_5F9E962AF675F31B ON comments (author_id)');
        $this->addSql('CREATE INDEX comments_project_created_idx ON comments (project_id, created_at)');
        $this->addSql('CREATE TABLE comment_mentions (comment_id INTEGER NOT NULL, user_id INTEGER NOT NULL, PRIMARY KEY (comment_id, user_id), CONSTRAINT FK_E37D1059F8697D13 FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E37D1059A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_E37D1059F8697D13 ON comment_mentions (comment_id)');
        $this->addSql('CREATE INDEX IDX_E37D1059A76ED395 ON comment_mentions (user_id)');
        $this->addSql('CREATE TABLE project_stage_statuses (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, stage VARCHAR(30) NOT NULL, applicable BOOLEAN NOT NULL, started_at DATETIME DEFAULT NULL, completed_at DATETIME DEFAULT NULL, notes CLOB DEFAULT NULL, project_id INTEGER NOT NULL, CONSTRAINT FK_AAA0EEDE166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_AAA0EEDE166D1F9C ON project_stage_statuses (project_id)');
        $this->addSql('CREATE UNIQUE INDEX pss_project_stage_uniq ON project_stage_statuses (project_id, stage)');
        $this->addSql('CREATE TABLE projects (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, reference VARCHAR(20) NOT NULL, title VARCHAR(200) NOT NULL, status VARCHAR(20) NOT NULL, current_stage VARCHAR(30) NOT NULL, priority VARCHAR(10) NOT NULL, target_delivery_date DATE DEFAULT NULL, delivered_at DATETIME DEFAULT NULL, budget_target INTEGER NOT NULL, selling_price INTEGER NOT NULL, description CLOB DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, client_id INTEGER NOT NULL, assigned_designer_id INTEGER DEFAULT NULL, assigned_jeweler_id INTEGER DEFAULT NULL, assigned_setter_id INTEGER DEFAULT NULL, CONSTRAINT FK_5C93B3A419EB6921 FOREIGN KEY (client_id) REFERENCES clients (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5C93B3A4E8FFAF62 FOREIGN KEY (assigned_designer_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5C93B3A42D3E3B7 FOREIGN KEY (assigned_jeweler_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5C93B3A44FFDC724 FOREIGN KEY (assigned_setter_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_5C93B3A419EB6921 ON projects (client_id)');
        $this->addSql('CREATE INDEX IDX_5C93B3A4E8FFAF62 ON projects (assigned_designer_id)');
        $this->addSql('CREATE INDEX IDX_5C93B3A42D3E3B7 ON projects (assigned_jeweler_id)');
        $this->addSql('CREATE INDEX IDX_5C93B3A44FFDC724 ON projects (assigned_setter_id)');
        $this->addSql('CREATE INDEX projects_status_stage_idx ON projects (status, current_stage)');
        $this->addSql('CREATE UNIQUE INDEX projects_reference_uniq ON projects (reference)');
        $this->addSql('CREATE TABLE tasks (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(200) NOT NULL, description CLOB DEFAULT NULL, due_date DATE DEFAULT NULL, completed_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, project_id INTEGER NOT NULL, assignee_id INTEGER DEFAULT NULL, completed_by_id INTEGER DEFAULT NULL, CONSTRAINT FK_50586597166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5058659759EC7D60 FOREIGN KEY (assignee_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_5058659785ECDE76 FOREIGN KEY (completed_by_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_50586597166D1F9C ON tasks (project_id)');
        $this->addSql('CREATE INDEX IDX_5058659759EC7D60 ON tasks (assignee_id)');
        $this->addSql('CREATE INDEX IDX_5058659785ECDE76 ON tasks (completed_by_id)');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(80) NOT NULL, last_name VARCHAR(80) NOT NULL, roles CLOB NOT NULL, avatar VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX users_email_uniq ON users (email)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE activity_logs');
        $this->addSql('DROP TABLE clients');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE comment_mentions');
        $this->addSql('DROP TABLE project_stage_statuses');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE tasks');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
