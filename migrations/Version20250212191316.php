<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212191316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE community_members (community_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_6165BBACFDA7B0BF (community_id), INDEX IDX_6165BBACA76ED395 (user_id), PRIMARY KEY(community_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE community_members ADD CONSTRAINT FK_6165BBACFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE community_members ADD CONSTRAINT FK_6165BBACA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE community ADD avatar VARCHAR(255) DEFAULT NULL, ADD banner_image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE community_members DROP FOREIGN KEY FK_6165BBACFDA7B0BF');
        $this->addSql('ALTER TABLE community_members DROP FOREIGN KEY FK_6165BBACA76ED395');
        $this->addSql('DROP TABLE community_members');
        $this->addSql('ALTER TABLE community DROP avatar, DROP banner_image');
    }
}
