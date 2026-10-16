<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261016092431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop the link between accounts and Redlist';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_88bdf3e9f3a19e8c');
        $this->addSql('ALTER TABLE app_user DROP redlist_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_user ADD redlist_id UUID DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_88bdf3e9f3a19e8c ON app_user (redlist_id)');
    }
}
