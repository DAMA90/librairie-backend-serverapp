<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314095714 extends AbstractMigration
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
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495570CE4D77');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955700047AD');
        $this->addSql('DROP TABLE reservation');
    }
}
