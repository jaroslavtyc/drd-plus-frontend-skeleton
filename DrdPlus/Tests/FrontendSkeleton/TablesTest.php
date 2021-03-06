<?php
declare(strict_types=1);

namespace DrdPlus\Tests\FrontendSkeleton;

use DrdPlus\Tests\FrontendSkeleton\Partials\AbstractContentTest;

class TablesTest extends AbstractContentTest
{
    /**
     * @test
     */
    public function I_can_get_tables_only(): void
    {
        $withTables = $this->getHtmlDocument(['tables' => '' /* all of them */]);
        $body = $withTables->getElementsByTagName('body')[0];
        $tables = $body->getElementsByTagName('table');
        if (!$this->getTestsConfiguration()->hasTables()) {
            self::assertCount(0, $tables, 'No tables expected');
        } else {
            self::assertGreaterThan(0, \count($tables), 'Expected some tables');
        }
    }
}