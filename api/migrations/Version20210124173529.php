<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210124173529 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE schedule_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE service_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE vehicle_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE schedule (id INT NOT NULL, datetime TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE service (id INT NOT NULL, working_time_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, working_time_end TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, discriminator VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE service_carwash (id INT NOT NULL, vehicle_capacity INT NOT NULL, coffee_shop BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE service_service_station (id INT NOT NULL, roadside_assistance BOOLEAN NOT NULL, free_diagnostic BOOLEAN NOT NULL, express_service BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE service_tire_shop (id INT NOT NULL, vehicle_capacity INT NOT NULL, coffee_shop BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE vehicle (id INT NOT NULL, trade_mark VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, discriminator VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE service_carwash ADD CONSTRAINT FK_508A4EA7BF396750 FOREIGN KEY (id) REFERENCES service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_service_station ADD CONSTRAINT FK_72BCDAC9BF396750 FOREIGN KEY (id) REFERENCES service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service_tire_shop ADD CONSTRAINT FK_C8D093E2BF396750 FOREIGN KEY (id) REFERENCES service (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE service_carwash DROP CONSTRAINT FK_508A4EA7BF396750');
        $this->addSql('ALTER TABLE service_service_station DROP CONSTRAINT FK_72BCDAC9BF396750');
        $this->addSql('ALTER TABLE service_tire_shop DROP CONSTRAINT FK_C8D093E2BF396750');
        $this->addSql('DROP SEQUENCE schedule_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE service_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE vehicle_id_seq CASCADE');
        $this->addSql('DROP TABLE schedule');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE service_carwash');
        $this->addSql('DROP TABLE service_service_station');
        $this->addSql('DROP TABLE service_tire_shop');
        $this->addSql('DROP TABLE vehicle');
    }
}
