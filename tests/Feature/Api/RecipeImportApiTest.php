<?php

namespace Tests\Feature\Api;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class RecipeImportApiTest extends TestCase
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

    private function importRequest(User $user, string $content)
    {
        return $this->actingAs($user)->postJson('/api/recipes/import', [
            'file' => UploadedFile::fake()->createWithContent('recipe.xml', $content),
        ]);
    }

    public function test_a_valid_beerxml_upload_returns_201_with_the_recipe(): void
    {
        $user = User::factory()->create();

        $this->importRequest($user, self::VALID_XML)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Test IPA')
            ->assertJsonCount(8, 'data.steps');

        $recipe = Recipe::firstWhere('name', 'Test IPA');
        $this->assertNotNull($recipe);
        $this->assertSame($user->id, $recipe->user_id);
    }

    public function test_a_malformed_xml_returns_422_and_creates_nothing(): void
    {
        $user = User::factory()->create();

        $this->importRequest($user, '<?xml version="1.0"?><RECIPES><RECIPE>')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertSame(0, Recipe::count());
    }

    public function test_an_incomplete_structure_returns_422_after_rollback(): void
    {
        $user = User::factory()->create();
        $xml = str_replace('<USE>Boil</USE>', '', self::VALID_XML);

        $this->importRequest($user, $xml)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertSame(0, Recipe::count());
    }

    public function test_a_missing_file_returns_422(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/api/recipes/import', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_guests_get_401(): void
    {
        $this->postJson('/api/recipes/import', [])->assertUnauthorized();
    }
}
