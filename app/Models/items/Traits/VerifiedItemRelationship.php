<?php

namespace App\Models\items\Traits;

use App\Models\product\ProductVariation;
use App\Models\quote\Quote;

trait VerifiedItemRelationship
{
   public function product_variation()
   {
      return $this->belongsTo(ProductVariation::class, 'product_id');
   }

   public function quote()
   {
      return $this->belongsTo(Quote::class, 'quote_id');
   }
}
