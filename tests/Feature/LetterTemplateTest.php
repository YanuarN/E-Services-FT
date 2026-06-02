<?php

namespace Tests\Feature;

use App\Models\LetterTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LetterTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_usage_request_template_type_can_be_persisted(): void
    {
        $template = LetterTemplate::query()->create([
            'letter_type' => 'room_usage_request',
            'document_path' => 'letter-templates/room-usage-request-template.docx',
        ]);

        $this->assertSame('room_usage_request', $template->letter_type);
        $this->assertDatabaseHas('letter_templates', [
            'id' => $template->id,
            'letter_type' => 'room_usage_request',
        ]);
    }
}
