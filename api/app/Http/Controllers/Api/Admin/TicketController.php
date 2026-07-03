<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Http\Resources\TicketResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    /**
     * Listar todos os tickets paginados.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $tickets = Ticket::query()
            ->with(['user.addresses', 'user.orders'])
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->filled('subject'), function ($q) use ($request) {
                $q->where('subject', $request->subject);
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = '%' . strtolower((string) $request->search) . '%';
                $q->where(function ($sub) use ($search) {
                    $sub->whereRaw('LOWER(subject) LIKE ?', [$search])
                        ->orWhereRaw('LOWER(message) LIKE ?', [$search])
                        ->orWhereHas('user', function ($u) use ($search) {
                            $u->whereRaw('LOWER(firstname) LIKE ?', [$search])
                                ->orWhereRaw('LOWER(lastname) LIKE ?', [$search])
                                ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
                        });
                });
            })
            ->latest()
            ->paginate(min($request->integer('per_page', 10), 100));

        return TicketResource::collection($tickets);
    }

    /**
     * Mostrar detalhes de um ticket.
     */
    public function show(Ticket $ticket): TicketResource
    {
        return new TicketResource($ticket->load(['user.addresses', 'user.orders']));
    }

    /**
     * Atualizar o estado (status) do ticket.
     */
    public function updateStatus(Request $request, Ticket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:open,closed'],
        ], [
            'status.required' => 'O estado é obrigatório.',
            'status.in'       => 'O estado deve ser: open (aberto) ou closed (fechado).',
        ]);

        $ticket->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Estado do ticket atualizado com sucesso.',
            'data'    => new TicketResource($ticket->load('user')),
        ]);
    }

    /**
     * Eliminar um ticket.
     */
    public function destroy(Ticket $ticket): JsonResponse
    {
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket de suporte eliminado com sucesso.',
        ]);
    }
}
