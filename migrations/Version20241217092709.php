<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241217092709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cadeau (id INT AUTO_INCREMENT NOT NULL, production_id INT DEFAULT NULL, client_id INT DEFAULT NULL, quantite INT NOT NULL, description VARCHAR(255) NOT NULL, date DATETIME NOT NULL, INDEX IDX_3D213460ECC6147F (production_id), INDEX IDX_3D21346019EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cadeau ADD CONSTRAINT FK_3D213460ECC6147F FOREIGN KEY (production_id) REFERENCES production (id)');
        $this->addSql('ALTER TABLE cadeau ADD CONSTRAINT FK_3D21346019EB6921 FOREIGN KEY (client_id) REFERENCES client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cadeau DROP FOREIGN KEY FK_3D213460ECC6147F');
        $this->addSql('ALTER TABLE cadeau DROP FOREIGN KEY FK_3D21346019EB6921');
        $this->addSql('DROP TABLE cadeau');
    }
}
