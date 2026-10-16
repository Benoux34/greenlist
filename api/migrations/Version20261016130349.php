<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261016130349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store friendships and friend invitations';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE friend_invitation (id UUID NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, inviter_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5F5A95A6B3BC57DA ON friend_invitation (token_hash)');
        $this->addSql('CREATE INDEX IDX_5F5A95A6B79F4F04 ON friend_invitation (inviter_id)');
        $this->addSql('CREATE TABLE friendship (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, user_id UUID NOT NULL, friend_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX friendship_user_friend_unique ON friendship (user_id, friend_id)');
        $this->addSql('CREATE INDEX IDX_7234A45FA76ED395 ON friendship (user_id)');
        $this->addSql('CREATE INDEX IDX_7234A45F6A5458E8 ON friendship (friend_id)');
        $this->addSql('ALTER TABLE friend_invitation ADD CONSTRAINT FK_5F5A95A6B79F4F04 FOREIGN KEY (inviter_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_7234A45FA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE friendship ADD CONSTRAINT FK_7234A45F6A5458E8 FOREIGN KEY (friend_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE friend_invitation DROP CONSTRAINT FK_5F5A95A6B79F4F04');
        $this->addSql('ALTER TABLE friendship DROP CONSTRAINT FK_7234A45FA76ED395');
        $this->addSql('ALTER TABLE friendship DROP CONSTRAINT FK_7234A45F6A5458E8');
        $this->addSql('DROP TABLE friend_invitation');
        $this->addSql('DROP TABLE friendship');
    }
}
