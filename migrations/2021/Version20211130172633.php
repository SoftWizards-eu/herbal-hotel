<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211130172633 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin_users (id INT AUTO_INCREMENT NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(60) NOT NULL, main_roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', is_enabled TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_B4A95E13E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', path VARCHAR(255) NOT NULL, repository VARCHAR(255) NOT NULL, mime VARCHAR(255) NOT NULL, size INT NOT NULL, meta LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE node (id BIGINT AUTO_INCREMENT NOT NULL, parent_id BIGINT DEFAULT NULL, node_url_id BIGINT DEFAULT NULL, service INT NOT NULL, pos INT NOT NULL, type VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, external_id VARCHAR(255) DEFAULT NULL, INDEX IDX_857FE845727ACA70 (parent_id), INDEX IDX_857FE84535DDCF42 (node_url_id), INDEX node_type_idx (service, type, locale), INDEX node_external_id (external_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE node_url (id BIGINT AUTO_INCREMENT NOT NULL, service INT NOT NULL, locale VARCHAR(2) NOT NULL, slug VARCHAR(255) NOT NULL, path VARCHAR(1000) NOT NULL, INDEX node_url_service_idx (service), INDEX node_url_pathx (service, locale, path), UNIQUE INDEX UNIQ_875367D5E19D9AD24180C698989D9B62 (service, locale, slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET ascii COLLATE `ascii_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE node_variable (name VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, pos INT NOT NULL, node_id BIGINT NOT NULL, file_value CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', node_value BIGINT DEFAULT NULL, type VARCHAR(255) NOT NULL, string_value VARCHAR(255) DEFAULT NULL, int_value INT DEFAULT NULL, float_value DOUBLE PRECISION DEFAULT NULL, text_value LONGTEXT DEFAULT NULL, date_time_value DATETIME DEFAULT NULL, INDEX IDX_72E09FD8460D9FD7 (node_id), INDEX IDX_72E09FD8B7F4E803 (file_value), INDEX IDX_72E09FD84DCCAC8 (node_value), PRIMARY KEY(node_id, name, locale, pos)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setting (service INT NOT NULL, name VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, pos INT NOT NULL, file_value CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', node_value BIGINT DEFAULT NULL, type VARCHAR(255) NOT NULL, string_value VARCHAR(255) DEFAULT NULL, int_value INT DEFAULT NULL, float_value DOUBLE PRECISION DEFAULT NULL, text_value LONGTEXT DEFAULT NULL, date_time_value DATETIME DEFAULT NULL, INDEX IDX_9F74B898B7F4E803 (file_value), INDEX IDX_9F74B8984DCCAC8 (node_value), PRIMARY KEY(service, name, locale, pos)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE845727ACA70 FOREIGN KEY (parent_id) REFERENCES node (id)');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE84535DDCF42 FOREIGN KEY (node_url_id) REFERENCES node_url (id)');
        $this->addSql('ALTER TABLE node_variable ADD CONSTRAINT FK_72E09FD8460D9FD7 FOREIGN KEY (node_id) REFERENCES node (id)');
        $this->addSql('ALTER TABLE node_variable ADD CONSTRAINT FK_72E09FD8B7F4E803 FOREIGN KEY (file_value) REFERENCES file (id)');
        $this->addSql('ALTER TABLE node_variable ADD CONSTRAINT FK_72E09FD84DCCAC8 FOREIGN KEY (node_value) REFERENCES node (id)');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT FK_9F74B898B7F4E803 FOREIGN KEY (file_value) REFERENCES file (id)');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT FK_9F74B8984DCCAC8 FOREIGN KEY (node_value) REFERENCES node (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE node_variable DROP FOREIGN KEY FK_72E09FD8B7F4E803');
        $this->addSql('ALTER TABLE setting DROP FOREIGN KEY FK_9F74B898B7F4E803');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE845727ACA70');
        $this->addSql('ALTER TABLE node_variable DROP FOREIGN KEY FK_72E09FD8460D9FD7');
        $this->addSql('ALTER TABLE node_variable DROP FOREIGN KEY FK_72E09FD84DCCAC8');
        $this->addSql('ALTER TABLE setting DROP FOREIGN KEY FK_9F74B8984DCCAC8');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE84535DDCF42');
        $this->addSql('DROP TABLE admin_users');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE node');
        $this->addSql('DROP TABLE node_url');
        $this->addSql('DROP TABLE node_variable');
        $this->addSql('DROP TABLE setting');
    }
}
