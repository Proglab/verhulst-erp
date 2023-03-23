<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230322170531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE todo (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, date_reminder DATETIME NOT NULL, todo LONGTEXT NOT NULL, date_done DATETIME DEFAULT NULL, done TINYINT(1) NOT NULL, INDEX IDX_5A0EB6A019EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE todo ADD CONSTRAINT FK_5A0EB6A019EB6921 FOREIGN KEY (client_id) REFERENCES company_contact (id)');
        $this->addSql('ALTER TABLE company_contact CHANGE interests interests JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE todo DROP FOREIGN KEY FK_5A0EB6A019EB6921');
        $this->addSql('DROP TABLE todo');
        $this->addSql('ALTER TABLE company_contact CHANGE interests interests JSON NOT NULL');
    }
}
