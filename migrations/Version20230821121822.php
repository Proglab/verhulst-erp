<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230821121822 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE budget_budget (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_2747B1C771F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_category (id INT AUTO_INCREMENT NOT NULL, budget_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_D183486536ABA6B8 (budget_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_category_reference (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_event (id INT AUTO_INCREMENT NOT NULL, admin_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_50BF9F51642B8210 (admin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE event_user (event_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_92589AE271F7E88B (event_id), INDEX IDX_92589AE2A76ED395 (user_id), PRIMARY KEY(event_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_product (id INT AUTO_INCREMENT NOT NULL, sub_category_id INT NOT NULL, title VARCHAR(255) NOT NULL, quantity NUMERIC(10, 2) NOT NULL, price NUMERIC(10, 2) NOT NULL, INDEX IDX_11C94E4CF7BFE87C (sub_category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_subcategory (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_7BD6E21712469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE budget_subcategory_reference (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_1C9020B812469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE budget_budget ADD CONSTRAINT FK_2747B1C771F7E88B FOREIGN KEY (event_id) REFERENCES budget_event (id)');
        $this->addSql('ALTER TABLE budget_category ADD CONSTRAINT FK_D183486536ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget_budget (id)');
        $this->addSql('ALTER TABLE budget_event ADD CONSTRAINT FK_50BF9F51642B8210 FOREIGN KEY (admin_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_92589AE271F7E88B FOREIGN KEY (event_id) REFERENCES budget_event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_user ADD CONSTRAINT FK_92589AE2A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE budget_product ADD CONSTRAINT FK_11C94E4CF7BFE87C FOREIGN KEY (sub_category_id) REFERENCES budget_subcategory (id)');
        $this->addSql('ALTER TABLE budget_subcategory ADD CONSTRAINT FK_7BD6E21712469DE2 FOREIGN KEY (category_id) REFERENCES budget_category (id)');
        $this->addSql('ALTER TABLE budget_subcategory_reference ADD CONSTRAINT FK_1C9020B812469DE2 FOREIGN KEY (category_id) REFERENCES budget_category_reference (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE budget_budget DROP FOREIGN KEY FK_2747B1C771F7E88B');
        $this->addSql('ALTER TABLE budget_category DROP FOREIGN KEY FK_D183486536ABA6B8');
        $this->addSql('ALTER TABLE budget_event DROP FOREIGN KEY FK_50BF9F51642B8210');
        $this->addSql('ALTER TABLE event_user DROP FOREIGN KEY FK_92589AE271F7E88B');
        $this->addSql('ALTER TABLE event_user DROP FOREIGN KEY FK_92589AE2A76ED395');
        $this->addSql('ALTER TABLE budget_product DROP FOREIGN KEY FK_11C94E4CF7BFE87C');
        $this->addSql('ALTER TABLE budget_subcategory DROP FOREIGN KEY FK_7BD6E21712469DE2');
        $this->addSql('ALTER TABLE budget_subcategory_reference DROP FOREIGN KEY FK_1C9020B812469DE2');
        $this->addSql('DROP TABLE budget_budget');
        $this->addSql('DROP TABLE budget_category');
        $this->addSql('DROP TABLE budget_category_reference');
        $this->addSql('DROP TABLE budget_event');
        $this->addSql('DROP TABLE event_user');
        $this->addSql('DROP TABLE budget_product');
        $this->addSql('DROP TABLE budget_subcategory');
        $this->addSql('DROP TABLE budget_subcategory_reference');
    }
}
