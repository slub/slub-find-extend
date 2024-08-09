<?php
namespace Slub\SlubFindExtend\Tests\Unit\ViewHelpers;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Slub\SlubFindExtend\Tests\Unit\Fixtures\LoadableClass;

class LinksFromDataViewHelperTest extends UnitTestCase
{

    /**
     * @test
     */
    public function methodReturnsTrue()
    {
        $firstClassObject = new LoadableClass();
        $this->assertTrue($firstClassObject->returnsTrue());
    }
}