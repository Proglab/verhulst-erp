<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230201172817 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commission CHANGE percent_com percent_com NUMERIC(5, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE product CHANGE percent_vr percent_vr NUMERIC(5, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE sales CHANGE percent_vr percent_vr NUMERIC(5, 2) DEFAULT NULL, CHANGE percent_com percent_com NUMERIC(5, 2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commission CHANGE percent_com percent_com DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE sales CHANGE percent_vr percent_vr DOUBLE PRECISION DEFAULT NULL, CHANGE percent_com percent_com DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE product CHANGE percent_vr percent_vr DOUBLE PRECISION DEFAULT NULL');
    }
}
