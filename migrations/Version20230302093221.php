<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230302093221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE thread ADD COLUMN slug VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__thread AS SELECT id, related_group_id, owner_id, title, content, created_at FROM thread');
        $this->addSql('DROP TABLE thread');
        $this->addSql('CREATE TABLE thread (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, related_group_id INTEGER NOT NULL, owner_id INTEGER NOT NULL, title VARCHAR(50) NOT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_31204C8358D797EA FOREIGN KEY (related_group_id) REFERENCES "group" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_31204C837E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO thread (id, related_group_id, owner_id, title, content, created_at) SELECT id, related_group_id, owner_id, title, content, created_at FROM __temp__thread');
        $this->addSql('DROP TABLE __temp__thread');
        $this->addSql('CREATE INDEX IDX_31204C8358D797EA ON thread (related_group_id)');
        $this->addSql('CREATE INDEX IDX_31204C837E3C61F9 ON thread (owner_id)');
    }
}
