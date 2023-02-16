<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230216155206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_contact ADD added_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE company_contact ADD CONSTRAINT FK_6C30FCEF55B127A4 FOREIGN KEY (added_by_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_6C30FCEF55B127A4 ON company_contact (added_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_contact DROP FOREIGN KEY FK_6C30FCEF55B127A4');
        $this->addSql('DROP INDEX IDX_6C30FCEF55B127A4 ON company_contact');
        $this->addSql('ALTER TABLE company_contact DROP added_by_id');
    }
}
