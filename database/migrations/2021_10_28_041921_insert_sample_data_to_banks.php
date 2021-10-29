<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InsertSampleDataToBanks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banks', function (Blueprint $table) {
            //
        });

        // Sample data
        $row1 = [
            'name' => 'KCB Moi Avenue Nairobi',
            'bank' => 'KCB',
            'number' => '254122366',
            'code' => '100',
            'branch' => 'Moi Avenue Nairobi',
            'ins' => 1
        ];
        $row2 = [
            'name' => 'Co-operative Bank Embakasi Junction Branch',
            'bank' => 'Co-operative Bank',
            'number' => '254256888',
            'code' => '200',
            'branch' => 'Embakasi Junction Branch',
            'ins' => 1
        ];
        DB::table('banks')->insert([$row1, $row2]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banks', function (Blueprint $table) {
            //
        });
        // delete sample data
        DB::table('banks')->delete();
    }
}
