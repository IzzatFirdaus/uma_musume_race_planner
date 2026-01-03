<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\AptitudeGrade;
use App\Enums\CareerYear;
use App\Enums\RunStatus;
use App\Enums\Scenario;
use App\Enums\SkillStatus;
use App\Enums\StorageMode;
use App\Enums\UmaClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for enum casts and validation.
 * Task 6.1.3: Test enum casts and validation.
 */
class EnumCastsTest extends TestCase
{
    public function test_skill_status_enum_values(): void
    {
        $this->assertEquals('acquired', SkillStatus::Acquired->value);
        $this->assertEquals('skipped', SkillStatus::Skipped->value);
        $this->assertEquals('suggested', SkillStatus::Suggested->value);
    }

    public function test_skill_status_labels(): void
    {
        $this->assertEquals('Acquired', SkillStatus::Acquired->label());
        $this->assertEquals('Skipped', SkillStatus::Skipped->label());
        $this->assertEquals('Suggested', SkillStatus::Suggested->label());
    }

    public function test_skill_status_requires_turn_acquired(): void
    {
        $this->assertTrue(SkillStatus::Acquired->requiresTurnAcquired());
        $this->assertFalse(SkillStatus::Skipped->requiresTurnAcquired());
        $this->assertFalse(SkillStatus::Suggested->requiresTurnAcquired());
    }

    public function test_skill_status_values_array(): void
    {
        $values = SkillStatus::values();
        $this->assertContains('acquired', $values);
        $this->assertContains('skipped', $values);
        $this->assertContains('suggested', $values);
        $this->assertCount(3, $values);
    }

    public function test_career_year_enum_values(): void
    {
        $this->assertEquals('junior', CareerYear::Junior->value);
        $this->assertEquals('classic', CareerYear::Classic->value);
        $this->assertEquals('senior', CareerYear::Senior->value);
    }

    public function test_career_year_labels(): void
    {
        $this->assertEquals('Junior', CareerYear::Junior->label());
        $this->assertEquals('Classic', CareerYear::Classic->label());
        $this->assertEquals('Senior', CareerYear::Senior->label());
    }

    public function test_run_status_enum_values(): void
    {
        $this->assertEquals('ongoing', RunStatus::Ongoing->value);
        $this->assertEquals('finished', RunStatus::Finished->value);
        $this->assertEquals('failed', RunStatus::Failed->value);
    }

    public function test_run_status_labels(): void
    {
        $this->assertEquals('Ongoing', RunStatus::Ongoing->label());
        $this->assertEquals('Finished', RunStatus::Finished->label());
        $this->assertEquals('Failed', RunStatus::Failed->label());
    }

    public function test_uma_class_enum_values(): void
    {
        $this->assertEquals('debut', UmaClass::Debut->value);
        $this->assertEquals('beginner', UmaClass::Beginner->value);
        $this->assertEquals('bronze', UmaClass::Bronze->value);
        $this->assertEquals('silver', UmaClass::Silver->value);
        $this->assertEquals('gold', UmaClass::Gold->value);
        $this->assertEquals('platinum', UmaClass::Platinum->value);
        $this->assertEquals('legend', UmaClass::Legend->value);
    }

    public function test_aptitude_grade_enum_values(): void
    {
        $this->assertEquals('S', AptitudeGrade::S->value);
        $this->assertEquals('A', AptitudeGrade::A->value);
        $this->assertEquals('B', AptitudeGrade::B->value);
        $this->assertEquals('C', AptitudeGrade::C->value);
        $this->assertEquals('D', AptitudeGrade::D->value);
        $this->assertEquals('E', AptitudeGrade::E->value);
        $this->assertEquals('F', AptitudeGrade::F->value);
        $this->assertEquals('G', AptitudeGrade::G->value);
    }

    public function test_scenario_enum_values(): void
    {
        $this->assertEquals('URA', Scenario::URA->value);
    }

    public function test_scenario_labels(): void
    {
        $this->assertEquals('URA Finals', Scenario::URA->label());
    }

    public function test_storage_mode_enum_values(): void
    {
        $this->assertEquals('local', StorageMode::Local->value);
        $this->assertEquals('account', StorageMode::Account->value);
    }

    public function test_storage_mode_labels(): void
    {
        $this->assertEquals('Local', StorageMode::Local->label());
        $this->assertEquals('Account', StorageMode::Account->label());
    }

    public function test_enum_from_string(): void
    {
        $this->assertEquals(SkillStatus::Acquired, SkillStatus::from('acquired'));
        $this->assertEquals(StorageMode::Local, StorageMode::from('local'));
        $this->assertEquals(Scenario::URA, Scenario::from('URA'));
    }

    public function test_enum_try_from_invalid_returns_null(): void
    {
        $this->assertNull(SkillStatus::tryFrom('invalid'));
        $this->assertNull(StorageMode::tryFrom('invalid'));
    }
}
