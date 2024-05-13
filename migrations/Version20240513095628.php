<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240513095628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fast_sales (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, client_id INT NOT NULL, name VARCHAR(255) NOT NULL, po VARCHAR(255) NOT NULL, forecast_budget NUMERIC(10, 2) DEFAULT NULL, final_budget NUMERIC(10, 2) DEFAULT NULL, com_sale NUMERIC(10, 2) NOT NULL, INDEX IDX_1EE8D696A76ED395 (user_id), INDEX IDX_1EE8D69619EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fast_sales ADD CONSTRAINT FK_1EE8D696A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE fast_sales ADD CONSTRAINT FK_1EE8D69619EB6921 FOREIGN KEY (client_id) REFERENCES company_contact (id)');
        $this->addSql('ALTER TABLE product CHANGE percent_vr percent_vr NUMERIC(17, 14) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mika (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(37) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, email VARCHAR(45) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, lang VARCHAR(6) CHARACTER SET utf8mb3 DEFAULT NULL COLLATE `utf8mb3_general_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb3 COLLATE `utf8mb3_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE fast_sales DROP FOREIGN KEY FK_1EE8D696A76ED395');
        $this->addSql('ALTER TABLE fast_sales DROP FOREIGN KEY FK_1EE8D69619EB6921');
        $this->addSql('DROP TABLE fast_sales');
        $this->addSql('ALTER TABLE product CHANGE percent_vr percent_vr NUMERIC(17, 14) DEFAULT NULL');
    }
}
