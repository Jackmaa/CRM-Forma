<?php

declare (strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250702085252 extends AbstractMigration {
    public function getDescription(): string {
        return '';
    }

// migrations/Version20250702085252.php

    public function up(Schema $schema): void {
        // 1. Ajout des colonnes avec DEFAULT TRUE
        $this->addSql('ALTER TABLE users ADD is_active BOOLEAN DEFAULT TRUE NOT NULL');
        $this->addSql('ALTER TABLE users ADD force_password_reset BOOLEAN DEFAULT TRUE NOT NULL');

        // 2. (Optionnel si tu as mis NOT NULL + DEFAULT)
        //    Mais tu peux forcer la mise à TRUE sur les anciennes lignes au cas où :
        $this->addSql('UPDATE users SET is_active = TRUE WHERE is_active IS NULL');
        $this->addSql('UPDATE users SET force_password_reset = TRUE WHERE force_password_reset IS NULL');

        // 3. Retirer la valeur par défaut si tu ne veux pas la conserver
        $this->addSql('ALTER TABLE users ALTER COLUMN is_active DROP DEFAULT');
        $this->addSql('ALTER TABLE users ALTER COLUMN force_password_reset DROP DEFAULT');
    }

    public function down(Schema $schema): void {
        $this->addSql('ALTER TABLE users DROP COLUMN force_password_reset');
        $this->addSql('ALTER TABLE users DROP COLUMN is_active');
    }

}
