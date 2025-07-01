<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211130173005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE newsletter_content (id BIGINT AUTO_INCREMENT NOT NULL, service INT NOT NULL, locale VARCHAR(2) NOT NULL, created_at DATETIME NOT NULL, send_at DATETIME DEFAULT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, sent TINYINT(1) DEFAULT NULL, INDEX IDX_F17F93C6E19D9AD24180C698 (service, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE newsletter_email (token VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, service INT NOT NULL, locale VARCHAR(2) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_6DFF3080E19D9AD24180C698 (service, locale), UNIQUE INDEX newsletetr_email_uniq (email, locale), PRIMARY KEY(token)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE newsletter_content');
        $this->addSql('DROP TABLE newsletter_email');
    }
}
