<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('hub.support.index', compact('user'));
    }

    public function open(Request $request)
    {
        $tickets = $this->baseQuery($request)->where('status', 'open')->latest()->paginate(20);
        return view('hub.support.partials.tickets_list', compact('tickets'));
    }

    public function inBehandeling(Request $request)
    {
        $tickets = $this->baseQuery($request)->where('status', 'in_behandeling')->latest()->paginate(20);
        return view('hub.support.partials.tickets_list', compact('tickets'));
    }

    public function gesloten(Request $request)
    {
        $tickets = $this->baseQuery($request)->where('status', 'gesloten')->latest()->paginate(20);
        return view('hub.support.partials.tickets_list', compact('tickets'));
    }




    protected function baseQuery(Request $request)
    {
        $user   = $request->user();
        $q      = (string) $request->query('q', '');
        $query  = Ticket::query()->with(['user:id,name,email']);

        if (!in_array($user->rol, ['admin','medewerker'], true)) {
            $query->where('user_id', $user->id);
        }

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('subject', 'like', "%{$q}%")
                    ->orWhere('message', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%");
            });
        }

        return $query;
    }
}
