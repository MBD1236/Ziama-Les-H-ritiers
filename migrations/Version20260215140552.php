<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215140552 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cadeau (id INT AUTO_INCREMENT NOT NULL, produit_id INT DEFAULT NULL, client_id INT DEFAULT NULL, quantite INT NOT NULL, description VARCHAR(255) NOT NULL, date DATETIME NOT NULL, INDEX IDX_3D213460F347EFB (produit_id), INDEX IDX_3D21346019EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE caisse (id INT AUTO_INCREMENT NOT NULL, montant INT NOT NULL, date DATETIME NOT NULL, type VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(500) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, type_client VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE depense (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, montant INT NOT NULL, description VARCHAR(255) NOT NULL, date_depense DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, vente_id INT DEFAULT NULL, code_facture VARCHAR(255) NOT NULL, montant_regle INT NOT NULL, montant_restant INT NOT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_FE8664107DC7170A (vente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fournisseur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, produit_fourni VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ligne_vente (id INT AUTO_INCREMENT NOT NULL, vente_id INT DEFAULT NULL, produit_id INT DEFAULT NULL, quantite INT NOT NULL, prix_unitaire INT NOT NULL, total_ligne INT NOT NULL, INDEX IDX_8B26C07C7DC7170A (vente_id), INDEX IDX_8B26C07CF347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE livraison (id INT AUTO_INCREMENT NOT NULL, vente_id INT DEFAULT NULL, user_id INT DEFAULT NULL, date_livraison DATETIME NOT NULL, INDEX IDX_A60C9F1F7DC7170A (vente_id), INDEX IDX_A60C9F1FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mouvement_stock (id INT AUTO_INCREMENT NOT NULL, produit_id INT DEFAULT NULL, type_mouvement VARCHAR(255) NOT NULL, quantite INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_61E2C8EBF347EFB (produit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, categorie_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prix_achat INT NOT NULL, prix_vente INT NOT NULL, quantite_stock INT NOT NULL, seuil_alerte INT NOT NULL, INDEX IDX_29A5EC27BCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reglement_facture (id INT AUTO_INCREMENT NOT NULL, facture_id INT DEFAULT NULL, mode_reglement VARCHAR(255) NOT NULL, montant_regle INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_4C4769AA7F2DEE08 (facture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction_fournisseur (id INT AUTO_INCREMENT NOT NULL, fournisseur_id INT DEFAULT NULL, montant INT NOT NULL, mode_paiement VARCHAR(255) NOT NULL, date_transaction DATETIME NOT NULL, description VARCHAR(255) NOT NULL, INDEX IDX_8A17DF1F670C757F (fournisseur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vente (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, user_id INT DEFAULT NULL, code_vente VARCHAR(255) NOT NULL, mode_paiement VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, date_vente DATETIME NOT NULL, INDEX IDX_888A2A4C19EB6921 (client_id), INDEX IDX_888A2A4CA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cadeau ADD CONSTRAINT FK_3D213460F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE cadeau ADD CONSTRAINT FK_3D21346019EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE8664107DC7170A FOREIGN KEY (vente_id) REFERENCES vente (id)');
        $this->addSql('ALTER TABLE ligne_vente ADD CONSTRAINT FK_8B26C07C7DC7170A FOREIGN KEY (vente_id) REFERENCES vente (id)');
        $this->addSql('ALTER TABLE ligne_vente ADD CONSTRAINT FK_8B26C07CF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1F7DC7170A FOREIGN KEY (vente_id) REFERENCES vente (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mouvement_stock ADD CONSTRAINT FK_61E2C8EBF347EFB FOREIGN KEY (produit_id) REFERENCES produit (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE reglement_facture ADD CONSTRAINT FK_4C4769AA7F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        $this->addSql('ALTER TABLE transaction_fournisseur ADD CONSTRAINT FK_8A17DF1F670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id)');
        $this->addSql('ALTER TABLE vente ADD CONSTRAINT FK_888A2A4C19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE vente ADD CONSTRAINT FK_888A2A4CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cadeau DROP FOREIGN KEY FK_3D213460F347EFB');
        $this->addSql('ALTER TABLE cadeau DROP FOREIGN KEY FK_3D21346019EB6921');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE8664107DC7170A');
        $this->addSql('ALTER TABLE ligne_vente DROP FOREIGN KEY FK_8B26C07C7DC7170A');
        $this->addSql('ALTER TABLE ligne_vente DROP FOREIGN KEY FK_8B26C07CF347EFB');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1F7DC7170A');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1FA76ED395');
        $this->addSql('ALTER TABLE mouvement_stock DROP FOREIGN KEY FK_61E2C8EBF347EFB');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27BCF5E72D');
        $this->addSql('ALTER TABLE reglement_facture DROP FOREIGN KEY FK_4C4769AA7F2DEE08');
        $this->addSql('ALTER TABLE transaction_fournisseur DROP FOREIGN KEY FK_8A17DF1F670C757F');
        $this->addSql('ALTER TABLE vente DROP FOREIGN KEY FK_888A2A4C19EB6921');
        $this->addSql('ALTER TABLE vente DROP FOREIGN KEY FK_888A2A4CA76ED395');
        $this->addSql('DROP TABLE cadeau');
        $this->addSql('DROP TABLE caisse');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE depense');
        $this->addSql('DROP TABLE facture');
        $this->addSql('DROP TABLE fournisseur');
        $this->addSql('DROP TABLE ligne_vente');
        $this->addSql('DROP TABLE livraison');
        $this->addSql('DROP TABLE mouvement_stock');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE reglement_facture');
        $this->addSql('DROP TABLE transaction_fournisseur');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vente');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
