<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Tests;

use Doctrine\Bundle\LicenseManagerBundle\Entity\Commit;

class CommitTest extends \PHPUnit_Framework_TestCase
{
    public function testParseChangeLine()
    {
        $commit = new Commit("abcdefg", null, null, " 3 files changed, 6 insertions(+), 5 deletions(-)");

        $this->assertEquals(3, $commit->getFilesChanged());
        $this->assertEquals(6, $commit->getInsertions());
        $this->assertEquals(5, $commit->getDeletions());
    }
}

