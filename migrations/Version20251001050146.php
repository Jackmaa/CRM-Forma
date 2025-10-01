<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251001050146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE document (id SERIAL NOT NULL, centre_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, type VARCHAR(20) NOT NULL, number VARCHAR(50) NOT NULL, status VARCHAR(20) NOT NULL, file_path VARCHAR(255) DEFAULT NULL, snapshot JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_D8698A7696901F54 ON document (number)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D8698A76463CD7C3 ON document (centre_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D8698A76B03A8386 ON document (created_by_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_doc_type_number ON document (type, number)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN document.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE document ADD CONSTRAINT FK_D8698A76463CD7C3 FOREIGN KEY (centre_id) REFERENCES centres (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE document ADD CONSTRAINT FK_D8698A76B03A8386 FOREIGN KEY (created_by_id) REFERENCES users (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE document DROP CONSTRAINT FK_D8698A76463CD7C3
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE document DROP CONSTRAINT FK_D8698A76B03A8386
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE document
        SQL);
    }
}
