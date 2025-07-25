<?php declare(strict_types=1);

use Bambamboole\LaravelOpenApi\Tests\TestClasses\Models\TestModel;

beforeEach(function () {
    $this->models = TestModel::factory()->count(5)->create();
});

it('can filter models via equals operator', function () {
    $models = createQueryFromFilterRequest([
        [
            'key' => 'name',
            'op' => 'equal',
            'value' => $this->models->first()->name,
        ],
    ])
        ->allowedFilters('name')
        ->get();

    expect($models)->toHaveCount(1);
});
//
// it('can use a custom filter query string parameter', function () {
//    config(['query-builder.parameters.filter' => 'custom_filter']);
//
//    $request = new Request([
//        'custom_filter' => ['name' => $this->models->first()->name],
//    ]);
//
//    $models = QueryBuilder::for(TestModel::class, $request)
//        ->allowedFilters('name')
//        ->get();
//
//    expect($models)->toHaveCount(1);
// });
//
// it('can work without a general filter query string parameter configured', function () {
//    config(['query-builder.parameters.filter' => null]);
//
//    $request = new Request([
//        'name' => $this->models->first()->name,
//    ]);
//
//    $models = QueryBuilder::for(TestModel::class, $request)
//        ->allowedFilters('name')
//        ->get();
//
//    expect($models)->toHaveCount(1);
// });
//
// it('can filter models by an array as filter value', function () {
//    $models = createQueryFromFilterRequest([
//        'name' => ['first' => $this->models->first()->name],
//    ])
//        ->allowedFilters('name')
//        ->get();
//
//    expect($models)->toHaveCount(1);
// });
//
// it('can filter partially and case insensitive', function () {
//    $models = createQueryFromFilterRequest([
//        'name' => strtoupper($this->models->first()->name),
//    ])
//        ->allowedFilters('name')
//        ->get();
//
//    expect($models)->toHaveCount(1);
// });
//
// it('can filter results based on the partial existence of a property in an array', function () {
//    $model1 = TestModel::create(['name' => 'abcdef']);
//    $model2 = TestModel::create(['name' => 'uvwxyz']);
//
//    $results = createQueryFromFilterRequest([
//        'name' => 'abc,xyz',
//    ])
//        ->allowedFilters('name')
//        ->get();
//
//    expect($results)->toHaveCount(2);
//    expect($results->pluck('id')->all())->toEqual([$model1->id, $model2->id]);
// });
//
// it('can filter models and return an empty collection', function () {
//    $models = createQueryFromFilterRequest([
//        'name' => 'None existing first name',
//    ])
//        ->allowedFilters('name')
//        ->get();
//
//    expect($models)->toHaveCount(0);
// });
//
// it('can filter a custom base query with select', function () {
//    $request = new Request([
//        'filter' => ['name' => 'john'],
//    ]);
//
//    $queryBuilderSql = QueryBuilder::for(TestModel::select('id', 'name'), $request)
//        ->allowedFilters('name', 'id')
//        ->toSql();
//
//    $expectedSql = TestModel::select('id', 'name')
//        ->where(DB::raw('LOWER(`test_models`.`name`)'), 'LIKE', 'john')
//        ->toSql();
//
//    expect($queryBuilderSql)->toContain($expectedSql);
// });
//
// it('specifies escape character in supported databases', function (string $dbDriver) {
//    if ($dbDriver === 'mariadb' && ! in_array('mariadb', DB::supportedDrivers())) {
//        $this->markTestSkipped('mariadb driver not supported in the installed version of illuminate/database dependency');
//    }
//
//    $fakeConnection = "test_{$dbDriver}";
//
//    DB::connectUsing($fakeConnection, [
//        'driver' => $dbDriver,
//        'database' => null,
//    ]);
//
//    DB::usingConnection($fakeConnection, function () use ($dbDriver) {
//
//        $request = new Request([
//            'filter' => ['name' => 'to_find'],
//        ]);
//
//        $queryBuilderSql = QueryBuilder::for(TestModel::select('id', 'name'), $request)
//            ->allowedFilters('name', 'id')
//            ->toSql();
//
//        expect($queryBuilderSql)->when(in_array($dbDriver, ["sqlite", "sqlsrv"]), fn (
//            Expectation $query
//        ) => $query->toContain("ESCAPE '\'"));
//        expect($queryBuilderSql)->when(in_array($dbDriver, ["mysql", "mariadb", "pgsql"]), fn (
//            Expectation $query
//        ) => $query->not->toContain("ESCAPE '\'"));
//    });
// })->with(['sqlite', 'mysql', 'pgsql', 'sqlsrv', 'mariadb']);
//
// it('can filter results based on the existence of a property in an array', function () {
//    $results = createQueryFromFilterRequest([
//        'id' => '1,2',
//    ])
//        ->allowedFilters(AllowedFilter::exact('id'))
//        ->get();
//
//    expect($results)->toHaveCount(2);
//    expect($results->pluck('id')->all())->toEqual([1, 2]);
// });
//
// it('ignores empty values in an array partial filter', function () {
//    $results = createQueryFromFilterRequest([
//        'id' => '2,',
//    ])
//        ->allowedFilters(AllowedFilter::partial('id'))
//        ->get();
//
//    expect($results)->toHaveCount(1);
//    expect($results->pluck('id')->all())->toEqual([2]);
// });
//
// it('ignores an empty array partial filter', function () {
//    $results = createQueryFromFilterRequest([
//        'id' => ',,',
//    ])
//        ->allowedFilters(AllowedFilter::partial('id'))
//        ->get();
//
//    expect($results)->toHaveCount(5);
// });
//
// test('falsy values are not ignored when applying a partial filter', function () {
//    DB::enableQueryLog();
//
//    createQueryFromFilterRequest([
//        'id' => [0],
//    ])
//        ->allowedFilters(AllowedFilter::partial('id'))
//        ->get();
//
//    $this->assertQueryLogContains("select * from `test_models` where (LOWER(`test_models`.`id`) LIKE ?)");
// });
//
// test('falsy values are not ignored when applying a begins with strict filter', function () {
//    DB::enableQueryLog();
//
//    createQueryFromFilterRequest([
//        'id' => [0],
//    ])
//        ->allowedFilters(AllowedFilter::beginsWithStrict('id'))
//        ->get();
//
//    $this->assertQueryLogContains("select * from `test_models` where (`test_models`.`id` LIKE ?)");
// });
//
// test('falsy values are not ignored when applying a ends with strict filter', function () {
//    DB::enableQueryLog();
//
//    createQueryFromFilterRequest([
//        'id' => [0],
//    ])
//        ->allowedFilters(AllowedFilter::endsWithStrict('id'))
//        ->get();
//
//    $this->assertQueryLogContains("select * from `test_models` where (`test_models`.`id` LIKE ?)");
// });
//
// it('can filter partial using begins with strict', function () {
//    TestModel::create([
//        'name' => 'John Doe',
//    ]);
//
//    $models = createQueryFromFilterRequest(['name' => 'john'])
//        ->allowedFilters([
//            AllowedFilter::beginsWithStrict('name'),
//        ]);
//
//    $models2 = createQueryFromFilterRequest(['name' => 'doe'])
//        ->allowedFilters([
//            AllowedFilter::beginsWithStrict('name'),
//        ]);
//
//    expect($models->count())->toBe(1);
//    expect($models2->count())->toBe(0);
// });
//
// it('can filter partial using ends with strict', function () {
//    TestModel::create([
//        'name' => 'John Doe',
//    ]);
//
//    $models = createQueryFromFilterRequest(['name' => 'doe'])
//        ->allowedFilters([
//            AllowedFilter::endsWithStrict('name'),
//        ]);
//
//    $models2 = createQueryFromFilterRequest(['name' => 'john'])
//        ->allowedFilters([
//            AllowedFilter::endsWithStrict('name'),
//        ]);
//
//    expect($models->count())->toBe(1);
//    expect($models2->count())->toBe(0);
// });
//
// it('can filter and match results by exact property', function () {
//    $testModel = TestModel::first();
//
//    $models = TestModel::where('id', $testModel->id)
//        ->get();
//
//    $modelsResult = createQueryFromFilterRequest([
//        'id' => $testModel->id,
//    ])
//        ->allowedFilters(AllowedFilter::exact('id'))
//        ->get();
//
//    expect($models)->toEqual($modelsResult, $models);
// });
//
// it('can filter and reject results by exact property', function () {
//    $testModel = TestModel::create(['name' => 'John Testing Doe']);
//
//    $modelsResult = createQueryFromFilterRequest([
//        'name' => ' Testing ',
//    ])
//        ->allowedFilters(AllowedFilter::exact('name'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(0);
// });
//
// it('can filter results by belongs to', function () {
//    $relatedModel = RelatedModel::create(['name' => 'John Related Doe', 'test_model_id' => 0]);
//    $nestedModel = NestedRelatedModel::create(['name' => 'John Nested Doe', 'related_model_id' => $relatedModel->id]);
//
//    $modelsResult = createQueryFromFilterRequest(['relatedModel' => $relatedModel->id], NestedRelatedModel::class)
//        ->allowedFilters(AllowedFilter::belongsTo('relatedModel'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(1);
// });
//
// it('can filter results by belongs to no match', function () {
//    $relatedModel = RelatedModel::create(['name' => 'John Related Doe', 'test_model_id' => 0]);
//    $nestedModel = NestedRelatedModel::create(['name' => 'John Nested Doe', 'related_model_id' => $relatedModel->id + 1]);
//
//    $modelsResult = createQueryFromFilterRequest(['relatedModel' => $relatedModel->id], NestedRelatedModel::class)
//        ->allowedFilters(AllowedFilter::belongsTo('relatedModel'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(0);
// });
//
// it('can filter results by belongs multiple', function () {
//    $relatedModel1 = RelatedModel::create(['name' => 'John Related Doe 1', 'test_model_id' => 0]);
//    $nestedModel1 = NestedRelatedModel::create(['name' => 'John Nested Doe 1', 'related_model_id' => $relatedModel1->id]);
//    $relatedModel2 = RelatedModel::create(['name' => 'John Related Doe 2', 'test_model_id' => 0]);
//    $nestedModel2 = NestedRelatedModel::create(['name' => 'John Nested Doe 2', 'related_model_id' => $relatedModel2->id]);
//
//    $modelsResult = createQueryFromFilterRequest(['relatedModel' => $relatedModel1->id.','.$relatedModel2->id], NestedRelatedModel::class)
//        ->allowedFilters(AllowedFilter::belongsTo('relatedModel'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(2);
// });
//
// it('can filter results by belongs multiple with different internal name', function () {
//    $relatedModel1 = RelatedModel::create(['name' => 'John Related Doe 1', 'test_model_id' => 0]);
//    $nestedModel1 = NestedRelatedModel::create(['name' => 'John Nested Doe 1', 'related_model_id' => $relatedModel1->id]);
//    $relatedModel2 = RelatedModel::create(['name' => 'John Related Doe 2', 'test_model_id' => 0]);
//    $nestedModel2 = NestedRelatedModel::create(['name' => 'John Nested Doe 2', 'related_model_id' => $relatedModel2->id]);
//
//    $modelsResult = createQueryFromFilterRequest(['testFilter' => $relatedModel1->id.','.$relatedModel2->id], NestedRelatedModel::class)
//        ->allowedFilters(AllowedFilter::belongsTo('testFilter', 'relatedModel'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(2);
// });
//
// it('can filter results by belongs multiple with different internal name and nested model', function () {
//    $testModel1 = TestModel::create(['name' => 'John Test Doe 1']);
//    $relatedModel1 = RelatedModel::create(['name' => 'John Related Doe 1', 'test_model_id' => $testModel1->id]);
//    $nestedModel1 = NestedRelatedModel::create(['name' => 'John Nested Doe 1', 'related_model_id' => $relatedModel1->id]);
//    $testModel2 = TestModel::create(['name' => 'John Test Doe 2']);
//    $relatedModel2 = RelatedModel::create(['name' => 'John Related Doe 2', 'test_model_id' => $testModel2->id]);
//    $nestedModel2 = NestedRelatedModel::create(['name' => 'John Nested Doe 2', 'related_model_id' => $relatedModel2->id]);
//
//    $modelsResult = createQueryFromFilterRequest(['test_filter' => $testModel1->id.','.$testModel2->id], NestedRelatedModel::class)
//        ->allowedFilters(AllowedFilter::belongsTo('test_filter', 'relatedModel.testModel'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(2);
// });
//
// it('throws an exception when trying to filter by belongs to with an inexistent relation', function ($relationName, $exceptionClass) {
//    $this->expectException($exceptionClass);
//
//    $modelsResult = createQueryFromFilterRequest(['test_filter' => 1], RelatedModel::class)
//        ->allowedFilters(AllowedFilter::belongsTo('test_filter', $relationName))
//        ->get();
//
// })->with([
//    ['inexistentRelation', \BadMethodCallException::class],
//    ['testModel.inexistentRelation', \BadMethodCallException::class], // existing 'testModel' belongsTo relation
//    ['inexistentRelation.inexistentRelation', \BadMethodCallException::class],
//    ['getTable', \Illuminate\Database\Eloquent\RelationNotFoundException::class],
//    ['testModel.getTable', \Illuminate\Database\Eloquent\RelationNotFoundException::class], // existing 'testModel' belongsTo relation
//    ['getTable.getTable', \Illuminate\Database\Eloquent\RelationNotFoundException::class],
//    ['nestedRelatedModels', \Illuminate\Database\Eloquent\RelationNotFoundException::class], // existing 'nestedRelatedModels' relation but not a belongsTo relation
//    ['testModel.relatedModels', \Illuminate\Database\Eloquent\RelationNotFoundException::class], // existing 'testModel' belongsTo relation and existing 'relatedModels' relation but not a belongsTo relation
// ]);
//
// it('can filter results by scope', function () {
//    $testModel = TestModel::create(['name' => 'John Testing Doe']);
//
//    $modelsResult = createQueryFromFilterRequest(['named' => 'John Testing Doe'])
//        ->allowedFilters(AllowedFilter::scope('named'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(1);
// });
//
// it('can filter results by nested relation scope', function () {
//    $testModel = TestModel::create(['name' => 'John Testing Doe']);
//
//    $testModel->relatedModels()->create(['name' => 'John\'s Post']);
//
//    $modelsResult = createQueryFromFilterRequest(['relatedModels.named' => 'John\'s Post'])
//        ->allowedFilters(AllowedFilter::scope('relatedModels.named'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(1);
// });
//
// it('can filter results by type hinted scope', function () {
//    TestModel::create(['name' => 'John Testing Doe']);
//
//    $modelsResult = createQueryFromFilterRequest(['user' => 1])
//        ->allowedFilters(AllowedFilter::scope('user'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(1);
// });
//
// it('can filter results by regular and type hinted scope', function () {
//    TestModel::create(['id' => 1000, 'name' => 'John Testing Doe']);
//
//    $modelsResult = createQueryFromFilterRequest(['user_info' => ['id' => '1000', 'name' => 'John Testing Doe']])
//        ->allowedFilters(AllowedFilter::scope('user_info'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(1);
// });
//
// it('can filter results by scope with multiple parameters', function () {
//    Carbon::setTestNow(Carbon::parse('2016-05-05'));
//
//    $testModel = TestModel::create(['name' => 'John Testing Doe']);
//
//    $modelsResult = createQueryFromFilterRequest(['created_between' => '2016-01-01,2017-01-01'])
//        ->allowedFilters(AllowedFilter::scope('created_between'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(1);
// });
//
// it('can filter results by scope with multiple parameters in an associative array', function () {
//    Carbon::setTestNow(Carbon::parse('2016-05-05'));
//
//    $testModel = TestModel::create(['name' => 'John Testing Doe']);
//
//    $modelsResult = createQueryFromFilterRequest(['created_between' => ['start' => '2016-01-01', 'end' => '2017-01-01']])
//        ->allowedFilters(AllowedFilter::scope('created_between'))
//        ->get();
//
//    expect($modelsResult)->toHaveCount(1);
// });
//
// it('can filter results by a custom filter class', function () {
//    $testModel = $this->models->first();
//
//    $filterClass = new class () implements FilterInterface {
//        public function __invoke(Builder $query, $value, string $property): Builder
//        {
//            return $query->where('name', $value);
//        }
//    };
//
//    $modelResult = createQueryFromFilterRequest([
//        'custom_name' => $testModel->name,
//    ])
//        ->allowedFilters(AllowedFilter::custom('custom_name', $filterClass))
//        ->first();
//
//    expect($modelResult->id)->toEqual($testModel->id);
// });
//
// it('can allow multiple filters', function () {
//    $model1 = TestModel::create(['name' => 'abcdef']);
//    $model2 = TestModel::create(['name' => 'abcdef']);
//
//    $results = createQueryFromFilterRequest([
//        'name' => 'abc',
//    ])
//        ->allowedFilters('name', AllowedFilter::exact('id'))
//        ->get();
//
//    expect($results)->toHaveCount(2);
//    expect($results->pluck('id')->all())->toEqual([$model1->id, $model2->id]);
// });
//
// it('can allow multiple filters as an array', function () {
//    $model1 = TestModel::create(['name' => 'abcdef']);
//    $model2 = TestModel::create(['name' => 'abcdef']);
//
//    $results = createQueryFromFilterRequest([
//        'name' => 'abc',
//    ])
//        ->allowedFilters(['name', AllowedFilter::exact('id')])
//        ->get();
//
//    expect($results)->toHaveCount(2);
//    expect($results->pluck('id')->all())->toEqual([$model1->id, $model2->id]);
// });
//
// it('can allow multiple filters as nested array', function () {
//    $model1 = TestModel::create(['name' => 'abcdef']);
//    $model2 = TestModel::create(['name' => 'abcdef']);
//
//    $results = createQueryFromFilterRequest([
//        'name' => 'abc',
//    ])
//        ->allowedFilters([['name'], [AllowedFilter::exact('id')]])
//        ->get();
//
//    expect($results)->toHaveCount(2);
//    expect($results->pluck('id')->all())->toEqual([$model1->id, $model2->id]);
// });
//
// it('can filter by multiple filters', function () {
//    $model1 = TestModel::create(['name' => 'abcdef']);
//    $model2 = TestModel::create(['name' => 'abcdef']);
//
//    $results = createQueryFromFilterRequest([
//        'name' => 'abc',
//        'id' => "1,{$model1->id}",
//    ])
//        ->allowedFilters('name', AllowedFilter::exact('id'))
//        ->get();
//
//    expect($results)->toHaveCount(1);
//    expect($results->pluck('id')->all())->toEqual([$model1->id]);
// });
//
// it('guards against invalid filters', function () {
//    $this->expectException(InvalidFilterQuery::class);
//
//    createQueryFromFilterRequest(['name' => 'John'])
//        ->allowedFilters('id');
// });
//
// it('does not throw invalid filter exception when disable in config', function () {
//    config(['query-builder.disable_invalid_filter_query_exception' => true]);
//
//    createQueryFromFilterRequest(['name' => 'John'])
//        ->allowedFilters('id');
//
//    expect(true)->toBeTrue();
// });
//
// it('can create a custom filter with an instantiated filter', function () {
//    $customFilter = new class ('test1') implements CustomFilter {
//        /** @var string */
//        protected $filter;
//
//        public function __construct(string $filter)
//        {
//            $this->filter = $filter;
//        }
//
//        public function __invoke(Builder $query, $value, string $property): Builder
//        {
//            return $query;
//        }
//    };
//
//    TestModel::create(['name' => 'abcdef']);
//
//    $results = createQueryFromFilterRequest([
//        '*' => '*',
//    ])
//        ->allowedFilters('name', AllowedFilter::custom('*', $customFilter))
//        ->get();
//
//    $this->assertNotEmpty($results);
// });
//
// test('an invalid filter query exception contains the unknown and allowed filters', function () {
//    $exception = new InvalidFilterQuery(collect(['unknown filter']), collect(['allowed filter']));
//
//    expect($exception->unknownFilters->all())->toEqual(['unknown filter']);
//    expect($exception->allowedFilters->all())->toEqual(['allowed filter']);
// });
//
// it('allows for adding ignorable values', function () {
//    $shouldBeIgnored = ['', '-1', null, 'ignored_string', 'another_ignored_string'];
//
//    $filter = AllowedFilter::exact('name')->ignore($shouldBeIgnored[0]);
//    $filter
//        ->ignore($shouldBeIgnored[1], $shouldBeIgnored[2])
//        ->ignore([$shouldBeIgnored[3], $shouldBeIgnored[4]]);
//
//    $valuesIgnoredByFilter = $filter->getIgnored();
//
//    expect(sort($valuesIgnoredByFilter))->toEqual(sort($shouldBeIgnored));
// });
//
// it('should not apply a filter if the supplied value is ignored', function () {
//    $models = createQueryFromFilterRequest([
//        'name' => '-1',
//    ])
//        ->allowedFilters(AllowedFilter::exact('name')->ignore('-1'))
//        ->get();
//
//    expect($models)->toHaveCount(TestModel::count());
// });
//
// it('should apply the filter on the subset of allowed values', function () {
//    TestModel::create(['name' => 'John Doe']);
//    TestModel::create(['name' => 'John Deer']);
//
//    $models = createQueryFromFilterRequest([
//        'name' => 'John Deer,John Doe',
//    ])
//        ->allowedFilters(AllowedFilter::exact('name')->ignore('John Doe'))
//        ->get();
//
//    expect($models)->toHaveCount(1);
// });
//
// it('should apply the filter on the subset of allowed values regardless of the keys order', function () {
//    TestModel::create(['id' => 6, 'name' => 'John Doe']);
//    TestModel::create(['id' => 7, 'name' => 'John Deer']);
//
//    $models = createQueryFromFilterRequest([
//        'id' => [7, 6],
//    ])
//        ->allowedFilters(AllowedFilter::exact('id')->ignore(6))
//        ->get();
//
//    expect($models)->toHaveCount(1);
// });
//
// it('can take an argument for custom column name resolution', function () {
//    $filter = AllowedFilter::custom('property_name', new FiltersExact(), 'property_column_name');
//
//    expect($filter)->toBeInstanceOf(AllowedFilter::class);
//    assertObjectHasProperty('internalName', $filter);
// });
//
// it('sets property column name to property name by default', function () {
//    $filter = AllowedFilter::custom('property_name', new FiltersExact());
//
//    expect($filter->getInternalName())->toEqual($filter->getName());
// });
//
// it('resolves queries using property column name', function () {
//    $filter = AllowedFilter::custom('nickname', new FiltersExact(), 'name');
//
//    TestModel::create(['name' => 'abcdef']);
//
//    $models = createQueryFromFilterRequest([
//        'nickname' => 'abcdef',
//    ])
//        ->allowedFilters($filter)
//        ->get();
//
//    expect($models)->toHaveCount(1);
// });
//
// it('can filter using boolean flags', function () {
//    TestModel::query()->update(['is_visible' => true]);
//    $filter = AllowedFilter::exact('is_visible');
//
//    $models = createQueryFromFilterRequest(['is_visible' => 'false'])
//        ->allowedFilters($filter)
//        ->get();
//
//    expect($models)->toHaveCount(0);
//    expect(TestModel::all()->count())->toBeGreaterThan(0);
// });
//
// it('should apply a default filter value if nothing in request', function () {
//    TestModel::create(['name' => 'UniqueJohn Doe']);
//    TestModel::create(['name' => 'UniqueJohn Deer']);
//
//    $models = createQueryFromFilterRequest([])
//        ->allowedFilters(AllowedFilter::partial('name')->default('UniqueJohn'))
//        ->get();
//
//    expect($models->count())->toEqual(2);
// });
//
// it('does not apply default filter when filter exists and default is set', function () {
//    TestModel::create(['name' => 'UniqueJohn UniqueDoe']);
//    TestModel::create(['name' => 'UniqueJohn Deer']);
//
//    $models = createQueryFromFilterRequest([
//        'name' => 'UniqueDoe',
//    ])
//        ->allowedFilters(AllowedFilter::partial('name')->default('UniqueJohn'))
//        ->get();
//
//    expect($models->count())->toEqual(1);
// });
//
// it('should apply a null default filter value if nothing in request', function () {
//    TestModel::create(['name' => 'UniqueJohn Doe']);
//    TestModel::create(['name' => null]);
//
//    $models = createQueryFromFilterRequest([])
//        ->allowedFilters(AllowedFilter::exact('name')->default(null))
//        ->get();
//
//    expect($models->count())->toEqual(1);
// });
//
// it('does not apply default filter when filter exists and default null is set', function () {
//    TestModel::create(['name' => null]);
//    TestModel::create(['name' => 'UniqueJohn Deer']);
//
//    $models = createQueryFromFilterRequest([
//        'name' => 'UniqueJohn Deer',
//    ])
//        ->allowedFilters(AllowedFilter::exact('name')->default(null))
//        ->get();
//
//    expect($models->count())->toEqual(1);
// });
//
// it('should apply a nullable filter when filter exists and is null', function () {
//    DB::enableQueryLog();
//
//    TestModel::create(['name' => null]);
//    TestModel::create(['name' => 'UniqueJohn Deer']);
//
//    $models = createQueryFromFilterRequest([
//        'name' => null,
//    ])
//        ->allowedFilters(AllowedFilter::exact('name')->nullable())
//        ->get();
//
//    $this->assertQueryLogContains("select * from `test_models` where `test_models`.`name` is null");
//    expect($models->count())->toEqual(1);
// });
//
// it('should apply a nullable filter when filter exists and is set', function () {
//    TestModel::create(['name' => null]);
//    TestModel::create(['name' => 'UniqueJohn Deer']);
//
//    $models = createQueryFromFilterRequest([
//        'name' => 'UniqueJohn Deer',
//    ])
//        ->allowedFilters(AllowedFilter::exact('name')->nullable())
//        ->get();
//
//    expect($models->count())->toEqual(1);
// });
//
// it('should filter by query parameters if a default value is set and unset afterwards', function () {
//    TestModel::create(['name' => 'John Doe']);
//
//    $filterWithDefault = AllowedFilter::exact('name')->default('some default value');
//    $models = createQueryFromFilterRequest([
//        'name' => 'John Doe',
//    ])
//        ->allowedFilters($filterWithDefault->unsetDefault())
//        ->get();
//
//    expect($models->count())->toEqual(1);
// });
//
// it('should not filter at all if a default value is set and unset afterwards', function () {
//    $filterWithDefault = AllowedFilter::exact('name')->default('some default value');
//    $models = createQueryFromFilterRequest([])
//        ->allowedFilters($filterWithDefault->unsetDefault())
//        ->get();
//
//    expect($models->count())->toEqual(5);
// });
//
// it('should apply a filter with a multi-dimensional array value', function () {
//    TestModel::create(['name' => 'John Doe']);
//
//    $models = createQueryFromFilterRequest(['conditions' => [[
//        'attribute' => 'name',
//        'operator' => '=',
//        'value' => 'John Doe',
//    ]]])
//        ->allowedFilters(AllowedFilter::callback('conditions', function ($query, $conditions) {
//            foreach ($conditions as $condition) {
//                $query->where(
//                    $condition['attribute'],
//                    $condition['operator'],
//                    $condition['value']
//                );
//            }
//        }))
//        ->get();
//
//    expect($models->count())->toEqual(1);
// });
//
// it('can override the array value delimiter for single filters', function () {
//    TestModel::create(['name' => '>XZII/Q1On']);
//    TestModel::create(['name' => 'h4S4MG3(+>azv4z/I<o>']);
//
//    // First use default delimiter
//    $models = createQueryFromFilterRequest([
//        'ref_id' => 'h4S4MG3(+>azv4z/I<o>,>XZII/Q1On',
//    ])
//        ->allowedFilters(AllowedFilter::exact('ref_id', 'name', true))
//        ->get();
//    expect($models->count())->toEqual(2);
//
//    // Custom delimiter
//    $models = createQueryFromFilterRequest([
//        'ref_id' => 'h4S4MG3(+>azv4z/I<o>|>XZII/Q1On',
//    ])
//        ->allowedFilters(AllowedFilter::exact('ref_id', 'name', true, '|'))
//        ->get();
//    expect($models->count())->toEqual(2);
//
//    // Custom delimiter, but default in request
//    $models = createQueryFromFilterRequest([
//        'ref_id' => 'h4S4MG3(+>azv4z/I<o>,>XZII/Q1On',
//    ])
//        ->allowedFilters(AllowedFilter::exact('ref_id', 'name', true, '|'))
//        ->get();
//    expect($models->count())->toEqual(0);
// });
//
// it('can filter name with equal operator filter', function () {
//    TestModel::create(['name' => 'John Doe']);
//
//    $results = createQueryFromFilterRequest([
//        'name' => 'John Doe',
//    ])
//        ->allowedFilters(AllowedFilter::operator('name', FilterOperator::EQUAL))
//        ->get();
//
//    expect($results)->toHaveCount(1);
// });
//
// it('can filter name with not equal operator filter', function () {
//    TestModel::create(['name' => 'John Doe']);
//
//    $results = createQueryFromFilterRequest([
//        'name' => 'John Doe',
//    ])
//        ->allowedFilters(AllowedFilter::operator('name', FilterOperator::NOT_EQUAL))
//        ->get();
//
//    expect($results)->toHaveCount(5);
// });
//
// it('can filter salary with greater than operator filter', function () {
//    TestModel::create(['salary' => 5000]);
//
//    $results = createQueryFromFilterRequest([
//        'salary' => 3000,
//    ])
//        ->allowedFilters(AllowedFilter::operator('salary', FilterOperator::GREATER_THAN))
//        ->get();
//
//    expect($results)->toHaveCount(1);
// });
//
// it('can filter salary with less than operator filter', function () {
//    TestModel::create(['salary' => 5000]);
//
//    $results = createQueryFromFilterRequest([
//        'salary' => 7000,
//    ])
//        ->allowedFilters(AllowedFilter::operator('salary', FilterOperator::LESS_THAN))
//        ->get();
//
//    expect($results)->toHaveCount(1);
// });
//
// it('can filter salary with greater than or equal operator filter', function () {
//    TestModel::create(['salary' => 5000]);
//
//    $results = createQueryFromFilterRequest([
//        'salary' => 3000,
//    ])
//        ->allowedFilters(AllowedFilter::operator('salary', FilterOperator::GREATER_THAN_OR_EQUAL))
//        ->get();
//
//    expect($results)->toHaveCount(1);
// });
//
// it('can filter salary with less than or equal operator filter', function () {
//    TestModel::create(['salary' => 5000]);
//
//    $results = createQueryFromFilterRequest([
//        'salary' => 7000,
//    ])
//        ->allowedFilters(AllowedFilter::operator('salary', FilterOperator::LESS_THAN_OR_EQUAL))
//        ->get();
//
//    expect($results)->toHaveCount(1);
// });
//
// it('can filter array of names with equal operator filter', function () {
//    TestModel::create(['name' => 'John Doe']);
//    TestModel::create(['name' => 'Max Doe']);
//
//    $results = createQueryFromFilterRequest([
//        'name' => 'John Doe,Max Doe',
//    ])
//        ->allowedFilters(AllowedFilter::operator('name', FilterOperator::EQUAL, 'or'))
//        ->get();
//
//    expect($results)->toHaveCount(2);
// });
//
// it('can filter salary with dynamic operator filter', function () {
//    TestModel::create(['salary' => 5000]);
//    TestModel::create(['salary' => 2000]);
//
//    $results = createQueryFromFilterRequest([
//        'salary' => '>2000',
//    ])
//        ->allowedFilters(AllowedFilter::operator('salary', FilterOperator::DYNAMIC))
//        ->get();
//
//    expect($results)->toHaveCount(1);
// });
//
// it('can filter salary with dynamic array operator filter', function () {
//    TestModel::create(['salary' => 1000]);
//    TestModel::create(['salary' => 2000]);
//    TestModel::create(['salary' => 3000]);
//    TestModel::create(['salary' => 4000]);
//
//    $results = createQueryFromFilterRequest([
//        'salary' => '>1000,<4000',
//    ])
//        ->allowedFilters(AllowedFilter::operator('salary', FilterOperator::DYNAMIC))
//        ->get();
//
//    expect($results)->toHaveCount(2);
// });
