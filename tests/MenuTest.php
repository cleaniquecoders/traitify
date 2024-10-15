<?php

use CleaniqueCoders\Traitify\Contracts\Menu;
use Illuminate\Support\Collection;

// Mock a class that implements the Menu contract
class MockMenu implements Menu
{
    public function menus(): Collection
    {
        return collect([
            ['name' => 'Home', 'url' => '/home'],
            ['name' => 'About', 'url' => '/about'],
        ]);
    }
}

it('returns a collection of menus', function () {
    $menu = new MockMenu;

    // Call the menus method
    $menus = $menu->menus();

    // Ensure the method returns a collection
    expect($menus)->toBeInstanceOf(Collection::class)
        ->and($menus->count())->toBe(2)
        ->and($menus->first())->toMatchArray(['name' => 'Home', 'url' => '/home'])
        ->and($menus->last())->toMatchArray(['name' => 'About', 'url' => '/about']);
});
