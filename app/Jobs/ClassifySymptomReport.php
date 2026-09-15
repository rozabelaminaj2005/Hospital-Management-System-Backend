<?php

namespace App\Jobs;

use App\Models\AiRecommendation;
use App\Models\SymptomReport;
use App\Services\SymptomClassifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched with ->afterResponse() so the "Analyzing…" step the frontend
 * expects completes without requiring a queue worker process for the demo.
 */
class ClassifySymptomReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(public int $symptomReportId)
    {
    }

    public function handle(SymptomClassifier $classifier): void
    {
        $report = SymptomReport::with('symptoms')->find($this->symptomReportId);

        if (! $report) {
            return;
        }

        $result = $classifier->classify(
            $report->symptoms->pluck('name'),
            $report->duration,
            $report->pain_level
        );

        AiRecommendation::create([
            'symptom_report_id' => $report->id,
            'urgency' => $result['urgency'],
            'confidence' => $result['confidence'],
            'recommended_department_id' => $result['department_id'],
            'recommended_doctor_id' => $result['doctor_id'],
            'suggested_time' => $result['suggested_time'],
            'reasoning' => $result['reasoning'],
            'diagnosis_hint' => $result['diagnosis_hint'],
        ]);

        $report->update(['status' => 'completed']);
    }
}
