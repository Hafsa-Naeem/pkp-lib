<?php

/**
 * @file tests/jobs/submissions/RemoveSubmissionFromSearchIndexJobTest.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Tests for removal of submission from search index job.
 */

namespace PKP\tests\jobs\submissions;

use Mockery;
use PKP\db\DAORegistry;
use APP\core\Application;
use PKP\tests\PKPTestCase;
use PKP\jobs\submissions\RemoveSubmissionFromSearchIndexJob;

/**
 * @runTestsInSeparateProcesses
 *
 * @see https://docs.phpunit.de/en/9.6/annotations.html#runtestsinseparateprocesses
 */
class RemoveSubmissionFromSearchIndexJobTest extends PKPTestCase
{
    /**
     * serializion from OJS 3.4.0
     */
    protected string $serializedJobData = <<<END
    O:55:"PKP\\jobs\\submissions\\RemoveSubmissionFromSearchIndexJob":3:{s:15:"\0*\0submissionId";i:26;s:10:"connection";s:8:"database";s:5:"queue";s:5:"queue";}
    END;

    /**
     * @see PKPTestCase::getMockedDAOs()
     */
    protected function getMockedDAOs(): array
    {
        return [
            ...parent::getMockedDAOs(),
            $this->getAppSearchDaoKey(),
        ];
    }

    /**
     * Test job is a proper instance
     */
    public function testUnserializationGetProperJobInstance(): void
    {
        $this->assertInstanceOf(
            RemoveSubmissionFromSearchIndexJob::class,
            unserialize($this->serializedJobData)
        );
    }
    
    /**
     * Ensure that a serialized job can be unserialized and executed
     */
    public function testRunSerializedJob(): void
    {
        /** @var RemoveSubmissionFromSearchIndexJob $removeSubmissionFromSearchIndexJob */
        $removeSubmissionFromSearchIndexJob = unserialize($this->serializedJobData);

        $submissionSearchDAOMock = Mockery::mock(\PKP\search\SubmissionSearchDAO::class)
            ->makePartial()
            ->shouldReceive(['deleteSubmissionKeywords' => null])
            ->withAnyArgs()
            ->getMock();

        DAORegistry::registerDAO($this->getAppSearchDaoKey(), $submissionSearchDAOMock);

        // Test that the job can be handled without causing an exception.
        $this->assertNull($removeSubmissionFromSearchIndexJob->handle());
    }
}
