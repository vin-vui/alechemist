<?php

namespace Tests\Feature;

use App\Exceptions\InvalidBeerXmlException;
use App\Models\Recipe;
use App\Models\User;
use App\Services\BeerXmlImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeerXmlImporterTest extends TestCase
{
    use RefreshDatabase;

    private const VALID_XML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<RECIPES>
  <RECIPE>
    <NAME>Test IPA</NAME>
    <TYPE>All Grain</TYPE>
    <STYLE><NAME>American IPA</NAME><TYPE>Ale</TYPE></STYLE>
    <BATCH_SIZE>20.0</BATCH_SIZE>
    <BOIL_SIZE>24.0</BOIL_SIZE>
    <EFFICIENCY>72.0</EFFICIENCY>
    <EST_COLOR>8.2</EST_COLOR>
    <IBU>45.3</IBU>
    <EST_ABV>5.6</EST_ABV>
    <EST_OG>1.052</EST_OG>
    <EST_FG>1.010</EST_FG>
    <BOIL_TIME>60</BOIL_TIME>
    <CARBONATION>2.4</CARBONATION>
    <FERMENTABLES>
      <FERMENTABLE><NAME>Pale Malt</NAME><AMOUNT>5.0</AMOUNT></FERMENTABLE>
      <FERMENTABLE><NAME>Munich</NAME><AMOUNT>0.5</AMOUNT></FERMENTABLE>
    </FERMENTABLES>
    <HOPS>
      <HOP><NAME>Cascade</NAME><AMOUNT>0.030</AMOUNT><TIME>60</TIME><USE>Boil</USE></HOP>
    </HOPS>
    <YEASTS>
      <YEAST><NAME>US-05</NAME><AMOUNT>0.011</AMOUNT></YEAST>
    </YEASTS>
    <MASH>
      <MASH_STEPS>
        <MASH_STEP>
          <NAME>Saccharification</NAME>
          <INFUSE_AMOUNT>15.0</INFUSE_AMOUNT>
          <INFUSE_TEMP>72.0</INFUSE_TEMP>
          <STEP_TEMP>67.0</STEP_TEMP>
          <STEP_TIME>60</STEP_TIME>
        </MASH_STEP>
      </MASH_STEPS>
      <SPARGE_TEMP>78.0</SPARGE_TEMP>
    </MASH>
    <PRIMARY_AGE>14</PRIMARY_AGE>
    <PRIMARY_TEMP>19.0</PRIMARY_TEMP>
  </RECIPE>
</RECIPES>
XML;

    public function test_import_creates_recipe_and_steps_for_the_given_user(): void
    {
        $user = User::factory()->create();

        $recipe = app(BeerXmlImporter::class)->import(self::VALID_XML, $user);

        $this->assertSame($user->id, $recipe->user_id);
        $this->assertSame('Test IPA', $recipe->name);
        // 2 fermentables + 1 hop + 1 yeast + infuse + mash step + sparge + primary
        $this->assertCount(8, $recipe->steps);
    }

    public function test_unreadable_xml_throws_and_persists_nothing(): void
    {
        $user = User::factory()->create();

        try {
            app(BeerXmlImporter::class)->import('<?xml version="1.0"?><RECIPES><RECIPE>', $user);
            $this->fail('InvalidBeerXmlException attendue');
        } catch (InvalidBeerXmlException) {
            $this->assertSame(0, Recipe::count());
        }
    }

    public function test_incomplete_structure_rolls_back_everything(): void
    {
        $user = User::factory()->create();
        // HOP sans USE : la création du step échoue APRÈS la création de la recipe
        $xml = str_replace('<USE>Boil</USE>', '', self::VALID_XML);

        try {
            app(BeerXmlImporter::class)->import($xml, $user);
            $this->fail('InvalidBeerXmlException attendue');
        } catch (InvalidBeerXmlException) {
            $this->assertSame(0, Recipe::count());
        }
    }
}
