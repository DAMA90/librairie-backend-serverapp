<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250130192023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE statut_livre (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commentaire ADD fk_id_livre INT NOT NULL');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC70CE4D77 FOREIGN KEY (fk_id_livre) REFERENCES livre (id)');
        $this->addSql('CREATE INDEX IDX_67F068BC70CE4D77 ON commentaire (fk_id_livre)');
        $this->addSql('ALTER TABLE reservation CHANGE date_reservation date_reservation DATETIME NOT NULL');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495570CE4D77 FOREIGN KEY (fk_id_livre) REFERENCES livre (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955700047AD FOREIGN KEY (fk_id_utilisateur) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE statut_livre');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC70CE4D77');
        $this->addSql('DROP INDEX IDX_67F068BC70CE4D77 ON commentaire');
        $this->addSql('ALTER TABLE commentaire DROP fk_id_livre');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495570CE4D77');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955700047AD');
        $this->addSql('ALTER TABLE reservation CHANGE date_reservation date_reservation DATETIME DEFAULT CURRENT_TIMESTAMP');
    }
}
