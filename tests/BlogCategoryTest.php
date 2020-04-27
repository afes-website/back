<?php

class BlogCategoryTest extends TestCase {
    public function test_get_category() {
        $this->get('/blog/categories');
        $this->assertResponseOk();
        $this->seeJsonStructure(['*' => ['name', 'visible']]);

    }
}
