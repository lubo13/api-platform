<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210124175235 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE schedule ADD vehicle_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE schedule ADD service_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FB545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5A3811FB545317D1 ON schedule (vehicle_id)');
        $this->addSql('CREATE INDEX IDX_5A3811FBED5CA9E6 ON schedule (service_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE schedule DROP CONSTRAINT FK_5A3811FB545317D1');
        $this->addSql('ALTER TABLE schedule DROP CONSTRAINT FK_5A3811FBED5CA9E6');
        $this->addSql('DROP INDEX IDX_5A3811FB545317D1');
        $this->addSql('DROP INDEX IDX_5A3811FBED5CA9E6');
        $this->addSql('ALTER TABLE schedule DROP vehicle_id');
        $this->addSql('ALTER TABLE schedule DROP service_id');
    }
}
