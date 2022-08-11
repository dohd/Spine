<?php

namespace App\Models\items\Traits;

use App\Models\goodsreceivenote\Goodsreceivenote;

trait SupplierbillItemRelationship
{
    public function grn()
    {
        return $this->belongsTo(Goodsreceivenote::class, 'goods_receive_note_id');
    }
}
