<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BeerXmlImportTest extends TestCase
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

    private function upload(User $user, string $content, string $name = 'recipe.xml')
    {
        return $this->actingAs($user)->post(route('upload.store'), [
            'file' => UploadedFile::fake()->createWithContent($name, $content),
        ]);
    }

    public function test_valid_beerxml_creates_recipe_and_steps(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->upload($user, self::VALID_XML);

        $recipe = Recipe::firstWhere('name', 'Test IPA');
        $this->assertNotNull($recipe);
        $this->assertSame($user->id, $recipe->user_id);
        // 2 fermentables + 1 hop + 1 yeast + infuse + mash step + sparge + primary
        $this->assertCount(8, $recipe->steps);
        $response->assertRedirect(route('recipes.show', $recipe));
    }

    public function test_malformed_xml_is_rejected_without_creating_anything(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $response = $this->upload($user, '<?xml version="1.0"?><RECIPES><RECIPE>');

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, Recipe::count());
    }

    public function test_import_failure_mid_parse_rolls_back_the_recipe(): void
    {
        $user = User::factory()->withPersonalTeam()->create();
        // HOP sans USE : la création du step échoue APRÈS la création de la recipe
        $xml = str_replace('<USE>Boil</USE>', '', self::VALID_XML);

        $response = $this->upload($user, $xml);

        $response->assertSessionHasErrors('file');
        $this->assertSame(0, Recipe::count());
    }

    public function test_uploaded_file_is_not_persisted_to_public_files(): void
    {
        $user = User::factory()->withPersonalTeam()->create();

        $this->upload($user, self::VALID_XML, 'persisted-check.xml');

        $this->assertFileDoesNotExist(public_path('files/persisted-check.xml'));
    }
}
