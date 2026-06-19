<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpar produtos existentes
        Product::query()->delete();

        $brands = Brand::all()->keyBy('slug');
        $categories = Category::all()->keyBy('slug');

        $masculino = [
            [
                'brand_slug' => 'rolex',
                'category_slug' => 'mergulho',
                'name' => 'Rolex Submariner Date',
                'short_description' => 'O relógio de mergulho de referência absoluta.',
                'description' => 'O design robusto e funcional do Rolex Submariner tornou-se rapidamente lendário. Com a sua caixa Oyster redesenhada, mostrador distintivo com grandes marcadores luminescentes e luneta rotativa unidirecional Cerachrom.',
                'features' => "Luneta rotativa unidirecional Cerachrom.\nVidro de safira resistente a riscos.\nMovimento mecânico automático Calibre 3235.\nEstanque até 300 metros.",
                'in_the_box' => ['Rolex Submariner Date', 'Caixa de pele premium', 'Cartão de garantia internacional', 'Manual do utilizador'],
                'price' => 14500.00,
                'stock' => 50,
                'weight' => 0.155,
                'is_featured' => true,
            ],
            [
                'brand_slug' => 'omega',
                'category_slug' => 'mergulho',
                'name' => 'Omega Seamaster Diver 300M',
                'short_description' => 'O lendário relógio usado pelo agente James Bond.',
                'description' => 'Desde 1993, o Seamaster Professional Diver 300M goza de uma reputação lendária. Este modelo moderno em aço inoxidável inclui uma luneta de cerâmica azul com escala de mergulho em esmalte branco.',
                'features' => "Mostrador de cerâmica azul com ondas gravadas a laser.\nVálvula de escape de hélio.\nMovimento automático Omega Co-Axial Master Chronometer 8800.\nResistência magnética até 15.000 gauss.",
                'in_the_box' => ['Omega Seamaster Diver', 'Estojo de madeira nobre', 'Certificado Master Chronometer', 'Manual de instruções'],
                'price' => 5900.00,
                'stock' => 50,
                'weight' => 0.168,
                'is_featured' => false,
            ],
            [
                'brand_slug' => 'tag-heuer',
                'category_slug' => 'cronografos',
                'name' => 'Tag Heuer Carrera Chronograph',
                'short_description' => 'Nascido nas pistas de corrida mais exigentes do mundo.',
                'description' => 'Um cronógrafo desportivo elegante e moderno inspirado no design original do painel de instrumentos de corrida. Equipado com o movimento automático de manufatura Heuer 02.',
                'features' => "Cronógrafo automático de manufatura Heuer 02.\nReserva de marcha impressionante de 80 horas.\nLuneta com escala taquimétrica.\nVidro de safira abaulado com duplo tratamento antirreflexo.",
                'in_the_box' => ['Tag Heuer Carrera', 'Estojo de viagem em pele', 'Cartão de garantia NFC', 'Instruções de funcionamento'],
                'price' => 6200.00,
                'stock' => 50,
                'weight' => 0.175,
                'is_featured' => true,
            ],
        ];

        $feminino = [
            [
                'brand_slug' => 'rolex',
                'category_slug' => 'classicos',
                'name' => 'Rolex Datejust 31 Oyster',
                'short_description' => 'O clássico feminino da Rolex por excelência.',
                'description' => 'O Datejust é o arquétipo do relógio clássico, graças a uma estética e a funções que transcendem as modas. A caixa Oyster de 31 mm em aço e ouro amarelo com luneta canelada é uma joia intemporal.',
                'features' => "Luneta canelada em ouro amarelo de 18 quilates.\nLupa de data Cyclops integrada no vidro de safira.\nMovimento mecânico automático Calibre 2236.\nBracelete Jubilee de cinco elos.",
                'in_the_box' => ['Rolex Datejust 31', 'Estojo de pele verde', 'Cartão de garantia internacional', 'Manual Datejust'],
                'price' => 9800.00,
                'stock' => 50,
                'weight' => 0.095,
                'is_featured' => true,
            ],
            [
                'brand_slug' => 'omega',
                'category_slug' => 'classicos',
                'name' => 'Omega Constellation Quartz 28mm',
                'short_description' => 'Sofisticação reconhecível com as icónicas "garras".',
                'description' => 'O design dramático e duradouro do Omega Constellation é caracterizado pelas suas famosas "garras" nas laterais da caixa. Apresenta marcadores de horas em diamante e mostrador em madrepérola branca.',
                'features' => "Caixa de 28 mm com luneta gravada com números romanos.\nMostrador em madrepérola branca com 12 diamantes.\nMovimento de precisão a quartzo Omega 4061.\nVidro de safira convexo resistente a riscos.",
                'in_the_box' => ['Omega Constellation', 'Estojo de madeira de luxo', 'Certificado de diamantes legítimos', 'Manual de utilizador'],
                'price' => 6400.00,
                'stock' => 50,
                'weight' => 0.080,
                'is_featured' => true,
            ],
            [
                'brand_slug' => 'seiko',
                'category_slug' => 'classicos',
                'name' => 'Seiko Presage Ladies Enamel',
                'short_description' => 'Mostrador de esmalte feito à mão por mestres artesãos japoneses.',
                'description' => 'A elegância e a cultura japonesas unem-se na coleção Presage. Este modelo feminino apresenta um requintado mostrador de esmalte branco cozido no forno por artesãos consagrados.',
                'features' => "Mostrador em esmalte genuíno cozido no forno.\nMovimento mecânico automático de calibre fino 6R31.\nPonteiros azuis temperados contrastantes.\nBracelete de pele de jacaré azul.",
                'in_the_box' => ['Seiko Presage Enamel', 'Estojo japonês em madeira Kiri', 'Certificado do artesão de esmalte', 'Manual de instruções'],
                'price' => 420.00,
                'stock' => 50,
                'weight' => 0.065,
                'is_featured' => false,
            ],
        ];

        $unisexo = [
            [
                'brand_slug' => 'casio',
                'category_slug' => 'desporto',
                'name' => 'Casio Classic F-91W-1YER',
                'short_description' => 'O clássico absoluto e a lenda intemporal da Casio.',
                'description' => 'Famoso pelo seu design minimalista, peso pluma, durabilidade indestrutível e excelente duração de bateria de até 7 anos. Um relógio versátil e unissexo amado mundialmente.',
                'features' => "Cronómetro de 1/100 segundos e alarme diário.\nIluminação lateral LED verde.\nBateria com autonomia de até 7 anos.\nBracelete de resina flexível e altamente resistente.",
                'in_the_box' => ['Casio F-91W', 'Caixa de cartão Casio retro', 'Manual do utilizador', 'Garantia nacional'],
                'price' => 25.00,
                'stock' => 100,
                'weight' => 0.021,
                'is_featured' => true,
            ],
            [
                'brand_slug' => 'rolex',
                'category_slug' => 'classicos',
                'name' => 'Rolex Oyster Perpetual 36',
                'short_description' => 'A essência do relógio de luxo Oyster unissexo.',
                'description' => 'Estes modelos destacam-se pelos seus mostradores coloridos. A caixa de 36 mm Oystersteel é perfeita para homens e mulheres que apreciam a elegância intemporal.',
                'features' => "Mostrador azul-turquesa brilhante (Tiffany Blue).\nMovimento mecânico automático Calibre 3230.\nExcelente legibilidade sob pouca luz Chromalight.\nReserva de marcha de 70 hours.",
                'in_the_box' => ['Rolex Oyster Perpetual 36', 'Estojo verde de pele', 'Selo de cronómetro superlativo', 'Manual de instruções'],
                'price' => 6300.00,
                'stock' => 50,
                'weight' => 0.110,
                'is_featured' => true,
            ],
        ];

        // Seed Masculino
        foreach ($masculino as $w) {
            $this->createProduct($w, 'masculino', $brands, $categories);
        }

        // Seed Feminino
        foreach ($feminino as $w) {
            $this->createProduct($w, 'feminino', $brands, $categories);
        }

        // Seed Unisexo
        foreach ($unisexo as $w) {
            $this->createProduct($w, 'unisexo', $brands, $categories);
        }
    }

    private function createProduct(array $w, string $gender, Collection $brands, Collection $categories): void
    {
        $brand = $brands->get($w['brand_slug']);
        $category = $categories->get($w['category_slug']);

        if (!$brand || !$category) {
            return;
        }

        Product::create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'gender' => $gender,
            'name' => $w['name'],
            'slug' => Str::slug($w['name']),
            'short_description' => $w['short_description'],
            'description' => $w['description'],
            'features' => $w['features'],
            'in_the_box' => $w['in_the_box'],
            'price' => $w['price'],
            'stock' => $w['stock'],
            'weight' => $w['weight'],
            'is_active' => true,
            'is_featured' => $w['is_featured'],
        ]);
    }
}
