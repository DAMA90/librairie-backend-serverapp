<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250314101935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables pour la base de données';
    }

    public function up(Schema $schema): void
    {
        // Vérification et création de la table "categorie"
        if (!$schema->hasTable('categorie')) {
            $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        // Vérification et création de la table "commentaire"
        if (!$schema->hasTable('commentaire')) {
            $this->addSql('CREATE TABLE commentaire (id INT AUTO_INCREMENT NOT NULL, utilisateur_id INT DEFAULT NULL, fk_id_livre INT NOT NULL, description VARCHAR(255) NOT NULL, date DATE NOT NULL, is_approved TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_67F068BCFB88E14F (utilisateur_id), INDEX IDX_67F068BC70CE4D77 (fk_id_livre), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        // Vérification et création de la table "emprunt"
        if (!$schema->hasTable('emprunt')) {
            $this->addSql('CREATE TABLE emprunt (id INT AUTO_INCREMENT NOT NULL, fk_id_livre INT NOT NULL, fk_id_utilisateur INT NOT NULL, date_emprunt DATETIME NOT NULL, date_retour_prevue DATETIME NOT NULL, date_retour_reelle DATETIME DEFAULT NULL, INDEX IDX_364071D770CE4D77 (fk_id_livre), INDEX IDX_364071D7700047AD (fk_id_utilisateur), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        // Vérification et création de la table "livre"
        if (!$schema->hasTable('livre')) {
            $this->addSql('CREATE TABLE livre (id INT AUTO_INCREMENT NOT NULL, fk_id_categorie INT NOT NULL, titre VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, date DATE NOT NULL, image VARCHAR(255) DEFAULT NULL, INDEX IDX_AC634F99E895680A (fk_id_categorie), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        // Vérification et création de la table "reservation"
        if (!$schema->hasTable('reservation')) {
            $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, fk_id_livre INT NOT NULL, fk_id_utilisateur INT NOT NULL, date_reservation DATETIME NOT NULL, date_expiration DATETIME NOT NULL, statut VARCHAR(50) NOT NULL, INDEX IDX_42C8495570CE4D77 (fk_id_livre), INDEX IDX_42C84955700047AD (fk_id_utilisateur), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        // Vérification et création de la table "utilisateur"
        if (!$schema->hasTable('utilisateur')) {
            $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mot_depasse VARCHAR(255) NOT NULL, roles JSON NOT NULL, reset_token VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        // Vérification et création de la table "statut_livre" (évite l'erreur existante)
        if (!$schema->hasTable('statut_livre')) {
            $this->addSql('CREATE TABLE statut_livre (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        }

        // Ajout des contraintes de clé étrangère
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BCFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE commentaire ADD CONSTRAINT FK_67F068BC70CE4D77 FOREIGN KEY (fk_id_livre) REFERENCES livre (id)');
        $this->addSql('ALTER TABLE emprunt ADD CONSTRAINT FK_364071D770CE4D77 FOREIGN KEY (fk_id_livre) REFERENCES livre (id)');
        $this->addSql('ALTER TABLE emprunt ADD CONSTRAINT FK_364071D7700047AD FOREIGN KEY (fk_id_utilisateur) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE livre ADD CONSTRAINT FK_AC634F99E895680A FOREIGN KEY (fk_id_categorie) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495570CE4D77 FOREIGN KEY (fk_id_livre) REFERENCES livre (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955700047AD FOREIGN KEY (fk_id_utilisateur) REFERENCES utilisateur (id)');
    }

    public function down(Schema $schema): void
    {
        // Suppression des contraintes de clé étrangère avant de supprimer les tables
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BCFB88E14F');
        $this->addSql('ALTER TABLE commentaire DROP FOREIGN KEY FK_67F068BC70CE4D77');
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_364071D770CE4D77');
        $this->addSql('ALTER TABLE emprunt DROP FOREIGN KEY FK_364071D7700047AD');
        $this->addSql('ALTER TABLE livre DROP FOREIGN KEY FK_AC634F99E895680A');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495570CE4D77');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955700047AD');

        // Suppression des tables si elles existent
        $this->addSql('DROP TABLE IF EXISTS categorie');
        $this->addSql('DROP TABLE IF EXISTS commentaire');
        $this->addSql('DROP TABLE IF EXISTS emprunt');
        $this->addSql('DROP TABLE IF EXISTS livre');
        $this->addSql('DROP TABLE IF EXISTS reservation');
        $this->addSql('DROP TABLE IF EXISTS utilisateur');
        $this->addSql('DROP TABLE IF EXISTS statut_livre');
    }
}
