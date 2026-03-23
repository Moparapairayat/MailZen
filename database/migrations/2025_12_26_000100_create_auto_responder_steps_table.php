<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auto_responder_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auto_responder_id')->constrained('auto_responders')->onDelete('cascade');
            $table->unsignedInteger('step_order');
            $table->string('name')->nullable();

            $table->foreignId('template_id')->nullable()->constrained('templates')->onDelete('set null');
            $table->string('subject');
            $table->text('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();

            $table->integer('delay_value')->default(0);
            $table->enum('delay_unit', ['minutes', 'hours', 'days', 'weeks'])->default('hours');

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->longText('html_content')->nullable();
            $table->longText('plain_text_content')->nullable();
            $table->json('template_data')->nullable();
            $table->boolean('track_opens')->default(true);
            $table->boolean('track_clicks')->default(true);

            $table->timestamps();

            $table->unique(['auto_responder_id', 'step_order']);
            $table->index(['auto_responder_id', 'status']);
        });

        DB::transaction(function () {
            $autoResponders = DB::table('auto_responders')->select('id')->get();

            foreach ($autoResponders as $autoResponder) {
                $exists = DB::table('auto_responder_steps')
                    ->where('auto_responder_id', $autoResponder->id)
                    ->where('step_order', 1)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $row = DB::table('auto_responders')
                    ->where('id', $autoResponder->id)
                    ->first();

                if (!$row) {
                    continue;
                }

                DB::table('auto_responder_steps')->insert([
                    'auto_responder_id' => $autoResponder->id,
                    'step_order' => 1,
                    'name' => 'Step 1',
                    'template_id' => $row->template_id,
                    'subject' => $row->subject,
                    'from_name' => $row->from_name,
                    'from_email' => $row->from_email,
                    'reply_to' => $row->reply_to,
                    'delay_value' => $row->delay_value ?? 0,
                    'delay_unit' => $row->delay_unit ?? 'hours',
                    'status' => 'active',
                    'html_content' => $row->html_content,
                    'plain_text_content' => $row->plain_text_content,
                    'template_data' => $row->template_data,
                    'track_opens' => (bool) ($row->track_opens ?? true),
                    'track_clicks' => (bool) ($row->track_clicks ?? true),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_responder_steps');
    }
};
