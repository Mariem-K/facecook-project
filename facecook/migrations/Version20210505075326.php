<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210505075326 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE composition DROP FOREIGN KEY FK_C7F4347933FE08C');
        $this->addSql('DROP TABLE composition');
        $this->addSql('DROP TABLE ingredient');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE composition (id INT AUTO_INCREMENT NOT NULL, recipe_id INT NOT NULL, ingredient_id INT NOT NULL, quantity DOUBLE PRECISION NOT NULL, INDEX IDX_C7F434759D8A214 (recipe_id), INDEX IDX_C7F4347933FE08C (ingredient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE ingredient (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, unit VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE composition ADD CONSTRAINT FK_C7F434759D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE composition ADD CONSTRAINT FK_C7F4347933FE08C FOREIGN KEY (ingredient_id) REFERENCES ingredient (id)');
    }
}
