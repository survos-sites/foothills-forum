<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240218195814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE submission ADD location_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE submission ADD CONSTRAINT FK_DB055AF364D218E FOREIGN KEY (location_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_DB055AF364D218E ON submission (location_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE submission DROP CONSTRAINT FK_DB055AF364D218E');
        $this->addSql('DROP INDEX IDX_DB055AF364D218E');
        $this->addSql('ALTER TABLE submission DROP location_id');
    }
}
