<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240516205514 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fast_sales DROP FOREIGN KEY FK_1EE8D69619EB6921');
        $this->addSql('ALTER TABLE fast_sales DROP FOREIGN KEY FK_1EE8D696A76ED395');
        $this->addSql('DROP TABLE fast_sales');
        $this->addSql('ALTER TABLE sales ADD name VARCHAR(255) DEFAULT NULL, ADD po VARCHAR(255) DEFAULT NULL, ADD forecast_price NUMERIC(10, 2) DEFAULT NULL, ADD type VARCHAR(255) NOT NULL, ADD validate TINYINT(1) DEFAULT NULL, CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE percent_vr percent_vr NUMERIC(5, 2) NOT NULL, CHANGE percent_com percent_com NUMERIC(4, 2) NOT NULL, CHANGE invoiced invoiced TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fast_sales (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, client_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, po VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, forecast_budget NUMERIC(10, 2) DEFAULT NULL, final_budget NUMERIC(10, 2) DEFAULT NULL, com_sale NUMERIC(10, 2) NOT NULL, INDEX IDX_1EE8D69619EB6921 (client_id), INDEX IDX_1EE8D696A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE fast_sales ADD CONSTRAINT FK_1EE8D69619EB6921 FOREIGN KEY (client_id) REFERENCES company_contact (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE fast_sales ADD CONSTRAINT FK_1EE8D696A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE sales DROP name, DROP po, DROP forecast_price, DROP type, DROP validate, CHANGE price price NUMERIC(10, 2) NOT NULL, CHANGE percent_vr percent_vr NUMERIC(5, 2) DEFAULT NULL, CHANGE percent_com percent_com NUMERIC(4, 2) DEFAULT NULL, CHANGE invoiced invoiced TINYINT(1) NOT NULL');
    }
}
