<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230303170645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD percent_tv NUMERIC(5, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD com VARCHAR(255) NOT NULL, DROP freelance');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP percent_tv');
        $this->addSql('ALTER TABLE `user` ADD freelance TINYINT(1) NOT NULL, DROP com');
    }
}
