<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261011164128 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_user ADD redlist_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ALTER password DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_88BDF3E9F3A19E8C ON app_user (redlist_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_88BDF3E9F3A19E8C');
        $this->addSql('ALTER TABLE app_user DROP redlist_id');
        $this->addSql('ALTER TABLE app_user ALTER password SET NOT NULL');
    }
}
