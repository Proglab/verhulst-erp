<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230310104458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE company SET street = "" WHERE street IS NULL');
        $this->addSql('UPDATE company SET pc = "" WHERE pc IS NULL');
        $this->addSql('UPDATE company SET city = "" WHERE city IS NULL');
        $this->addSql('UPDATE company SET country = "BE" WHERE country IS NULL');
        $this->addSql('UPDATE sales SET price = 0 WHERE price IS NULL');
        $this->addSql('ALTER TABLE company CHANGE street street VARCHAR(255) NOT NULL, CHANGE pc pc VARCHAR(20) NOT NULL, CHANGE city city VARCHAR(255) NOT NULL, CHANGE country country VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE sales CHANGE price price NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE com com VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sales CHANGE price price NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE company CHANGE street street VARCHAR(255) DEFAULT NULL, CHANGE pc pc VARCHAR(20) DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT NULL, CHANGE country country VARCHAR(2) DEFAULT NULL');
        $this->addSql('ALTER TABLE `user` CHANGE com com VARCHAR(255) NOT NULL');
    }
}
