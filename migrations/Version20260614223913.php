<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260614223913 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_push_subscriptions (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, endpoint CLOB NOT NULL, p256dh_key VARCHAR(255) NOT NULL, auth_token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_93A8A266A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_93A8A266A76ED395 ON user_push_subscriptions (user_id)');
        $this->addSql('CREATE UNIQUE INDEX ups_endpoint_uniq ON user_push_subscriptions (endpoint)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user_push_subscriptions');
    }
}
