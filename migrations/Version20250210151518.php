<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250210151518 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE community (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, creator_id INT DEFAULT NULL, INDEX IDX_1B60403361220EA6 (creator_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE community ADD CONSTRAINT FK_1B60403361220EA6 FOREIGN KEY (creator_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE discussion ADD title VARCHAR(255) NOT NULL, ADD is_draft TINYINT(1) NOT NULL, ADD tags JSON DEFAULT NULL, ADD community_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE discussion ADD CONSTRAINT FK_C0B9F90FFDA7B0BF FOREIGN KEY (community_id) REFERENCES community (id)');
        $this->addSql('CREATE INDEX IDX_C0B9F90FFDA7B0BF ON discussion (community_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE community DROP FOREIGN KEY FK_1B60403361220EA6');
        $this->addSql('DROP TABLE community');
        $this->addSql('ALTER TABLE discussion DROP FOREIGN KEY FK_C0B9F90FFDA7B0BF');
        $this->addSql('DROP INDEX IDX_C0B9F90FFDA7B0BF ON discussion');
        $this->addSql('ALTER TABLE discussion DROP title, DROP is_draft, DROP tags, DROP community_id');
    }
}
