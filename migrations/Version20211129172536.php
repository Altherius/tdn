<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211129172536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE football_match ADD winner_id INT DEFAULT NULL, ADD loser_id INT DEFAULT NULL, ADD penalties TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE football_match ADD CONSTRAINT FK_8CE33ACE5DFCD4B8 FOREIGN KEY (winner_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE football_match ADD CONSTRAINT FK_8CE33ACE1BCAA5F6 FOREIGN KEY (loser_id) REFERENCES team (id)');
        $this->addSql('CREATE INDEX IDX_8CE33ACE5DFCD4B8 ON football_match (winner_id)');
        $this->addSql('CREATE INDEX IDX_8CE33ACE1BCAA5F6 ON football_match (loser_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE football_match DROP FOREIGN KEY FK_8CE33ACE5DFCD4B8');
        $this->addSql('ALTER TABLE football_match DROP FOREIGN KEY FK_8CE33ACE1BCAA5F6');
        $this->addSql('DROP INDEX IDX_8CE33ACE5DFCD4B8 ON football_match');
        $this->addSql('DROP INDEX IDX_8CE33ACE1BCAA5F6 ON football_match');
        $this->addSql('ALTER TABLE football_match DROP winner_id, DROP loser_id, DROP penalties');
    }
}
