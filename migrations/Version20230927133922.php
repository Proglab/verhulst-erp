<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230927133922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE budget_product_reference (id INT AUTO_INCREMENT NOT NULL, sub_category_id INT NOT NULL, title VARCHAR(255) NOT NULL, INDEX IDX_D5A91CD3F7BFE87C (sub_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE budget_product_reference ADD CONSTRAINT FK_D5A91CD3F7BFE87C FOREIGN KEY (sub_category_id) REFERENCES budget_subcategory_reference (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE budget_product_reference DROP FOREIGN KEY FK_D5A91CD3F7BFE87C');
        $this->addSql('DROP TABLE budget_product_reference');
    }
}
