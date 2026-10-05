<?php

namespace Tests\Unit;

use App\Support\Reicon;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ReiconTest extends TestCase
{
    public function test_reicon_can_render_outline_and_filled_icons(): void
    {
        $trophy = Reicon::render('trophy', 'size-5 text-amber-500');
        $this->assertStringContainsString('<svg', (string) $trophy);
        $this->assertStringContainsString('viewBox="0 0 24 24"', (string) $trophy);
        $this->assertStringContainsString('class="reicon inline-block size-5 text-amber-500"', (string) $trophy);

        $starFilled = Reicon::render('star', 'size-4 text-amber-400', 'filled');
        $this->assertStringContainsString('<svg', (string) $starFilled);

        $football = reicon('football', 'size-4 text-emerald-600');
        $this->assertStringContainsString('<svg', (string) $football);
    }

    public function test_blade_component_renders_reicon(): void
    {
        $rendered = Blade::render('<x-reicon name="trophy" class="size-6 text-emerald-600" />');
        $this->assertStringContainsString('<svg', $rendered);
        $this->assertStringContainsString('class="reicon inline-block size-6 text-emerald-600"', $rendered);
    }

    public function test_reicon_resolves_aliases(): void
    {
        $this->assertTrue(Reicon::exists('trofeo'));
        $this->assertTrue(Reicon::exists('estrella'));
        $this->assertTrue(Reicon::exists('futbol')); // alias or fallback
    }
}
