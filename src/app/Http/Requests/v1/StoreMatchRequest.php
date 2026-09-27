<?php

namespace App\Http\Requests\v1;

use App\Models\v1\MatchGame;
use App\Models\v1\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class StoreMatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tournament_id' => ['required', 'integer', 'exists:tournaments,id'],
            'home_team_id' => ['required', 'integer', 'exists:teams,id', 'different:away_team_id'],
            'away_team_id' => ['required', 'integer', 'exists:teams,id'],
            'match_date' => ['required', 'date'],
            'status' => ['sometimes', 'in:scheduled,played,suspended'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->hasAny(['tournament_id', 'home_team_id', 'away_team_id', 'match_date'])) {
                return;
            }

            $tournamentId = (int) $this->input('tournament_id');
            $teamIds = [
                'home_team_id' => (int) $this->input('home_team_id'),
                'away_team_id' => (int) $this->input('away_team_id'),
            ];
            $registeredTeamIds = Team::query()
                ->where('tournament_id', $tournamentId)
                ->whereKey(array_values($teamIds))
                ->pluck('id')
                ->all();

            foreach ($teamIds as $field => $teamId) {
                if (! in_array($teamId, $registeredTeamIds)) {
                    $validator->errors()->add($field, 'El equipo debe estar inscrito en el torneo seleccionado.');
                }
            }

            $conflictingTeamIds = MatchGame::query()
                ->whereDate('match_date', Carbon::parse($this->input('match_date'))->toDateString())
                ->whereIn('status', ['scheduled', 'played'])
                ->where(function ($query) use ($teamIds): void {
                    $query->whereIn('home_team_id', array_values($teamIds))
                        ->orWhereIn('away_team_id', array_values($teamIds));
                })
                ->get(['home_team_id', 'away_team_id'])
                ->flatMap(fn (MatchGame $match): array => [$match->home_team_id, $match->away_team_id])
                ->unique()
                ->all();

            foreach ($teamIds as $field => $teamId) {
                if (in_array($teamId, $conflictingTeamIds)) {
                    $validator->errors()->add($field, 'El equipo ya tiene un partido programado para ese día.');
                }
            }
        });
    }
}
