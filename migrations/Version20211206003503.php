<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211206003503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE football_match ADD penalties_winner_id INT DEFAULT NULL, DROP penalties');
        $this->addSql('ALTER TABLE football_match ADD CONSTRAINT FK_8CE33ACE5DAA40B6 FOREIGN KEY (penalties_winner_id) REFERENCES team (id)');
        $this->addSql('CREATE INDEX IDX_8CE33ACE5DAA40B6 ON football_match (penalties_winner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE football_match DROP FOREIGN KEY FK_8CE33ACE5DAA40B6');
        $this->addSql('DROP INDEX IDX_8CE33ACE5DAA40B6 ON football_match');
        $this->addSql('ALTER TABLE football_match ADD penalties TINYINT(1) NOT NULL, DROP penalties_winner_id');
    }
}
