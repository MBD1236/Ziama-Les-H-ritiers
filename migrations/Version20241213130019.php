<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213130019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bobine (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, quantite_stock INT NOT NULL, prix_unitaire INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE caisse (id INT AUTO_INCREMENT NOT NULL, montant INT NOT NULL, date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, type_client VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, client_id INT DEFAULT NULL, production_id INT DEFAULT NULL, user_id INT DEFAULT NULL, code_commande VARCHAR(255) NOT NULL, quantite INT NOT NULL, montant_total INT NOT NULL, mode_paiement VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, date_commande DATETIME NOT NULL, INDEX IDX_6EEAA67D19EB6921 (client_id), INDEX IDX_6EEAA67DECC6147F (production_id), INDEX IDX_6EEAA67DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE depense (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, montant INT NOT NULL, description VARCHAR(255) NOT NULL, date_depense DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE facture (id INT AUTO_INCREMENT NOT NULL, commande_id INT DEFAULT NULL, code_facture VARCHAR(255) NOT NULL, montant_regle INT NOT NULL, montant_restant INT NOT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_FE86641082EA2E54 (commande_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fournisseur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, telephone VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, produit_fourni VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE livraison (id INT AUTO_INCREMENT NOT NULL, commande_id INT DEFAULT NULL, user_id INT DEFAULT NULL, date_livraison DATETIME NOT NULL, INDEX IDX_A60C9F1F82EA2E54 (commande_id), INDEX IDX_A60C9F1FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE mouvement_bobine (id INT AUTO_INCREMENT NOT NULL, bobine_id INT DEFAULT NULL, type_mouvement VARCHAR(255) NOT NULL, quantite INT NOT NULL, prix_unitaire INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_85537D811D7FDEFA (bobine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE production (id INT AUTO_INCREMENT NOT NULL, bobine_id INT DEFAULT NULL, quantite_utilisee INT NOT NULL, nombre_pack INT NOT NULL, date_production DATETIME NOT NULL, INDEX IDX_D3EDB1E01D7FDEFA (bobine_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE production_employe (id INT AUTO_INCREMENT NOT NULL, production_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_ED115E64ECC6147F (production_id), INDEX IDX_ED115E64A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reglement_facture (id INT AUTO_INCREMENT NOT NULL, facture_id INT DEFAULT NULL, mode_reglement VARCHAR(255) NOT NULL, montant_regle INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_4C4769AA7F2DEE08 (facture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction_fournisseur (id INT AUTO_INCREMENT NOT NULL, fournisseur_id INT DEFAULT NULL, montant INT NOT NULL, mode_paiement VARCHAR(255) NOT NULL, date_transaction DATETIME NOT NULL, description VARCHAR(255) NOT NULL, INDEX IDX_8A17DF1F670C757F (fournisseur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67D19EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DECC6147F FOREIGN KEY (production_id) REFERENCES production (id)');
        $this->addSql('ALTER TABLE commande ADD CONSTRAINT FK_6EEAA67DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE facture ADD CONSTRAINT FK_FE86641082EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1F82EA2E54 FOREIGN KEY (commande_id) REFERENCES commande (id)');
        $this->addSql('ALTER TABLE livraison ADD CONSTRAINT FK_A60C9F1FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE mouvement_bobine ADD CONSTRAINT FK_85537D811D7FDEFA FOREIGN KEY (bobine_id) REFERENCES bobine (id)');
        $this->addSql('ALTER TABLE production ADD CONSTRAINT FK_D3EDB1E01D7FDEFA FOREIGN KEY (bobine_id) REFERENCES bobine (id)');
        $this->addSql('ALTER TABLE production_employe ADD CONSTRAINT FK_ED115E64ECC6147F FOREIGN KEY (production_id) REFERENCES production (id)');
        $this->addSql('ALTER TABLE production_employe ADD CONSTRAINT FK_ED115E64A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reglement_facture ADD CONSTRAINT FK_4C4769AA7F2DEE08 FOREIGN KEY (facture_id) REFERENCES facture (id)');
        $this->addSql('ALTER TABLE transaction_fournisseur ADD CONSTRAINT FK_8A17DF1F670C757F FOREIGN KEY (fournisseur_id) REFERENCES fournisseur (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67D19EB6921');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DECC6147F');
        $this->addSql('ALTER TABLE commande DROP FOREIGN KEY FK_6EEAA67DA76ED395');
        $this->addSql('ALTER TABLE facture DROP FOREIGN KEY FK_FE86641082EA2E54');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1F82EA2E54');
        $this->addSql('ALTER TABLE livraison DROP FOREIGN KEY FK_A60C9F1FA76ED395');
        $this->addSql('ALTER TABLE mouvement_bobine DROP FOREIGN KEY FK_85537D811D7FDEFA');
        $this->addSql('ALTER TABLE production DROP FOREIGN KEY FK_D3EDB1E01D7FDEFA');
        $this->addSql('ALTER TABLE production_employe DROP FOREIGN KEY FK_ED115E64ECC6147F');
        $this->addSql('ALTER TABLE production_employe DROP FOREIGN KEY FK_ED115E64A76ED395');
        $this->addSql('ALTER TABLE reglement_facture DROP FOREIGN KEY FK_4C4769AA7F2DEE08');
        $this->addSql('ALTER TABLE transaction_fournisseur DROP FOREIGN KEY FK_8A17DF1F670C757F');
        $this->addSql('DROP TABLE bobine');
        $this->addSql('DROP TABLE caisse');
        $this->addSql('DROP TABLE client');
        $this->addSql('DROP TABLE commande');
        $this->addSql('DROP TABLE depense');
        $this->addSql('DROP TABLE facture');
        $this->addSql('DROP TABLE fournisseur');
        $this->addSql('DROP TABLE livraison');
        $this->addSql('DROP TABLE mouvement_bobine');
        $this->addSql('DROP TABLE production');
        $this->addSql('DROP TABLE production_employe');
        $this->addSql('DROP TABLE reglement_facture');
        $this->addSql('DROP TABLE transaction_fournisseur');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
