<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230904144157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE budget_supplier (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_vat (id INT AUTO_INCREMENT NOT NULL, percent NUMERIC(5, 2) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE budget_product ADD vat_id INT NOT NULL, ADD supplier_id INT NOT NULL');
        $this->addSql('ALTER TABLE budget_product ADD CONSTRAINT FK_11C94E4CB5B63A6B FOREIGN KEY (vat_id) REFERENCES budget_vat (id)');
        $this->addSql('ALTER TABLE budget_product ADD CONSTRAINT FK_11C94E4C2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES budget_supplier (id)');
        $this->addSql('CREATE INDEX IDX_11C94E4CB5B63A6B ON budget_product (vat_id)');
        $this->addSql('CREATE INDEX IDX_11C94E4C2ADD6D8C ON budget_product (supplier_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE budget_product DROP FOREIGN KEY FK_11C94E4C2ADD6D8C');
        $this->addSql('ALTER TABLE budget_product DROP FOREIGN KEY FK_11C94E4CB5B63A6B');
        $this->addSql('DROP TABLE budget_supplier');
        $this->addSql('DROP TABLE budget_vat');
        $this->addSql('DROP INDEX IDX_11C94E4CB5B63A6B ON budget_product');
        $this->addSql('DROP INDEX IDX_11C94E4C2ADD6D8C ON budget_product');
        $this->addSql('ALTER TABLE budget_product DROP vat_id, DROP supplier_id');
    }
}
