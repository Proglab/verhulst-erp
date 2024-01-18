<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240118154854 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_contact CHANGE interests interests JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE temp_company_contact ADD CONSTRAINT FK_5678A5F4979B1AD6 FOREIGN KEY (company_id) REFERENCES temp_company (id)');
        $this->addSql('ALTER TABLE temp_company_contact ADD CONSTRAINT FK_5678A5F455B127A4 FOREIGN KEY (added_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_contact CHANGE interests interests LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE temp_company_contact DROP FOREIGN KEY FK_5678A5F4979B1AD6');
        $this->addSql('ALTER TABLE temp_company_contact DROP FOREIGN KEY FK_5678A5F455B127A4');
        $this->addSql('ALTER TABLE `user` CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
