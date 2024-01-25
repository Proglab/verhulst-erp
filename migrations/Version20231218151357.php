<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231218151357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company_contact_note (id INT AUTO_INCREMENT NOT NULL, company_contact_id INT NOT NULL, user_id INT NOT NULL, note LONGTEXT NOT NULL, created_dt DATETIME NOT NULL, INDEX IDX_198FB2255A2FCCDC (company_contact_id), INDEX IDX_198FB225A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company_contact_note ADD CONSTRAINT FK_198FB2255A2FCCDC FOREIGN KEY (company_contact_id) REFERENCES company_contact (id)');
        $this->addSql('ALTER TABLE company_contact_note ADD CONSTRAINT FK_198FB225A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_contact_note DROP FOREIGN KEY FK_198FB2255A2FCCDC');
        $this->addSql('ALTER TABLE company_contact_note DROP FOREIGN KEY FK_198FB225A76ED395');
        $this->addSql('DROP TABLE company_contact_note');
    }
}
