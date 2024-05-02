<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240502020006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_movies (user_id INT NOT NULL, movies_id INT NOT NULL, INDEX IDX_A34CF60DA76ED395 (user_id), INDEX IDX_A34CF60D53F590A4 (movies_id), PRIMARY KEY(user_id, movies_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_movies ADD CONSTRAINT FK_A34CF60DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_movies ADD CONSTRAINT FK_A34CF60D53F590A4 FOREIGN KEY (movies_id) REFERENCES movies (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_movies DROP FOREIGN KEY FK_A34CF60DA76ED395');
        $this->addSql('ALTER TABLE user_movies DROP FOREIGN KEY FK_A34CF60D53F590A4');
        $this->addSql('DROP TABLE user_movies');
    }
}
