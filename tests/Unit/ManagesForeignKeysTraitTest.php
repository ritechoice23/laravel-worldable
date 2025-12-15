<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Ritechoice23\Worldable\Traits\ManagesForeignKeys;

beforeEach(function () {
    // Create test model with trait
    $this->testModel = new class extends Model
    {
        use ManagesForeignKeys;

        protected $table = 'test_models';

        protected $guarded = [];

        public function getForeignKeyDefinitions(): array
        {
            return [
                'parent_id' => ['table' => 'parent_table'],
            ];
        }
    };

    // Create test table
    if (! Schema::hasTable('test_models')) {
        Schema::create('test_models', function ($table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
        });
    }
});

afterEach(function () {
    Schema::dropIfExists('test_models');
    Schema::dropIfExists('parent_table');
});

it('sets foreign key to null when parent table does not exist', function () {
    // Make sure parent table doesn't exist
    Schema::dropIfExists('parent_table');

    $model = $this->testModel::create([
        'parent_id' => 123,
    ]);

    expect($model->parent_id)->toBeNull();
});

it('preserves foreign key value when parent table exists', function () {
    // Create parent table
    Schema::create('parent_table', function ($table) {
        $table->id();
    });

    $model = $this->testModel::create([
        'parent_id' => 123,
    ]);

    expect($model->parent_id)->toBe(123);
});

it('handles multiple foreign keys correctly', function () {
    $testModel = new class extends Model
    {
        use ManagesForeignKeys;

        protected $table = 'multi_fk_models';

        protected $guarded = [];

        public function getForeignKeyDefinitions(): array
        {
            return [
                'table1_id' => ['table' => 'table1'],
                'table2_id' => ['table' => 'table2'],
            ];
        }
    };

    Schema::create('multi_fk_models', function ($table) {
        $table->id();
        $table->unsignedBigInteger('table1_id')->nullable();
        $table->unsignedBigInteger('table2_id')->nullable();
        $table->timestamps();
    });

    // Create only table1, not table2
    Schema::create('table1', function ($table) {
        $table->id();
    });

    $model = $testModel::create([
        'table1_id' => 100,
        'table2_id' => 200,
    ]);

    expect($model->table1_id)->toBe(100);
    expect($model->table2_id)->toBeNull();

    Schema::dropIfExists('multi_fk_models');
    Schema::dropIfExists('table1');
});
