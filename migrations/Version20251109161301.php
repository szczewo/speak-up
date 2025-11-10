<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251109161301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD verification_token VARCHAR(255) DEFAULT NULL, ADD verification_token_expires_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', CHANGE name name VARCHAR(45) DEFAULT NULL, CHANGE last_name last_name VARCHAR(45) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP verification_token, DROP verification_token_expires_at, CHANGE name name VARCHAR(45) NOT NULL, CHANGE last_name last_name VARCHAR(45) NOT NULL
        SQL);
    }
}
