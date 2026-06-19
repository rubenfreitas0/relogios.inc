<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductImage;
use App\Models\Product;

class ProductImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar imagens de produtos existentes
        ProductImage::query()->delete();

        $products = Product::all();

        $imageMapping = [
            // Masculino
            'rolex-submariner-date' => 1,
            'omega-seamaster-diver-300m' => 2,
            'tag-heuer-carrera-chronograph' => 4,
            
            // Feminino
            'rolex-datejust-31-oyster' => 3,
            'omega-constellation-quartz-28mm' => 2,
            'seiko-presage-ladies-enamel' => 1,
            
            // Unisexo
            'casio-classic-f-91w-1yer' => 4,
            'rolex-oyster-perpetual-36' => 1,
        ];

        foreach ($products as $product) {
            $watchNumber = $imageMapping[$product->slug] ?? (($product->id % 4) + 1);

            // Imagem Principal (Frente)
            ProductImage::create([
                'product_id' => $product->id,
                'url'        => "/products/premium/watch{$watchNumber}.png",
                'is_primary' => true,
                'sort_order' => 1,
            ]);

            // Segunda Imagem (Lado)
            ProductImage::create([
                'product_id' => $product->id,
                'url'        => "/products/premium/watch{$watchNumber}_side.png",
                'is_primary' => false,
                'sort_order' => 2,
            ]);

            // Terceira Imagem (Detalhe)
            ProductImage::create([
                'product_id' => $product->id,
                'url'        => "/products/premium/watch{$watchNumber}_detail.png",
                'is_primary' => false,
                'sort_order' => 3,
            ]);
        }
    }
}
