<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240613101338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_contact CHANGE firstname firstname VARCHAR(255) DEFAULT NULL, CHANGE lastname lastname VARCHAR(255) DEFAULT NULL, CHANGE lang lang VARCHAR(2) NOT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(30) DEFAULT NULL, CHANGE note note LONGTEXT DEFAULT NULL, CHANGE gsm gsm VARCHAR(30) DEFAULT NULL, CHANGE street street VARCHAR(255) DEFAULT NULL, CHANGE pc pc VARCHAR(20) DEFAULT NULL, CHANGE city city VARCHAR(255) DEFAULT NULL, CHANGE country country VARCHAR(2) DEFAULT NULL, CHANGE fonction fonction VARCHAR(255) DEFAULT NULL, CHANGE sex sex VARCHAR(1) DEFAULT NULL, CHANGE greeting greeting VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sales ADD percent_vr_eur NUMERIC(10, 2) DEFAULT NULL, ADD percent_com_type VARCHAR(255) NOT NULL, ADD percent_vr_type VARCHAR(255) NOT NULL, ADD percent_com_eur NUMERIC(10, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(180) NOT NULL, CHANGE password password VARCHAR(255) NOT NULL, CHANGE first_name first_name VARCHAR(255) NOT NULL, CHANGE last_name last_name VARCHAR(255) NOT NULL, CHANGE slug slug VARCHAR(255) NOT NULL, CHANGE locale locale VARCHAR(2) NOT NULL, CHANGE totpSecret totpSecret VARCHAR(255) DEFAULT NULL, CHANGE com com VARCHAR(255) DEFAULT NULL, CHANGE phone phone VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_contact CHANGE firstname firstname VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lastname lastname VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE lang lang VARCHAR(2) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE email email VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE phone phone VARCHAR(30) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE gsm gsm VARCHAR(30) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE street street VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE pc pc VARCHAR(20) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE city city VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE country country VARCHAR(2) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE fonction fonction VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE note note LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE sex sex VARCHAR(1) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE greeting greeting VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE sales DROP percent_vr_eur, DROP percent_com_type, DROP percent_vr_type, DROP percent_com_eur');
        $this->addSql('ALTER TABLE `user` CHANGE email email VARCHAR(180) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE password password VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE first_name first_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE last_name last_name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE slug slug VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE locale locale VARCHAR(2) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE totpSecret totpSecret VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE com com VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE phone phone VARCHAR(255) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
