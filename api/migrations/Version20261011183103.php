<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261011183103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE observation (id UUID NOT NULL, observed_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, count INT DEFAULT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, author_id UUID NOT NULL, species_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX observation_author_observed_at_idx ON observation (author_id, observed_at)');
        $this->addSql('CREATE INDEX IDX_C576DBE0F675F31B ON observation (author_id)');
        $this->addSql('CREATE INDEX IDX_C576DBE0B2A1D860 ON observation (species_id)');
        $this->addSql('CREATE TABLE species (id UUID NOT NULL, inat_id INT NOT NULL, scientific_name VARCHAR(255) NOT NULL, vernacular_name VARCHAR(255) DEFAULT NULL, kingdom VARCHAR(100) NOT NULL, phylum VARCHAR(100) DEFAULT NULL, taxon_class VARCHAR(100) DEFAULT NULL, taxon_order VARCHAR(100) DEFAULT NULL, family VARCHAR(100) DEFAULT NULL, category VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A50FF71268B3CD76 ON species (inat_id)');
        $this->addSql('CREATE INDEX species_category_idx ON species (category)');
        $this->addSql('ALTER TABLE observation ADD CONSTRAINT FK_C576DBE0F675F31B FOREIGN KEY (author_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE observation ADD CONSTRAINT FK_C576DBE0B2A1D860 FOREIGN KEY (species_id) REFERENCES species (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE observation DROP CONSTRAINT FK_C576DBE0F675F31B');
        $this->addSql('ALTER TABLE observation DROP CONSTRAINT FK_C576DBE0B2A1D860');
        $this->addSql('DROP TABLE observation');
        $this->addSql('DROP TABLE species');
    }
}
