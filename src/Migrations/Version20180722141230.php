<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20180722141230 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE enrollment (id INT AUTO_INCREMENT NOT NULL, id_parent_id INT NOT NULL, id_child_id INT NOT NULL, id_unit_id INT NOT NULL, id_service_id INT NOT NULL, enroll_date DATETIME NOT NULL, notes VARCHAR(512) NOT NULL, INDEX IDX_DBDCD7E1F24F7657 (id_parent_id), INDEX IDX_DBDCD7E18DB92E8E (id_child_id), INDEX IDX_DBDCD7E12620D97D (id_unit_id), INDEX IDX_DBDCD7E148D62931 (id_service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E1F24F7657 FOREIGN KEY (id_parent_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E18DB92E8E FOREIGN KEY (id_child_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E12620D97D FOREIGN KEY (id_unit_id) REFERENCES school_unit (id)');
        $this->addSql('ALTER TABLE enrollment ADD CONSTRAINT FK_DBDCD7E148D62931 FOREIGN KEY (id_service_id) REFERENCES school_service (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE enrollment');
    }
}
