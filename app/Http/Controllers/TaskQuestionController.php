<?php

namespace App\Http\Controllers;

use App\Models\AanvraagTaskQuestion;
use Illuminate\Http\Request;

class TaskQuestionController extends Controller
{
    public function update(Request $request, AanvraagTaskQuestion $question)
    {
        \Log::info('Question update hit', ['id' => $question->id, 'payload' => $request->all()]);

        $data = $request->validate(['answer' => 'nullable|string']);
        $question->update($data);

        return response()->json([
            'success' => true,
            'id'      => $question->id,
            'answer'  => $question->answer,
        ]);
    }
}