<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    /**
     * Listar todas as moradas do utilizador.
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->latest()->get();
        return AddressResource::collection($addresses);
    }

    /**
     * Criar uma nova morada.
     */
    public function store(StoreAddressRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        $address = DB::transaction(function () use ($user, $validated) {
            if ($user->addresses()->count() === 0) {
                $validated['is_default'] = true;
            }

            if (($validated['is_default'] ?? false) === true) {
                $user->addresses()->lockForUpdate()->update(['is_default' => false]);
            }

            return $user->addresses()->create($validated);
        });

        return response()->json([
            'message' => 'Morada adicionada com sucesso.',
            'data' => new AddressResource($address)
        ], 201);
    }

    /**
     * Mostrar os detalhes de uma morada específica.
     */
    public function show(Request $request, Address $address)
    {
        if ($request->user()->id !== $address->user_id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        return new AddressResource($address);
    }

    /**
     * Atualizar os dados de uma morada.
     */
    public function update(UpdateAddressRequest $request, Address $address)
    {
        if ($request->user()->id !== $address->user_id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($request, $address, $validated) {
            if (($validated['is_default'] ?? false) === true && !$address->is_default) {
                $request->user()->addresses()->lockForUpdate()->update(['is_default' => false]);
            }

            $address->update($validated);
        });

        return response()->json([
            'message' => 'Morada atualizada com sucesso.',
            'data' => new AddressResource($address)
        ]);
    }

    /**
     * Definir a morada como a principal
     */
    public function setDefault(Request $request, Address $address)
    {
        if ($request->user()->id !== $address->user_id) {
            return response()->json(['message' => 'Acesso negado.'], 403);
        }

        DB::transaction(function () use ($request, $address) {
            $request->user()->addresses()->lockForUpdate()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return response()->json([
            'message' => 'Morada definida como principal.',
            'data' => new AddressResource($address)
        ]);
    }

    /**
     * Eliminar uma morada.
     */
    public function destroy(Request $request, Address $address)
    {
        if ($request->user()->id !== $address->user_id) {
            return response()->json(['message' => 'Não tens permissão para apagar esta morada.'], 403);
        }

        DB::transaction(function () use ($request, $address) {
            $wasDefault = $address->is_default;

            $address->delete();

            if ($wasDefault) {
                $nextAddress = $request->user()->addresses()->latest()->first();
                if ($nextAddress) {
                    $nextAddress->update(['is_default' => true]);
                }
            }
        });

        return response()->noContent();
    }
}
