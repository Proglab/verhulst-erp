<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230202130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD updated_at DATETIME DEFAULT NULL, ADD file_original_name VARCHAR(255) DEFAULT NULL, ADD file_mime_type VARCHAR(255) DEFAULT NULL, ADD file_size INT DEFAULT NULL, ADD file_dimensions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE pdf_filename file_name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD pdf_filename VARCHAR(255) DEFAULT NULL, DROP updated_at, DROP file_name, DROP file_original_name, DROP file_mime_type, DROP file_size, DROP file_dimensions');
    }
}
