<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230116163501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, street VARCHAR(255) DEFAULT NULL, number VARCHAR(20) DEFAULT NULL, box VARCHAR(20) DEFAULT NULL, pc VARCHAR(20) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, country VARCHAR(2) DEFAULT NULL, vat_number VARCHAR(30) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_contact (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, lang VARCHAR(2) NOT NULL, email VARCHAR(255) NOT NULL, INDEX IDX_6C30FCEF979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, name VARCHAR(255) NOT NULL, ca NUMERIC(10, 2) DEFAULT NULL, type VARCHAR(255) NOT NULL, date DATETIME DEFAULT NULL, INDEX IDX_D34A04AD166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, INDEX IDX_6B817044A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_company_contact (sales_id INT NOT NULL, company_contact_id INT NOT NULL, INDEX IDX_1ABE10BBA4522A07 (sales_id), INDEX IDX_1ABE10BB5A2FCCDC (company_contact_id), PRIMARY KEY(sales_id, company_contact_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company_contact ADD CONSTRAINT FK_6C30FCEF979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE sales ADD CONSTRAINT FK_6B817044A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE sales_company_contact ADD CONSTRAINT FK_1ABE10BBA4522A07 FOREIGN KEY (sales_id) REFERENCES sales (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_company_contact ADD CONSTRAINT FK_1ABE10BB5A2FCCDC FOREIGN KEY (company_contact_id) REFERENCES company_contact (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_contact DROP FOREIGN KEY FK_6C30FCEF979B1AD6');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD166D1F9C');
        $this->addSql('ALTER TABLE sales DROP FOREIGN KEY FK_6B817044A76ED395');
        $this->addSql('ALTER TABLE sales_company_contact DROP FOREIGN KEY FK_1ABE10BBA4522A07');
        $this->addSql('ALTER TABLE sales_company_contact DROP FOREIGN KEY FK_1ABE10BB5A2FCCDC');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE company_contact');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE sales');
        $this->addSql('DROP TABLE sales_company_contact');
    }
}
