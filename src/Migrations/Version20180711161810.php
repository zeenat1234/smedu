<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180711161810 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE school_unit ADD CONSTRAINT FK_9D93D3BE444E1AE8 FOREIGN KEY (schoolyear_id) REFERENCES school_year (id)');
        $this->addSql('CREATE INDEX IDX_9D93D3BE444E1AE8 ON school_unit (schoolyear_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE school_unit DROP FOREIGN KEY FK_9D93D3BE444E1AE8');
        $this->addSql('DROP INDEX IDX_9D93D3BE444E1AE8 ON school_unit');
    }
}
