<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class LivewireComponentsTest extends TestCase
{
    public function test_formtabs_class_exists(): void
    {
        $this->assertTrue(class_exists(\App\Livewire\FormTabs::class));
    }

    public function test_planinlinedetails_class_exists(): void
    {
        $this->assertTrue(class_exists(\App\Livewire\Dashboard\PlanInlineDetails::class));
    }
}
