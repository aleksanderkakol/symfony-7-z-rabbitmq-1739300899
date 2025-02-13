<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250212224917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create User';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE user (
                id INT NOT NULL,
                full_name VARCHAR(255) NOT NULL, 
                email VARCHAR(255) NOT NULL, 
                city VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
    }
}
