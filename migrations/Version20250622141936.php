<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250622141936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE centres (id SERIAL NOT NULL, nom VARCHAR(255) NOT NULL, siret VARCHAR(14) NOT NULL, adresse TEXT NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, logo_url VARCHAR(500) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_3BA7EA5226E94372 ON centres (siret)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE formations (id SERIAL NOT NULL, centre_id INT NOT NULL, responsable_id INT NOT NULL, titre VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, thematique VARCHAR(255) NOT NULL, niveau VARCHAR(50) NOT NULL, modalites JSON NOT NULL, prerequis TEXT NOT NULL, objectifs JSON NOT NULL, duree INT NOT NULL, tarif NUMERIC(10, 2) NOT NULL, description TEXT NOT NULL, published BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_40902137989D9B62 ON formations (slug)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_40902137463CD7C3 ON formations (centre_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_4090213753C59D72 ON formations (responsable_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN formations.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE sessions (id SERIAL NOT NULL, formation_id INT NOT NULL, formateur_responsable_id INT NOT NULL, centre_id INT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, lieu VARCHAR(255) NOT NULL, modalite VARCHAR(50) NOT NULL, remarques TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9A609D135200282E ON sessions (formation_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9A609D13FC103F87 ON sessions (formateur_responsable_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9A609D13463CD7C3 ON sessions (centre_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id SERIAL NOT NULL, centre_id INT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1483A5E9463CD7C3 ON users (centre_id)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN users.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.available_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.delivered_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
                BEGIN
                    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formations ADD CONSTRAINT FK_40902137463CD7C3 FOREIGN KEY (centre_id) REFERENCES centres (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formations ADD CONSTRAINT FK_4090213753C59D72 FOREIGN KEY (responsable_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions ADD CONSTRAINT FK_9A609D135200282E FOREIGN KEY (formation_id) REFERENCES formations (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions ADD CONSTRAINT FK_9A609D13FC103F87 FOREIGN KEY (formateur_responsable_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions ADD CONSTRAINT FK_9A609D13463CD7C3 FOREIGN KEY (centre_id) REFERENCES centres (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD CONSTRAINT FK_1483A5E9463CD7C3 FOREIGN KEY (centre_id) REFERENCES centres (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formations DROP CONSTRAINT FK_40902137463CD7C3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE formations DROP CONSTRAINT FK_4090213753C59D72
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions DROP CONSTRAINT FK_9A609D135200282E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions DROP CONSTRAINT FK_9A609D13FC103F87
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions DROP CONSTRAINT FK_9A609D13463CD7C3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP CONSTRAINT FK_1483A5E9463CD7C3
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE centres
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE formations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE sessions
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
