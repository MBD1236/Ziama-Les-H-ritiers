<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260217130230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_vente DROP prix_unitaire');
        $this->addSql('ALTER TABLE vente DROP mode_paiement');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ligne_vente ADD prix_unitaire INT NOT NULL');
        $this->addSql('ALTER TABLE vente ADD mode_paiement VARCHAR(255) NOT NULL');
    }
}
