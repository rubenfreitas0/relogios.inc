<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreShippingZoneRequest;
use App\Http\Requests\Admin\UpdateShippingZoneRequest;
use App\Http\Resources\ShippingZoneResource;
use App\Models\ShippingZone;
use App\Models\ShippingZoneCountry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShippingZoneController extends Controller
{
    /**
     * Listagem paginada de zonas de envio.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $zones = ShippingZone::with('countries')
            ->withCount('shippingMethods')
            ->when(
                $request->filled('search'),
                fn($q) => $q->where('name', 'LIKE', '%' . $request->search . '%')
            )
            ->when(
                $request->has('is_active'),
                fn($q) => $q->where('is_active', $request->boolean('is_active'))
            )
            ->orderBy('name')
            ->paginate(min($request->integer('per_page', 15), 100));

        return ShippingZoneResource::collection($zones);
    }

    /**
     * Criar nova zona de envio.
     */
    public function store(StoreShippingZoneRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $zone = ShippingZone::create([
            'name'      => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Associar países se fornecidos
        if (! empty($validated['countries'])) {
            $this->syncCountries($zone, $validated['countries']);
        }

        $zone->load('countries');

        return (new ShippingZoneResource($zone))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Detalhe de uma zona de envio.
     */
    public function show(ShippingZone $shippingZone): ShippingZoneResource
    {
        $shippingZone->load('countries');
        $shippingZone->loadCount('shippingMethods');

        return new ShippingZoneResource($shippingZone);
    }

    /**
     * Atualizar zona de envio.
     */
    public function update(UpdateShippingZoneRequest $request, ShippingZone $shippingZone): ShippingZoneResource
    {
        $validated = $request->validated();

        $shippingZone->update([
            'name'      => $validated['name'] ?? $shippingZone->name,
            'is_active' => $validated['is_active'] ?? $shippingZone->is_active,
        ]);

        // Sincronizar países se fornecidos
        if (array_key_exists('countries', $validated)) {
            $this->syncCountries($shippingZone, $validated['countries']);
        }

        $shippingZone->load('countries');
        $shippingZone->loadCount('shippingMethods');

        return new ShippingZoneResource($shippingZone);
    }

    /**
     * Eliminar zona de envio.
     */
    public function destroy(ShippingZone $shippingZone): JsonResponse
    {
        if ($shippingZone->shippingMethods()->exists()) {
            return response()->json([
                'message' => 'Não é possível eliminar esta zona — existem métodos de envio associados. Remova-os primeiro.',
            ], 422);
        }

        // Remover países associados e a zona
        $shippingZone->countries()->delete();
        $shippingZone->delete();

        return response()->json(null, 204);
    }

    /**
     * Sincroniza os country codes de uma zona (remove os antigos e insere os novos).
     */
    private function syncCountries(ShippingZone $zone, array $countryCodes): void
    {
        $zone->countries()->delete();

        if (empty($countryCodes)) {
            return;
        }

        $records = array_map(fn(string $code) => [
            'shipping_zone_id' => $zone->id,
            'country_code'     => strtoupper($code),
            'created_at'       => now(),
            'updated_at'       => now(),
        ], $countryCodes);

        ShippingZoneCountry::insert($records);
    }
}
