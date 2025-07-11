<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Database\Factories;

use Bambamboole\LaravelOpenApi\Tests\TestClasses\Models\TestModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestModelFactory extends Factory
{
    protected $model = TestModel::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
