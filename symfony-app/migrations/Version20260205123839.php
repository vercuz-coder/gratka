<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260205123839 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE auth_tokens ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE likes ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE photos ALTER id DROP DEFAULT');
        $this->addSql('CREATE INDEX idx_photos_location ON photos (location)');
        $this->addSql('CREATE INDEX idx_photos_camera ON photos (camera)');
        $this->addSql('CREATE INDEX idx_photos_taken_at ON photos (taken_at)');
        $this->addSql('ALTER TABLE users ALTER id DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE users_id_seq');
        $this->addSql('SELECT setval(\'users_id_seq\', (SELECT MAX(id) FROM users))');
        $this->addSql('ALTER TABLE users ALTER id SET DEFAULT nextval(\'users_id_seq\')');
        $this->addSql('DROP INDEX idx_photos_location');
        $this->addSql('DROP INDEX idx_photos_camera');
        $this->addSql('DROP INDEX idx_photos_taken_at');
        $this->addSql('CREATE SEQUENCE photos_id_seq');
        $this->addSql('SELECT setval(\'photos_id_seq\', (SELECT MAX(id) FROM photos))');
        $this->addSql('ALTER TABLE photos ALTER id SET DEFAULT nextval(\'photos_id_seq\')');
        $this->addSql('CREATE SEQUENCE likes_id_seq');
        $this->addSql('SELECT setval(\'likes_id_seq\', (SELECT MAX(id) FROM likes))');
        $this->addSql('ALTER TABLE likes ALTER id SET DEFAULT nextval(\'likes_id_seq\')');
        $this->addSql('CREATE SEQUENCE auth_tokens_id_seq');
        $this->addSql('SELECT setval(\'auth_tokens_id_seq\', (SELECT MAX(id) FROM auth_tokens))');
        $this->addSql('ALTER TABLE auth_tokens ALTER id SET DEFAULT nextval(\'auth_tokens_id_seq\')');
    }
}
