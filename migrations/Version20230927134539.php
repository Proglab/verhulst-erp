<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230927134539 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE budget_product_reference ADD vat_id INT NOT NULL');
        $this->addSql('ALTER TABLE budget_product_reference ADD CONSTRAINT FK_D5A91CD3B5B63A6B FOREIGN KEY (vat_id) REFERENCES budget_vat (id)');
        $this->addSql('CREATE INDEX IDX_D5A91CD3B5B63A6B ON budget_product_reference (vat_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE budget_product_reference DROP FOREIGN KEY FK_D5A91CD3B5B63A6B');
        $this->addSql('DROP INDEX IDX_D5A91CD3B5B63A6B ON budget_product_reference');
        $this->addSql('ALTER TABLE budget_product_reference DROP vat_id');
    }
}
