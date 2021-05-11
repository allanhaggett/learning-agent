<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\PathwaysTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\PathwaysTable Test Case
 */
class PathwaysTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\PathwaysTable
     */
    protected $Pathways;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Pathways',
        'app.Topics',
        'app.Ministries',
        'app.Statuses',
        'app.Competencies',
        'app.Steps',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Pathways') ? [] : ['className' => PathwaysTable::class];
        $this->Pathways = $this->getTableLocator()->get('Pathways', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Pathways);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
