<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230309123707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sales ADD contact_id INT NOT NULL');
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales_company_contact DROP FOREIGN KEY FK_1ABE10BB5A2FCCDC');
        $this->addSql('ALTER TABLE sales_company_contact DROP FOREIGN KEY FK_1ABE10BBA4522A07');
        $this->addSql('UPDATE `sales` JOIN sales_company_contact ON sales.id = sales_company_contact.sales_id SET contact_id = company_contact_id ');
        $this->addSql('ALTER TABLE sales ADD CONSTRAINT FK_6B817044E7A1254A FOREIGN KEY (contact_id) REFERENCES company_contact (id)');
        $this->addSql('CREATE INDEX IDX_6B817044E7A1254A ON sales (contact_id)');
        $this->addSql('DROP TABLE sales_company_contact');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sales_company_contact (sales_id INT NOT NULL, company_contact_id INT NOT NULL, INDEX IDX_1ABE10BB5A2FCCDC (company_contact_id), INDEX IDX_1ABE10BBA4522A07 (sales_id), PRIMARY KEY(sales_id, company_contact_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sales_company_contact ADD CONSTRAINT FK_1ABE10BB5A2FCCDC FOREIGN KEY (company_contact_id) REFERENCES company_contact (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_company_contact ADD CONSTRAINT FK_1ABE10BBA4522A07 FOREIGN KEY (sales_id) REFERENCES sales (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales DROP FOREIGN KEY FK_6B817044E7A1254A');
        $this->addSql('DROP INDEX IDX_6B817044E7A1254A ON sales');
        $this->addSql('ALTER TABLE sales DROP contact_id');
    }
}
