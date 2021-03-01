<?php
namespace Tests;

class BlogCategoryTest extends TestCase {
    public function testGetCategory() {
        $this->get('/blog/categories');
        $this->assertResponseOk();
        $this->seeJsonStructure(['*' => ['name', 'visible']]);
    }
}
