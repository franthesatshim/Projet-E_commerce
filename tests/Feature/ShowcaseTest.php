<?php

namespace Tests\Feature;

use Tests\TestCase;

class ShowcaseTest extends TestCase
{
    public function test_showcase_page_displays_curated_photos(): void
    {
        $this->get('/collection')
            ->assertOk()
            ->assertSee('Collection photos', false)
            ->assertSee('images/showcase/photo-01.jpg');
    }

    public function test_showcase_can_be_filtered_by_category(): void
    {
        $this->get('/collection?categorie=Sacs')
            ->assertOk()
            ->assertSee('Sacs')
            ->assertSee('images/showcase/photo-20.jpg')
            ->assertDontSee('images/showcase/photo-12.jpg');
    }
}
