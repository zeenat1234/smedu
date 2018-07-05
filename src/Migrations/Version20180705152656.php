<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180705152656 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(32) NOT NULL, CHANGE email email VARCHAR(254) NOT NULL, CHANGE password password VARCHAR(32) NOT NULL, CHANGE usertype usertype VARCHAR(16) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE username username LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE email email TINYTEXT NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE password password LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci, CHANGE usertype usertype LONGTEXT NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
