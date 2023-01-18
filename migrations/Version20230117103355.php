<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230117103355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales_product DROP FOREIGN KEY FK_DDB378CD4584665A');
        $this->addSql('ALTER TABLE sales_product DROP FOREIGN KEY FK_DDB378CDA4522A07');
        $this->addSql('DROP TABLE sales_product');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sales_product (sales_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_DDB378CD4584665A (product_id), INDEX IDX_DDB378CDA4522A07 (sales_id), PRIMARY KEY(sales_id, product_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sales_product ADD CONSTRAINT FK_DDB378CD4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_product ADD CONSTRAINT FK_DDB378CDA4522A07 FOREIGN KEY (sales_id) REFERENCES sales (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
