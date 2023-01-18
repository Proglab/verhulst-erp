<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230117103123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commission (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, user_id INT NOT NULL, percent_com INT NOT NULL, INDEX IDX_1C6501584584665A (product_id), INDEX IDX_1C650158A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commission ADD CONSTRAINT FK_1C6501584584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE commission ADD CONSTRAINT FK_1C650158A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE product ADD percent_vr INT NOT NULL');
        $this->addSql('ALTER TABLE sales ADD percent_vr INT DEFAULT NULL, ADD percent_com INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commission DROP FOREIGN KEY FK_1C6501584584665A');
        $this->addSql('ALTER TABLE commission DROP FOREIGN KEY FK_1C650158A76ED395');
        $this->addSql('DROP TABLE commission');
        $this->addSql('ALTER TABLE product DROP percent_vr');
        $this->addSql('ALTER TABLE sales DROP percent_vr, DROP percent_com');
    }
}
