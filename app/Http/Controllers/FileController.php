<?php

namespace App\Http\Controllers;

use File;
use App\Models\Recipe;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    public function index()
    {
        return view ('recipes.uploadFile');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xml|max:2048'
        ]);


        $uploadFile = $request->file('file');
        $fileContent = file_get_contents($uploadFile->path());

        $xmlFile = simplexml_load_string($fileContent);
        $arrayFile = json_decode(json_encode($xmlFile), true);
        $recipe = ($arrayFile['RECIPE']);
        $mash = ($arrayFile['RECIPE']['MASH']);

        // Informations recipes

        $imported_recipe = Recipe::create ([
            'name' => $recipe['NAME'],
            'type' => $recipe['STYLE']['NAME'],
            'method' => $recipe['TYPE'],
            'ferment' => $recipe['STYLE']['TYPE'],
            'volume' => $recipe['BATCH_SIZE'],
            'efficiency' => $recipe['EFFICIENCY'],
            'color' => $recipe['EST_COLOR'],
            'bitterness' => $recipe['IBU'],
            'alcohol' => $recipe['EST_ABV'],
            'initial_density' => $recipe['EST_OG'],
            'final_density' => $recipe['EST_FG'],
            'carbonation' => $recipe['CARBONATION']
        ]);


        // Céréales et sucres - Fermentables
        if (!empty($recipe['FERMENTABLES'])) {
            foreach ($arrayFile['RECIPE']['FERMENTABLES']['FERMENTABLE'] as $fermentable) {

                $this->createStep($fermentable['AMOUNT']*1000, 'g', $fermentable['NAME'], null, 'Preparation', $imported_recipe->id);
            }
        }

        // Houblons - Hops
        if (!empty($recipe['HOPS'])) {
            if(isset($recipe['HOPS']['HOP']['NAME'])) {
                $array_hop = $recipe['HOPS'];
            } else {
                $array_hop = $recipe['HOPS']['HOP'];
            }
            foreach ($array_hop as $hop) {

                $this->createStep($hop['AMOUNT']*1000, 'g', $hop['NAME'], $hop['TIME'], $hop['USE'], $imported_recipe->id);
            }
        }


        // Divers - Miscs
        if (!empty($recipe['MISCS'])) {
            if(isset($recipe['MISCS']['MISC']['NAME'])) {
                $array_misc = $recipe['MISCS'];
            } else {
                $array_misc = $recipe['MISCS']['MISC'];
            }
        foreach ($array_misc as $misc) {

            $this->createStep($misc['AMOUNT']*1000, 'g', $misc['NAME'], $misc['TIME'], $misc['USE'], $imported_recipe->id);

        }
        }

        //Levures - Yeats
        if (!empty($recipe['YEASTS'])) {
            if (isset($recipe['YEASTS']['YEAST']['NAME'])) {
                $array_yeast = $recipe['YEASTS'];
            } else {
                $array_yeast = $recipe['YEASTS']['YEAST'];
            }

            foreach ($array_yeast as $yeast) {

                 $this->createStep($yeast['AMOUNT']*1000, 'g', $yeast['NAME'], null, 'Yeast' , $imported_recipe->id);

            }
        }

        //Empâtage
        $mash = $recipe['MASH']['MASH_STEPS']['MASH_STEP'];

        if (array_key_exists('INFUSE_AMOUNT', $mash)) {
            $this->createStep($mash['INFUSE_AMOUNT'], 'l', $mash['INFUSE_TEMP'].' °C', null, 'Preparation', $imported_recipe->id);
        }
        if (array_key_exists('STEP_TEMP', $mash)) {
            $this->createStep($mash['STEP_TEMP'], '°C', $mash['NAME'], $mash['STEP_TIME'], 'Mash', $imported_recipe->id);
        }
        if (array_key_exists('SPARGE_TEMP', $recipe['MASH'])) {
            $this->createStep($recipe['MASH']['SPARGE_TEMP'], '°C', 'Filtrer et rincer les drêches jusqu\'à'.' '.$recipe['BOIL_SIZE'].' l', null, 'Mash', $imported_recipe->id);
        }


        //Fermentation
        if (array_key_exists('PRIMARY_AGE', $recipe)) {
            $this->createStep($recipe['PRIMARY_TEMP'], '°C', 'Primaire', $recipe['PRIMARY_AGE']*1440, 'Primary', $imported_recipe->id);
        }
        if (array_key_exists('SECONDARY_AGE', $recipe)) {
            $this->createStep($recipe['SECONDARY_TEMP'], '°C', 'Secondaire', $recipe['SECONDARY_AGE']*1440, 'Secondary', $imported_recipe->id);
        }
        if (array_key_exists('TERTIARY_AGE', $recipe)) {
            $this->createStep($recipe['TERTIARY_TEMP'], '°C', 'Tertiaire', $recipe['TERTIARY_AGE']*1440, 'Tertiary', $imported_recipe->id);
        }
        if (array_key_exists('AGE', $recipe)) {
            $this->createStep($recipe['AGE_TEMP'], '°C', 'Garde en bouteille', $recipe['AGE']*1440, 'Bottle', $imported_recipe->id);
        }

        return redirect('uploadFile');
    }

    // createStep($quantity, $unit, $field, $time, $type, $recipe_id)
    private function createStep(float $quantity, string $unit, string $field, int $time = null, string $type, int $recipe_id)
    {
        $test = Step::create(compact(
            'quantity',
            'unit',
            'field',
            'time',
            'type',
            'recipe_id'
        ));

        if ($test) {
            Log::info('✅ Step ' . $type . ' extracted successfully');
        } else {
            Log::error('💣 Step ' . $type . ' NOT extracted');
        }
    }
}
