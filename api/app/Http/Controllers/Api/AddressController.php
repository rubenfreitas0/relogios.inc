<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Display a listing of the user's addresses.
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->latest()->get();
        return AddressResource::collection($addresses);
    }

    /**
     * Store a newly created address.
     */
    public function store(StoreAddressRequest $request)
    {
        $validated = $request->validated();
        
        $user = $request->user();

        // Se for a primeira morada do user, forçamos a ser 'is_default'
        if ($user->addresses()->count() === 0) {
            $validated['is_default'] = true;
        }

        // Caso o utilizador envie esta para ser default, tiramos o default de todas as outras dele
        if (($validated['is_default'] ?? false) === true) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($validated);

        return response()->json([
            'message' => 'Morada adicionada com sucesso.',
            'data' => new AddressResource($address)
        ], 201);
    }

    /**
     * Update the user's address.
     */
    public function update(UpdateAddressRequest $request, Address $address)
    {
        $validated = $request->validated();

        if (($validated['is_default'] ?? false) === true && !$address->is_default) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($validated);

        return response()->json([
            'message' => 'Morada atualizada com sucesso.',
            'data' => new AddressResource($address)
        ]);
    }

    /**
     * Remove the address.
     */
    public function destroy(Request $request, Address $address)
    {
        if ($request->user()->id !== $address->user_id) {
            return response()->json(['message' => 'Não tens permissão para apagar esta morada.'], 403);
        }

        $address->delete();

        return response()->json([
            'message' => 'Morada apagada silenciosamente com sucesso.'
        ]);
    }
}
