<?php

namespace App\Http\Requests\v1;

use Illuminate\Foundation\Http\FormRequest;

class StoreMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tournament_id' => ['required', 'integer'],
            'home_team_id' => ['required', 'integer'],
            'away_team_id' => ['required', 'integer'],
            'played_at' => ['required', 'date'],
        ];
    }
}
