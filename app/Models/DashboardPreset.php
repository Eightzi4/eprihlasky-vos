<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardPreset extends Model
{
    protected $fillable = [
        'label',
        'icon',
        'color_class',
        'checkpoint',
        'state',
        'study_program_id',
        'round_id',
        'sort_order',
    ];

    protected $casts = [
        'study_program_id' => 'integer',
        'round_id'         => 'integer',
        'sort_order'       => 'integer',
    ];

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function round()
    {
        return $this->belongsTo(ApplicationRound::class);
    }

    public function countApplications(): int
    {
        $query = Application::query();

        if ($this->checkpoint) {
            return $this->countViaCheckpoint($query);
        }

        if ($this->study_program_id) {
            $query->where('study_program_id', $this->study_program_id);
        }
        if ($this->round_id) {
            $query->where('round_id', $this->round_id);
        }

        return $query->count();
    }

    private function countViaCheckpoint($query): int
    {
        if ($this->checkpoint === 'submitted') {
            if ($this->state === 'complete') {
                $query->where('submitted', true);
            } elseif ($this->state === 'incomplete') {
                $query->where('submitted', false);
            }
        } elseif ($this->checkpoint === 'payment') {
            if ($this->state === 'pending') {
                $query->where('paid', true)->where('payment_accepted', false);
            } elseif ($this->state === 'complete') {
                $query->where('paid', true)->where('payment_accepted', true);
            } elseif ($this->state === 'incomplete') {
                $query->where('paid', false);
            }
        } elseif ($this->checkpoint === 'gdpr_accepted') {
            if ($this->state === 'complete') {
                $query->where('gdpr_accepted', true);
            } elseif ($this->state === 'incomplete') {
                $query->where('gdpr_accepted', false);
            }
        } elseif ($this->checkpoint === 'identity_verified') {
            if ($this->state === 'complete') {
                $query->where('identity_verified', true);
            } elseif ($this->state === 'incomplete') {
                $query->where('identity_verified', false);
            }
        }

        if ($this->study_program_id) {
            $query->where('study_program_id', $this->study_program_id);
        }
        if ($this->round_id) {
            $query->where('round_id', $this->round_id);
        }

        return $query->get()->filter(function (Application $app) {
            $statuses = $app->checkpointStatuses();
            return ($statuses[$this->checkpoint] ?? null) === $this->state;
        })->count();
    }
}
