<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector;

use Iterator;
use Rector\Core\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TypedPropertyTest extends AbstractRectorTestCase
{
    /**
     * @requires PHP >= 7.4
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureTypedProperty');
    }

    protected function getRectorClass(): string
    {
        return AnnotatedPropertyInjectToConstructorInjectionRector::class;
    }
}
