<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240516231352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales CHANGE product_id product_id INT DEFAULT NULL');
        $this->addSql('UPDATE sales
            JOIN product on (sales.product_id = product.id)
            JOIN project on (product.project_id = project.id)
            SET sales.validate = 1, sales.type = \'normal\', sales.name = CONCAT(project.name, \' || \', product.name)
            WHERE sales.product_id IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales CHANGE product_id product_id INT NOT NULL');
    }
}
