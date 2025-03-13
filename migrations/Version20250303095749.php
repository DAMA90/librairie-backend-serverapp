<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303095749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, fk_id_livre INT NOT NULL, fk_id_utilisateur INT NOT NULL, date_reservation DATETIME NOT NULL, date_expiration DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, INDEX IDX_42C8495570CE4D77 (fk_id_livre), INDEX IDX_42C84955700047AD (fk_id_utilisateur), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495570CE4D77 FOREIGN KEY (fk_id_livre) REFERENCES livre (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955700047AD FOREIGN KEY (fk_id_utilisateur) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE emprunt RENAME INDEX fk_emprunt_livre TO IDX_364071D770CE4D77');
        $this->addSql('ALTER TABLE emprunt RENAME INDEX fk_emprunt_utilisateur TO IDX_364071D7700047AD');
        $this->addSql('ALTER TABLE utilisateur ADD reset_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495570CE4D77');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955700047AD');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('ALTER TABLE emprunt RENAME INDEX idx_364071d770ce4d77 TO FK_EMPRUNT_LIVRE');
        $this->addSql('ALTER TABLE emprunt RENAME INDEX idx_364071d7700047ad TO FK_EMPRUNT_UTILISATEUR');
        $this->addSql('DROP INDEX UNIQ_1D1C63B3E7927C74 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP reset_token');
    }
}
