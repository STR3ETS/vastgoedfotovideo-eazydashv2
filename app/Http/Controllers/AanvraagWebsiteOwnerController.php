<?php

namespace App\Http\Controllers;

use App\Models\AanvraagWebsite;
use App\Models\User;
use Illuminate\Http\Request;

class AanvraagWebsiteOwnerController extends Controller
{
    public function update(Request $request, AanvraagWebsite $aanvraag)
    {
        $data = $request->validate([
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $aanvraag->owner_id = $data['owner_id'] ?? null;
        $aanvraag->save();

        $owner = $aanvraag->owner;

        return response()->json([
            'success'  => true,
            'owner_id' => $aanvraag->owner_id,
            'owner'    => $owner ? [
                'id'   => $owner->id,
                'name' => $owner->name,
            ] : null,
        ]);
    }
}
