<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250630113757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, postal_code INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE etat (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE outgoing (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, date_begin VARCHAR(255) NOT NULL, duration VARCHAR(255) NOT NULL, date_subscription_limit VARCHAR(255) NOT NULL, nb_subscription_max INT NOT NULL, description LONGTEXT DEFAULT NULL, etat_id INT DEFAULT NULL, organizer_id INT DEFAULT NULL, site_id INT DEFAULT NULL, place_id INT DEFAULT NULL, INDEX IDX_65F3D350D5E86FF (etat_id), INDEX IDX_65F3D350876C4DDA (organizer_id), INDEX IDX_65F3D350F6BD1646 (site_id), INDEX IDX_65F3D350DA6A219 (place_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE outgoing_user (outgoing_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_734C2D85845CDE9F (outgoing_id), INDEX IDX_734C2D85A76ED395 (user_id), PRIMARY KEY(outgoing_id, user_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE place (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, street VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, city_id INT DEFAULT NULL, INDEX IDX_741D53CD8BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE site (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, pseudo VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(100) NOT NULL, lastname VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL, phone VARCHAR(20) NOT NULL, administrator TINYINT(1) NOT NULL, active TINYINT(1) NOT NULL, picture VARCHAR(100) DEFAULT NULL, site_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D64986CC499D (pseudo), INDEX IDX_8D93D649F6BD1646 (site_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
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
        $this->addSql('ALTER TABLE outgoing_user DROP FOREIGN KEY FK_734C2D85845CDE9F');
        $this->addSql('ALTER TABLE outgoing_user DROP FOREIGN KEY FK_734C2D85A76ED395');
        $this->addSql('ALTER TABLE place DROP FOREIGN KEY FK_741D53CD8BAC62AF');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F6BD1646');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE etat');
        $this->addSql('DROP TABLE outgoing');
        $this->addSql('DROP TABLE outgoing_user');
        $this->addSql('DROP TABLE place');
        $this->addSql('DROP TABLE site');
        $this->addSql('DROP TABLE user');
    }
}
