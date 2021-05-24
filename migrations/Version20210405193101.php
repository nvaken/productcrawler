<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210405193101 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) DEFAULT NULL, code_type VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, brand VARCHAR(255) DEFAULT NULL, seller VARCHAR(255) DEFAULT NULL, url VARCHAR(255) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article_price_entry (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, price DOUBLE PRECISION NOT NULL, price_currency VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL, INDEX IDX_9A6BFF777294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article_price_entry ADD CONSTRAINT FK_9A6BFF777294869C FOREIGN KEY (article_id) REFERENCES article (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_price_entry DROP FOREIGN KEY FK_9A6BFF777294869C');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_price_entry');
    }
}
