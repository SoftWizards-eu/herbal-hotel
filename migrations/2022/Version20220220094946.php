<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220220094946 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE node_pos (node_id BIGINT NOT NULL, target_node_id BIGINT NOT NULL, pos INT NOT NULL, INDEX IDX_F3FCC4D7460D9FD7 (node_id), INDEX IDX_F3FCC4D78D6526BC (target_node_id), PRIMARY KEY(node_id, target_node_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE node_pos ADD CONSTRAINT FK_F3FCC4D7460D9FD7 FOREIGN KEY (node_id) REFERENCES node (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE node_pos ADD CONSTRAINT FK_F3FCC4D78D6526BC FOREIGN KEY (target_node_id) REFERENCES node (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE newsletter_email CHANGE token token VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX newsletetr_email_uniq ON newsletter_email (email, locale)');
        $this->addSql('ALTER TABLE node DROP FOREIGN KEY FK_857FE84535DDCF42');
        $this->addSql('DROP INDEX IDX_857FE84535DDCF42 ON node');
        $this->addSql('DROP INDEX node_type_idx ON node');
        $this->addSql('DROP INDEX node_external_id ON node');
        $this->addSql('ALTER TABLE node ADD meta LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', DROP node_url_id');
        $this->addSql('CREATE INDEX node_type_idx ON node (service, type, locale)');
        $this->addSql('CREATE INDEX node_external_id ON node (external_id)');
        $this->addSql('DROP INDEX node_url_pathx ON node_url');
        $this->addSql('ALTER TABLE node_url ADD node_id BIGINT DEFAULT NULL, ADD type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE node_url ADD CONSTRAINT FK_875367D5460D9FD7 FOREIGN KEY (node_id) REFERENCES node (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_875367D5460D9FD7 ON node_url (node_id)');
        $this->addSql('CREATE INDEX node_url_pathx ON node_url (service, locale, path)');
        $this->addSql('ALTER TABLE node_variable DROP FOREIGN KEY FK_72E09FD84DCCAC8');
        $this->addSql('ALTER TABLE node_variable CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX fk_72e09fd84dccac8 ON node_variable');
        $this->addSql('CREATE INDEX IDX_72E09FD84DCCAC8 ON node_variable (node_value)');
        $this->addSql('ALTER TABLE node_variable ADD CONSTRAINT FK_72E09FD84DCCAC8 FOREIGN KEY (node_value) REFERENCES node (id)');
        $this->addSql('ALTER TABLE setting DROP FOREIGN KEY FK_9F74B8984DCCAC8');
        $this->addSql('ALTER TABLE setting CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX fk_9f74b8984dccac8 ON setting');
        $this->addSql('CREATE INDEX IDX_9F74B8984DCCAC8 ON setting (node_value)');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT FK_9F74B8984DCCAC8 FOREIGN KEY (node_value) REFERENCES node (id)');
        $this->addSql("update node_url  inner join (select name, string_value, node_id from node_variable where name = 'url') as sub on (node_url.slug = sub.string_value) set node_url.node_id = sub.node_id ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE node_pos');
        $this->addSql('ALTER TABLE admin_users CHANGE password password VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(60) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE main_roles main_roles LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE file CHANGE id id CHAR(36) NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', CHANGE path path VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE repository repository VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE mime mime VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE meta meta LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE newsletter_content CHANGE locale locale VARCHAR(2) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE title title VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE content content LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX newsletetr_email_uniq ON newsletter_email');
        $this->addSql('ALTER TABLE newsletter_email CHANGE token token VARCHAR(100) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(200) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE locale locale VARCHAR(2) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX node_type_idx ON node');
        $this->addSql('DROP INDEX node_external_id ON node');
        $this->addSql('ALTER TABLE node ADD node_url_id BIGINT DEFAULT NULL, DROP meta, CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE locale locale VARCHAR(2) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE external_id external_id VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE node ADD CONSTRAINT FK_857FE84535DDCF42 FOREIGN KEY (node_url_id) REFERENCES node_url (id)');
        $this->addSql('CREATE INDEX IDX_857FE84535DDCF42 ON node (node_url_id)');
        $this->addSql('CREATE INDEX node_type_idx ON node (service, type(191), locale)');
        $this->addSql('CREATE INDEX node_external_id ON node (external_id(191))');
        $this->addSql('ALTER TABLE node_url DROP FOREIGN KEY FK_875367D5460D9FD7');
        $this->addSql('DROP INDEX IDX_875367D5460D9FD7 ON node_url');
        $this->addSql('DROP INDEX node_url_pathx ON node_url');
        $this->addSql('ALTER TABLE node_url DROP node_id, DROP type, CHANGE locale locale VARCHAR(2) NOT NULL COLLATE `ascii_general_ci`, CHANGE slug slug VARCHAR(255) NOT NULL COLLATE `ascii_general_ci`, CHANGE path path VARCHAR(1000) NOT NULL COLLATE `ascii_general_ci`');
        $this->addSql('CREATE INDEX node_url_pathx ON node_url (service, locale, path(767))');
        $this->addSql('ALTER TABLE node_variable DROP FOREIGN KEY FK_72E09FD84DCCAC8');
        $this->addSql('ALTER TABLE node_variable CHANGE name name VARCHAR(155) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE locale locale VARCHAR(2) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE file_value file_value CHAR(36) DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE string_value string_value VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE text_value text_value LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX idx_72e09fd84dccac8 ON node_variable');
        $this->addSql('CREATE INDEX FK_72E09FD84DCCAC8 ON node_variable (node_value)');
        $this->addSql('ALTER TABLE node_variable ADD CONSTRAINT FK_72E09FD84DCCAC8 FOREIGN KEY (node_value) REFERENCES node (id)');
        $this->addSql('ALTER TABLE setting DROP FOREIGN KEY FK_9F74B8984DCCAC8');
        $this->addSql('ALTER TABLE setting CHANGE name name VARCHAR(155) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE locale locale VARCHAR(2) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE file_value file_value CHAR(36) DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\', CHANGE type type VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE string_value string_value VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE text_value text_value LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('DROP INDEX idx_9f74b8984dccac8 ON setting');
        $this->addSql('CREATE INDEX FK_9F74B8984DCCAC8 ON setting (node_value)');
        $this->addSql('ALTER TABLE setting ADD CONSTRAINT FK_9F74B8984DCCAC8 FOREIGN KEY (node_value) REFERENCES node (id)');
    }
}
