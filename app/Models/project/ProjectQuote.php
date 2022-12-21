<?php

namespace App\Models\project;

use Illuminate\Database\Eloquent\Model;

class ProjectQuote extends Model
{
    protected $table = 'project_quotes';

    protected $fillable = ['project_id', 'quote_id'];

    public $timestamps = false;

    // relation
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
