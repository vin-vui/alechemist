<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBrewingStepRequest;
use App\Http\Resources\BrewingStepResource;
use App\Models\Brewing;
use App\Models\BrewingStep;
use Illuminate\Support\Facades\DB;

class BrewingStepController extends Controller
{
    /**
     * Types de palier qui portent un chrono, et étape du brassin pendant laquelle
     * ce chrono a un sens : les paliers d'empâtage, et les aromatiques, qui
     * infusent hors flamme une fois le feu coupé. Les autres types n'ont rien à
     * décompter ; l'ébullition, elle, a son chrono à elle, porté par le brassin.
     *
     * @var array<string, string>
     */
    private const TIMED_TYPES = ['Mash' => 'mash', 'Aroma' => 'boil'];

    public function update(UpdateBrewingStepRequest $request, BrewingStep $brewingStep)
    {
        $brewingStep->update($request->validated());

        return new BrewingStepResource($brewingStep);
    }

    /**
     * Démarre le chrono d'un palier : palier d'empâtage, ou infusion hors flamme
     * d'un aromatique.
     *
     * L'heure est posée par le serveur, jamais envoyée par le client, comme pour
     * l'ébullition. Un seul palier tourne à la fois : on ne tient pas deux paliers
     * en parallèle dans une cuve, et deux chronos côte à côte à l'écran ne
     * diraient plus lequel suivre.
     */
    public function startTimer(BrewingStep $brewingStep)
    {
        $this->authorize('update', $brewingStep->brewing);

        abort_unless(isset(self::TIMED_TYPES[$brewingStep->type]), 422);

        // Un aromatique entre dans la cuve à l'arrêt du feu : son infusion ne peut
        // pas commencer tant que l'ébullition tourne. L'écran cache le bouton
        // jusque-là, mais la règle ne peut pas ne vivre que dans l'écran.
        if ($brewingStep->type === 'Aroma') {
            abort_unless($this->boilIsOver($brewingStep->brewing), 409);
        }

        if ($brewingStep->started_at === null) {
            DB::transaction(function () use ($brewingStep) {
                BrewingStep::where('brewing_id', $brewingStep->brewing_id)
                    ->whereKeyNot($brewingStep->id)
                    ->whereNotNull('started_at')
                    ->update(['started_at' => null]);

                $brewingStep->started_at = now();
                $brewingStep->save();
            });
        }

        return new BrewingStepResource($brewingStep);
    }

    /**
     * Annule un chrono de palier lancé par erreur.
     *
     * Même règle que BrewingController::cancelBoil : la sortie n'existe que tant
     * que le brassin est *sur* l'étape du palier. Une fois l'étape passée,
     * l'horodatage est définitif et une consultation en arrière ne réécrit pas
     * l'historique.
     */
    public function cancelTimer(BrewingStep $brewingStep)
    {
        $this->authorize('update', $brewingStep->brewing);

        $stepOfType = self::TIMED_TYPES[$brewingStep->type] ?? null;
        abort_unless($brewingStep->brewing->current_step === $stepOfType, 409);

        $brewingStep->started_at = null;
        $brewingStep->save();

        return new BrewingStepResource($brewingStep);
    }

    /** L'arrêt du feu est passé : le chrono d'ébullition est démarré et écoulé. */
    private function boilIsOver(Brewing $brewing): bool
    {
        return $brewing->boil_start !== null
            && $brewing->boil_start->clone()->addMinutes($brewing->boil_time)->isPast();
    }
}
