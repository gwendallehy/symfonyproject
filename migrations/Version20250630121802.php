<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250630121802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE outgoing CHANGE date_begin date_begin DATETIME NOT NULL, CHANGE date_subscription_limit date_subscription_limit DATETIME NOT NULL');
        $this->addSql('ALTER TABLE outgoing ADD CONSTRAINT FK_65F3D350D5E86FF FOREIGN KEY (etat_id) REFERENCES etat (id)');
        $this->addSql('ALTER TABLE outgoing ADD CONSTRAINT FK_65F3D350876C4DDA FOREIGN KEY (organizer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE outgoing ADD CONSTRAINT FK_65F3D350F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE outgoing ADD CONSTRAINT FK_65F3D350DA6A219 FOREIGN KEY (place_id) REFERENCES place (id)');
        $this->addSql('ALTER TABLE outgoing_user ADD CONSTRAINT FK_734C2D85845CDE9F FOREIGN KEY (outgoing_id) REFERENCES outgoing (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE outgoing_user ADD CONSTRAINT FK_734C2D85A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE place ADD CONSTRAINT FK_741D53CD8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE outgoing DROP FOREIGN KEY FK_65F3D350D5E86FF');
        $this->addSql('ALTER TABLE outgoing DROP FOREIGN KEY FK_65F3D350876C4DDA');
        $this->addSql('ALTER TABLE outgoing DROP FOREIGN KEY FK_65F3D350F6BD1646');
        $this->addSql('ALTER TABLE outgoing DROP FOREIGN KEY FK_65F3D350DA6A219');
        $this->addSql('ALTER TABLE outgoing CHANGE date_begin date_begin VARCHAR(255) NOT NULL, CHANGE date_subscription_limit date_subscription_limit VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE place DROP FOREIGN KEY FK_741D53CD8BAC62AF');
        $this->addSql('ALTER TABLE outgoing_user DROP FOREIGN KEY FK_734C2D85845CDE9F');
        $this->addSql('ALTER TABLE outgoing_user DROP FOREIGN KEY FK_734C2D85A76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F6BD1646');
    }
}
