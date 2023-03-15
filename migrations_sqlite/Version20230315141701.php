<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230315141701 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commission (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, product_id INTEGER NOT NULL, user_id INTEGER NOT NULL, percent_com NUMERIC(5, 2) DEFAULT NULL, CONSTRAINT FK_1C6501584584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_1C650158A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_1C6501584584665A ON commission (product_id)');
        $this->addSql('CREATE INDEX IDX_1C650158A76ED395 ON commission (user_id)');
        $this->addSql('CREATE TABLE company (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, pc VARCHAR(20) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(2) NOT NULL, vat_number VARCHAR(30) DEFAULT NULL, note CLOB DEFAULT NULL, billing_street VARCHAR(255) DEFAULT NULL, billing_pc VARCHAR(20) DEFAULT NULL, billing_city VARCHAR(255) DEFAULT NULL, billing_country VARCHAR(2) DEFAULT NULL, billing_mail VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE company_contact (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, company_id INTEGER NOT NULL, added_by_id INTEGER DEFAULT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, lang VARCHAR(2) NOT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(30) DEFAULT NULL, gsm VARCHAR(30) DEFAULT NULL, note CLOB DEFAULT NULL, CONSTRAINT FK_6C30FCEF979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6C30FCEF55B127A4 FOREIGN KEY (added_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6C30FCEF979B1AD6 ON company_contact (company_id)');
        $this->addSql('CREATE INDEX IDX_6C30FCEF55B127A4 ON company_contact (added_by_id)');
        $this->addSql('CREATE TABLE css (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, content CLOB NOT NULL)');
        $this->addSql('CREATE TABLE product (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, project_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, percent_vr NUMERIC(5, 2) DEFAULT NULL, doc VARCHAR(255) DEFAULT NULL, description CLOB DEFAULT NULL, percent_freelance NUMERIC(5, 2) DEFAULT NULL, percent_salarie NUMERIC(5, 2) DEFAULT NULL, percent_tv NUMERIC(5, 2) DEFAULT NULL, date_begin DATETIME DEFAULT NULL, date_end DATETIME DEFAULT NULL, type VARCHAR(255) NOT NULL, ca NUMERIC(10, 2) DEFAULT NULL, quantity_max INTEGER DEFAULT NULL, CONSTRAINT FK_D34A04AD166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_D34A04AD166D1F9C ON product (project_id)');
        $this->addSql('CREATE TABLE project (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, archive BOOLEAN NOT NULL, date_begin DATE DEFAULT NULL, date_end DATE DEFAULT NULL)');
        $this->addSql('CREATE TABLE resend_confirmation_email_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, hashed_token VARCHAR(255) NOT NULL, requested_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , expires_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_6221B49EA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6221B49EA76ED395 ON resend_confirmation_email_request (user_id)');
        $this->addSql('CREATE TABLE reset_password_request (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , expires_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_7CE748AA76ED395 ON reset_password_request (user_id)');
        $this->addSql('CREATE TABLE sales (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, user_id INTEGER NOT NULL, product_id INTEGER NOT NULL, contact_id INTEGER NOT NULL, price NUMERIC(10, 2) NOT NULL, percent_vr NUMERIC(4, 2) DEFAULT NULL, percent_com NUMERIC(4, 2) DEFAULT NULL, date DATE NOT NULL, quantity INTEGER NOT NULL, discount NUMERIC(8, 2) DEFAULT NULL, invoiced BOOLEAN NOT NULL, invoiced_dt DATETIME DEFAULT NULL, CONSTRAINT FK_6B817044A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6B8170444584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6B817044E7A1254A FOREIGN KEY (contact_id) REFERENCES company_contact (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6B817044A76ED395 ON sales (user_id)');
        $this->addSql('CREATE INDEX IDX_6B8170444584665A ON sales (product_id)');
        $this->addSql('CREATE INDEX IDX_6B817044E7A1254A ON sales (contact_id)');
        $this->addSql('CREATE TABLE "user" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, locale VARCHAR(2) NOT NULL, totpSecret VARCHAR(255) DEFAULT NULL, is_totp_enabled BOOLEAN NOT NULL, com VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, enabled BOOLEAN NOT NULL, verified BOOLEAN NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE commission');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE company_contact');
        $this->addSql('DROP TABLE css');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE resend_confirmation_email_request');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE sales');
        $this->addSql('DROP TABLE "user"');
    }
}
