<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240217121900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE article_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE author_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE event_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE school_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE sport_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE submission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE team_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE users_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE article (id INT NOT NULL, slug TEXT DEFAULT NULL, headline TEXT NOT NULL, url TEXT NOT NULL, byline TEXT DEFAULT NULL, section VARCHAR(255) DEFAULT NULL, uuid UUID NOT NULL, subheadline TEXT DEFAULT NULL, sections JSONB DEFAULT NULL, keywords JSONB DEFAULT NULL, tags JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX article_idx ON article (uuid)');
        $this->addSql('CREATE TABLE author (id INT NOT NULL, uuid UUID NOT NULL, avatar TEXT DEFAULT NULL, profile TEXT DEFAULT NULL, full_name TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE author_article (author_id INT NOT NULL, article_id INT NOT NULL, PRIMARY KEY(author_id, article_id))');
        $this->addSql('CREATE INDEX IDX_47009125F675F31B ON author_article (author_id)');
        $this->addSql('CREATE INDEX IDX_470091257294869C ON author_article (article_id)');
        $this->addSql('CREATE TABLE event (id INT NOT NULL, sport_id INT NOT NULL, event_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, opponent VARCHAR(255) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, score VARCHAR(255) DEFAULT NULL, summary TEXT DEFAULT NULL, section VARCHAR(255) DEFAULT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, r_school_id INT DEFAULT NULL, submission_count INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7AC78BCF8 ON event (sport_id)');
        $this->addSql('CREATE TABLE school (id INT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE sport (id INT NOT NULL, school_id INT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1A85EFD2C32A47EE ON sport (school_id)');
        $this->addSql('CREATE TABLE submission (id INT NOT NULL, event_id INT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, image_size INT DEFAULT NULL, marking VARCHAR(32) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DB055AF371F7E88B ON submission (event_id)');
        $this->addSql('CREATE TABLE team (id INT NOT NULL, school_id INT NOT NULL, sport_id INT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, section VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C4E0A61FC32A47EE ON team (school_id)');
        $this->addSql('CREATE INDEX IDX_C4E0A61FAC78BCF8 ON team (sport_id)');
        $this->addSql('CREATE TABLE users (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, identifiers JSONB DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE author_article ADD CONSTRAINT FK_47009125F675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE author_article ADD CONSTRAINT FK_470091257294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7AC78BCF8 FOREIGN KEY (sport_id) REFERENCES sport (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sport ADD CONSTRAINT FK_1A85EFD2C32A47EE FOREIGN KEY (school_id) REFERENCES school (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE submission ADD CONSTRAINT FK_DB055AF371F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FC32A47EE FOREIGN KEY (school_id) REFERENCES school (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FAC78BCF8 FOREIGN KEY (sport_id) REFERENCES sport (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE article_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE author_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE event_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE school_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE sport_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE submission_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE team_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE users_id_seq CASCADE');
        $this->addSql('ALTER TABLE author_article DROP CONSTRAINT FK_47009125F675F31B');
        $this->addSql('ALTER TABLE author_article DROP CONSTRAINT FK_470091257294869C');
        $this->addSql('ALTER TABLE event DROP CONSTRAINT FK_3BAE0AA7AC78BCF8');
        $this->addSql('ALTER TABLE sport DROP CONSTRAINT FK_1A85EFD2C32A47EE');
        $this->addSql('ALTER TABLE submission DROP CONSTRAINT FK_DB055AF371F7E88B');
        $this->addSql('ALTER TABLE team DROP CONSTRAINT FK_C4E0A61FC32A47EE');
        $this->addSql('ALTER TABLE team DROP CONSTRAINT FK_C4E0A61FAC78BCF8');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE author_article');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE school');
        $this->addSql('DROP TABLE sport');
        $this->addSql('DROP TABLE submission');
        $this->addSql('DROP TABLE team');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
