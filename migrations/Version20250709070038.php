<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250709070038 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE session_participants (session_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(session_id, user_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BFF4CD13613FECDF ON session_participants (session_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BFF4CD13A76ED395 ON session_participants (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE session_formateurs (session_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(session_id, user_id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1D16EE2F613FECDF ON session_formateurs (session_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_1D16EE2FA76ED395 ON session_formateurs (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_participants ADD CONSTRAINT FK_BFF4CD13613FECDF FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_participants ADD CONSTRAINT FK_BFF4CD13A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_formateurs ADD CONSTRAINT FK_1D16EE2F613FECDF FOREIGN KEY (session_id) REFERENCES sessions (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_formateurs ADD CONSTRAINT FK_1D16EE2FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions ADD statut VARCHAR(20) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions ALTER date_debut TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions ALTER date_fin TYPE TIMESTAMP(0) WITHOUT TIME ZONE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_participants DROP CONSTRAINT FK_BFF4CD13613FECDF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_participants DROP CONSTRAINT FK_BFF4CD13A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_formateurs DROP CONSTRAINT FK_1D16EE2F613FECDF
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE session_formateurs DROP CONSTRAINT FK_1D16EE2FA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE session_participants
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE session_formateurs
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions DROP statut
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions ALTER date_debut TYPE DATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE sessions ALTER date_fin TYPE DATE
        SQL);
    }
}
