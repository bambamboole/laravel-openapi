<?php declare(strict_types=1);

namespace Bambamboole\LaravelOpenApi\Tests\TestClasses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    use HasFactory;

    protected $guarded = [];
}
