<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240205124119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice (id INT AUTO_INCREMENT NOT NULL, validated_user_id INT NOT NULL, supplier_id INT DEFAULT NULL, doc VARCHAR(255) DEFAULT NULL, validated TINYINT(1) NOT NULL, validated_date DATETIME DEFAULT NULL, INDEX IDX_90651744B054B7D (validated_user_id), INDEX IDX_906517442ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE invoice_product (invoice_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_2193327E2989F1FD (invoice_id), INDEX IDX_2193327E4584665A (product_id), PRIMARY KEY(invoice_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744B054B7D FOREIGN KEY (validated_user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517442ADD6D8C FOREIGN KEY (supplier_id) REFERENCES budget_supplier (id)');
        $this->addSql('ALTER TABLE invoice_product ADD CONSTRAINT FK_2193327E2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE invoice_product ADD CONSTRAINT FK_2193327E4584665A FOREIGN KEY (product_id) REFERENCES budget_product (id) ON DELETE CASCADE');
        // $this->addSql('ALTER TABLE temp_company_contact_bad DROP FOREIGN KEY FK_5678A5F455B127A4');
        // $this->addSql('ALTER TABLE temp_company_contact_bad DROP FOREIGN KEY FK_5678A5F4979B1AD6');
        // $this->addSql('DROP TABLE personnes_de_contact_verhulst');
        // $this->addSql('DROP TABLE `personnes_de_contact_verhulst_2023.614`');
        // $this->addSql('DROP TABLE temp_company_contact_08012024');
        // $this->addSql('DROP TABLE temp_company_contact_20230614');
        // $this->addSql('DROP TABLE temp_company_contact_bad');
        // $this->addSql('DROP TABLE temp_company_contactbck');
        $this->addSql('ALTER TABLE company_contact CHANGE interests interests JSON DEFAULT NULL');
        // $this->addSql('ALTER TABLE temp_company_contact ADD CONSTRAINT FK_5678A5F4979B1AD6 FOREIGN KEY (company_id) REFERENCES temp_company (id)');
        // $this->addSql('ALTER TABLE temp_company_contact ADD CONSTRAINT FK_5678A5F455B127A4 FOREIGN KEY (added_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE TABLE personnes_de_contact_verhulst (noms VARCHAR(114) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, societe VARCHAR(90) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, prenom VARCHAR(24) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, nom VARCHAR(62) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, contact VARCHAR(32) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, langue VARCHAR(21) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, sexe VARCHAR(11) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, email VARCHAR(50) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, Tel VARCHAR(48) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, gsm VARCHAR(35) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        // $this->addSql('CREATE TABLE personnes_de_contact_verhulst_2023.614 (noms VARCHAR(114) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, societe VARCHAR(90) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, prenom VARCHAR(24) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, nom VARCHAR(62) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, contact VARCHAR(32) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, langue VARCHAR(21) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, sexe VARCHAR(11) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, email VARCHAR(50) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, Tel VARCHAR(48) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, gsm VARCHAR(35) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        // $this->addSql('CREATE TABLE temp_company_contact_08012024 (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, added_by_id INT DEFAULT NULL, firstname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lastname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lang VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, gsm VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, street VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, pc VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, fonction VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_5678A5F455B127A4 (added_by_id), INDEX IDX_5678A5F4979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        // $this->addSql('CREATE TABLE temp_company_contact_20230614 (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, added_by_id INT DEFAULT NULL, firstname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lastname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lang VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, gsm VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, street VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, pc VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, fonction VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_5678A5F455B127A4 (added_by_id), INDEX IDX_5678A5F4979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        // $this->addSql('CREATE TABLE temp_company_contact_bad (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, added_by_id INT DEFAULT NULL, firstname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lastname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lang VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, gsm VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, street VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, pc VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, fonction VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_5678A5F455B127A4 (added_by_id), INDEX IDX_5678A5F4979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        // $this->addSql('CREATE TABLE temp_company_contactbck (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, added_by_id INT DEFAULT NULL, firstname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lastname VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, lang VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, gsm VARCHAR(30) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, street VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, pc VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, city VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, country VARCHAR(2) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, fonction VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_5678A5F455B127A4 (added_by_id), INDEX IDX_5678A5F4979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        // $this->addSql('ALTER TABLE temp_company_contact_bad ADD CONSTRAINT FK_5678A5F455B127A4 FOREIGN KEY (added_by_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        // $this->addSql('ALTER TABLE temp_company_contact_bad ADD CONSTRAINT FK_5678A5F4979B1AD6 FOREIGN KEY (company_id) REFERENCES temp_company (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_90651744B054B7D');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517442ADD6D8C');
        $this->addSql('ALTER TABLE invoice_product DROP FOREIGN KEY FK_2193327E2989F1FD');
        $this->addSql('ALTER TABLE invoice_product DROP FOREIGN KEY FK_2193327E4584665A');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_product');
        $this->addSql('ALTER TABLE company_contact CHANGE interests interests LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        // $this->addSql('ALTER TABLE temp_company_contact DROP FOREIGN KEY FK_5678A5F4979B1AD6');
        // $this->addSql('ALTER TABLE temp_company_contact DROP FOREIGN KEY FK_5678A5F455B127A4');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
