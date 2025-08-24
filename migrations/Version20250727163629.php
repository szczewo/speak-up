<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250727163629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, UNIQUE INDEX UNIQ_D4DB71B55E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE student (id INT NOT NULL, learning_language_id INT DEFAULT NULL, INDEX IDX_B723AF33A0208092 (learning_language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE teacher (id INT NOT NULL, city VARCHAR(128) NOT NULL, price_per_lesson NUMERIC(6, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE teaching_language (teacher_id INT NOT NULL, language_id INT NOT NULL, price NUMERIC(6, 2) NOT NULL, INDEX IDX_5AB2A18F41807E1D (teacher_id), INDEX IDX_5AB2A18F82F1BAF4 (language_id), PRIMARY KEY(teacher_id, language_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, name VARCHAR(45) NOT NULL, last_name VARCHAR(45) NOT NULL, reset_token VARCHAR(255) DEFAULT NULL, reset_token_expires_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE student ADD CONSTRAINT FK_B723AF33A0208092 FOREIGN KEY (learning_language_id) REFERENCES language (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE student ADD CONSTRAINT FK_B723AF33BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE teacher ADD CONSTRAINT FK_B0F6A6D5BF396750 FOREIGN KEY (id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE teaching_language ADD CONSTRAINT FK_5AB2A18F41807E1D FOREIGN KEY (teacher_id) REFERENCES teacher (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE teaching_language ADD CONSTRAINT FK_5AB2A18F82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE student DROP FOREIGN KEY FK_B723AF33A0208092
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE student DROP FOREIGN KEY FK_B723AF33BF396750
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE teacher DROP FOREIGN KEY FK_B0F6A6D5BF396750
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE teaching_language DROP FOREIGN KEY FK_5AB2A18F41807E1D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE teaching_language DROP FOREIGN KEY FK_5AB2A18F82F1BAF4
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE language
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE student
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE teacher
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE teaching_language
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
    }
}
