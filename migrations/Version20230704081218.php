<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230704081218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sales_bdc (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, creation_date DATE NOT NULL, INDEX IDX_942CF707A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sales_bdc_sales (sales_bdc_id INT NOT NULL, sales_id INT NOT NULL, INDEX IDX_16562087A2BC8B8 (sales_bdc_id), INDEX IDX_16562087A4522A07 (sales_id), PRIMARY KEY(sales_bdc_id, sales_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sales_bdc ADD CONSTRAINT FK_942CF707A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE sales_bdc_sales ADD CONSTRAINT FK_16562087A2BC8B8 FOREIGN KEY (sales_bdc_id) REFERENCES sales_bdc (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sales_bdc_sales ADD CONSTRAINT FK_16562087A4522A07 FOREIGN KEY (sales_id) REFERENCES sales (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales_bdc DROP FOREIGN KEY FK_942CF707A76ED395');
        $this->addSql('ALTER TABLE sales_bdc_sales DROP FOREIGN KEY FK_16562087A2BC8B8');
        $this->addSql('ALTER TABLE sales_bdc_sales DROP FOREIGN KEY FK_16562087A4522A07');
        $this->addSql('DROP TABLE sales_bdc');
        $this->addSql('DROP TABLE sales_bdc_sales');
    }
}
