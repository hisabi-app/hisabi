<?php

namespace Tests\Unit\Domain;

use App\Domain\SectionDivider;
use Tests\TestCase;

class SectionDividerTest extends TestCase
{
    public function test_it_has_component()
    {
        $sut = new SectionDivider;

        $this->assertEquals('section-divider', $sut->component());
    }

    public function test_it_has_default_name()
    {
        $sut = new SectionDivider;

        $this->assertEquals('Section Divider', $sut->name());
    }

    public function test_it_has_default_width()
    {
        $sut = new SectionDivider;

        $this->assertEquals('full', $sut->width());
    }

    public function test_it_has_title_and_can_be_set()
    {
        $sut = new SectionDivider;

        $sut->withTitle("Hello title");

        $this->assertEquals('Hello title', $sut->title());
    }

    public function test_it_is_json_serializeable()
    {
        $sut = new SectionDivider;

        $this->assertArrayHasKey('component', $sut->jsonSerialize());
        $this->assertArrayHasKey('name', $sut->jsonSerialize());
        $this->assertArrayHasKey('width', $sut->jsonSerialize());
    }
}
