<?php

namespace App\Models\toolkit\Traits;

use App\Models\toolkit\ToolkitItems;

/**
 * Class ToolkitorderRelationship
 */
trait ToolkitRelationship
{

    public function item()
    {
        return $this->hasMany(ToolkitItems::class, 'toolkit_id','id')->withoutGlobalScopes();
    }
 }
