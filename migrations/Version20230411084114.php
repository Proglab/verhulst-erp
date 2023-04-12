<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230411084114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE temp_company (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, pc VARCHAR(20) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(2) NOT NULL, vat_number VARCHAR(30) DEFAULT NULL, address VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE temp_company_contact (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, added_by_id INT DEFAULT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, lang VARCHAR(2) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, gsm VARCHAR(30) DEFAULT NULL, street VARCHAR(255) DEFAULT NULL, pc VARCHAR(20) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, fonction VARCHAR(255) DEFAULT NULL, INDEX IDX_5678A5F4979B1AD6 (company_id), INDEX IDX_5678A5F455B127A4 (added_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE temp_company_contact ADD CONSTRAINT FK_5678A5F4979B1AD6 FOREIGN KEY (company_id) REFERENCES temp_company (id)');
        $this->addSql('ALTER TABLE temp_company_contact ADD CONSTRAINT FK_5678A5F455B127A4 FOREIGN KEY (added_by_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE temp_company_contact DROP FOREIGN KEY FK_5678A5F4979B1AD6');
        $this->addSql('ALTER TABLE temp_company_contact DROP FOREIGN KEY FK_5678A5F455B127A4');
        $this->addSql('DROP TABLE temp_company');
        $this->addSql('DROP TABLE temp_company_contact');
    }
}
