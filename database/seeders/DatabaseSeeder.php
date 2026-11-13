<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Vrai catalogue ShopNow : aucune fiche avec prix fictifs n'est créée.
        foreach (['Vêtements', 'Perruques', 'Tissus wax', 'Sacs', 'Chaussures'] as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
            );
        }

        // Cacher uniquement les quatre fiches de démonstration générées
        // par l'ancienne version. Ne jamais effacer les produits ni les commandes.
        $demoNames = ['Smartphone Android', 'Casque Bluetooth', 'Montre connectée', 'Sac à dos'];
        Product::query()
            ->whereIn('name', $demoNames)
            ->whereIn('slug', array_map(fn ($name) => Str::slug($name), $demoNames))
            ->where('description', 'Produit de démonstration à remplacer par votre catalogue réel.')
            ->update(['is_active' => false]);

        // Retirer du menu les catégories génériques qui sont maintenant vides.
        // Conserver les catégories anciennes si un vrai produit actif y est lié.
        Category::query()
            ->whereIn('slug', ['electronique', 'mode', 'maison', 'sports', 'livres'])
            ->whereDoesntHave('products', fn ($query) => $query->where('is_active', true))
            ->update(['is_active' => false]);

        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        if ($email && $password) {
            User::updateOrCreate(
                ['email' => $email],
                ['name' => 'Administrateur', 'password' => Hash::make($password), 'role' => 'admin']
            );
        }
    }
}
