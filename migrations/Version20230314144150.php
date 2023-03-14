<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230314144150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD date_end DATETIME DEFAULT NULL, CHANGE date date_begin DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `product` SET `date_end` = `date_begin` WHERE `type` = "event"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD date DATETIME DEFAULT NULL, DROP date_begin, DROP date_end');
    }
}
