<?php

namespace Doctrine\DBAL\Tests\Functional\Ticket;

use Doctrine\DBAL\Tests\FunctionalTestCase;

use function in_array;
use function preg_match;

/**
 * @group DBAL-421
 */
class DBAL421Test extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $platform = $this->connection->getDatabasePlatform()->getName();
        if (in_array($platform, ['mysql', 'sqlite'])) {
            return;
        }

        $this->markTestSkipped('Currently restricted to MySQL and SQLite.');
    }

    public function testGuidShouldMatchPattern(): void
    {
        $guid    = $this->connection->query($this->getSelectGuidSql())->fetchOne();
        $pattern = '/[0-9A-F]{8}\-[0-9A-F]{4}\-[0-9A-F]{4}\-[8-9A-B][0-9A-F]{3}\-[0-9A-F]{12}/i';
        self::assertEquals(1, preg_match($pattern, $guid), 'GUID does not match pattern');
    }

    /**
     * This test does (of course) not proof that all generated GUIDs are
     * random, it should however provide some basic confidence.
     */
    public function testGuidShouldBeRandom(): void
    {
        $statement = $this->connection->prepare($this->getSelectGuidSql());
        $guids     = [];

        for ($i = 0; $i < 99; $i++) {
            $guid = $statement->execute()->fetchFirstColumn();
            self::assertNotContains($guid, $guids, 'Duplicate GUID detected');
            $guids[] = $guid;
        }
    }

    private function getSelectGuidSql(): string
    {
        return 'SELECT ' . $this->connection->getDatabasePlatform()->getGuidExpression();
    }
}