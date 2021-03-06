<?php

namespace Okipa\LaravelModelJsonStorage\Test\Unit;

use Okipa\LaravelModelJsonStorage\Test\Fakers\UsersFaker;
use Okipa\LaravelModelJsonStorage\Test\ModelJsonStorageTestCase;
use Okipa\LaravelModelJsonStorage\Test\Models\UserDatabase;
use Okipa\LaravelModelJsonStorage\Test\Models\UserJson;

class BuilderOverrideTest extends ModelJsonStorageTestCase
{
    use UsersFaker;

    public function setUp()
    {
        parent::setUp();
    }

    public function testGet()
    {
        $this->createMultipleDatabaseUsers(3);
        $getDatabaseUsersArray = app(UserDatabase::class)->get()->toArray();
        $getJsonUsersArray = app(UserJson::class)->get()->toArray();
        // notice : we remove the created_at and updated_at fields that are not relevant and can be different
        foreach ($getDatabaseUsersArray as $key => $databaseUserArray) {
            unset($databaseUserArray['created_at']);
            unset($databaseUserArray['updated_at']);
            $getDatabaseUsersArray[$key] = $databaseUserArray;
        }
        foreach ($getJsonUsersArray as $key => $jsonUserArray) {
            unset($jsonUserArray['created_at']);
            unset($jsonUserArray['updated_at']);
            $getJsonUsersArray[$key] = $jsonUserArray;
        }
        $this->assertEquals($getDatabaseUsersArray, $getJsonUsersArray);
        $getIdAndEmailDatabaseUsers = app(UserDatabase::class)->get(['id', 'email']);
        $getIdAndEmailJsonUsers = app(UserJson::class)->get(['id', 'email']);
        // notice : we compare the models one by one because query builder does return objects from get() in a random way
        $getIdAndEmailDatabaseUsers->each(function($databaseUser) use ($getIdAndEmailJsonUsers) {
            $jsonUserToCompare = $getIdAndEmailJsonUsers->where('id', $databaseUser->id)->first();
            $this->assertEquals($databaseUser->toArray(), $jsonUserToCompare->toArray());
        });
    }

    public function testSelect()
    {
        $this->createMultipleDatabaseUsers(3);
        $selectedDatabaseUser = app(UserDatabase::class)->select('name')->get();
        $selectedJsonUser = app(UserJson::class)->select('name')->get();
        $this->assertEquals($selectedDatabaseUser->toArray(), $selectedJsonUser->toArray());
    }

    public function testAddSelect()
    {
        $this->createMultipleDatabaseUsers(3);
        $selectedDatabaseUser = app(UserDatabase::class)->select('id')->addSelect('name')->get();
        $selectedJsonUser = app(UserJson::class)->select('id')->addSelect('name')->get();
        $this->assertEquals($selectedDatabaseUser->toArray(), $selectedJsonUser->toArray());
    }

    public function testWhere()
    {
        $this->createMultipleDatabaseUsers(3);
        $whereDatabaseUsers = app(UserDatabase::class)->where('id', 2)->get();
        $whereJsonUsers = app(UserJson::class)->where('id', 2)->get();
        $this->assertEquals($whereDatabaseUsers->toArray(), $whereJsonUsers->toArray());
    }

    public function testWhereIn()
    {
        $this->createMultipleDatabaseUsers(3);
        $whereInDatabaseUsers = app(UserDatabase::class)->whereIn('id', [2, 3])->get();
        $whereInJsonUsers = app(UserJson::class)->whereIn('id', [2, 3])->get();
        $this->assertEquals($whereInDatabaseUsers->toArray(), $whereInJsonUsers->toArray());
    }

    public function testWhereNotIn()
    {
        $this->createMultipleDatabaseUsers(3);
        $whereInDatabaseUsers = app(UserDatabase::class)->whereNotIn('id', [1, 3])->get();
        $whereInJsonUsers = app(UserJson::class)->whereNotIn('id', [1, 3])->get();
        $this->assertEquals($whereInDatabaseUsers->toArray(), $whereInJsonUsers->toArray());
    }

    public function testOrderBy()
    {
        $this->createMultipleDatabaseUsers(3);
        $orderedByIdDescDatabaseUsers = app(UserDatabase::class)->orderBy('id', 'desc')->get();
        $orderedByIdDescJsonUsers = app(UserJson::class)->orderBy('id', 'desc')->get();
        $orderedByNameAscDatabaseUsers = app(UserDatabase::class)->orderBy('name', 'asc')->get();
        $orderedByNameAscJsonUsers = app(UserJson::class)->orderBy('name', 'asc')->get();
        $this->assertEquals($orderedByIdDescDatabaseUsers->toArray(), $orderedByIdDescJsonUsers->toArray());
        $this->assertEquals($orderedByNameAscDatabaseUsers->toArray(), $orderedByNameAscJsonUsers->toArray());
    }

    public function testFind()
    {
        $this->createMultipleDatabaseUsers(5);
        $foundDatabaseUser = app(UserDatabase::class)->find(4);
        $foundJsonUser = app(UserJson::class)->find(4);
        $this->assertEquals($foundDatabaseUser->toArray(), $foundJsonUser->toArray());
    }

    public function testFailedFind()
    {
        $foundDatabaseUser = app(UserDatabase::class)->find(4);
        $this->assertEmpty($foundDatabaseUser);
    }

    public function testFindOrFail()
    {
        $this->createMultipleDatabaseUsers(5);
        $foundDatabaseUser = app(UserDatabase::class)->findOrFail(4);
        $foundJsonUser = app(UserJson::class)->findOrFail(4);
        $this->assertEquals($foundDatabaseUser->toArray(), $foundJsonUser->toArray());
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     * @expectedExceptionMessage No query results for model [Okipa\LaravelModelJsonStorage\Test\Models\UserDatabase] 4
     */
    public function testFailedFindOrFail()
    {
        app(UserDatabase::class)->findOrFail(4);
    }

    public function testPaginate()
    {
        $this->createMultipleDatabaseUsers(10);
        $firstPageDatabaseUsers = app(UserDatabase::class)->paginate();
        $firstPageJsonUsers = app(UserJson::class)->paginate();
        $secondPageDatabaseUsers = app(UserDatabase::class)->paginate(5, ['name'], 'page', 2);
        $secondPageJsonUsers = app(UserJson::class)->paginate(5, ['name'], 'page', 2);
        // notice : we remove the created_at and updated_at fields that are not relevant and can be different
        foreach ($firstPageDatabaseUsers as $key => $firstPageDatabaseUser) {
            unset($firstPageDatabaseUser->created_at);
            unset($firstPageDatabaseUser->updated_at);
        }
        foreach ($firstPageJsonUsers as $key => $firstPageJsonUser) {
            unset($firstPageJsonUser->created_at);
            unset($firstPageJsonUser->updated_at);
        }
        $this->assertEquals($secondPageDatabaseUsers->toArray(), $secondPageJsonUsers->toArray());
        $this->assertEquals($secondPageDatabaseUsers->toArray(), $secondPageJsonUsers->toArray());
    }

    public function testValue()
    {
        $this->createMultipleDatabaseUsers(3);
        $valueDatabaseUser = app(UserDatabase::class)->where('id', 2)->value('email');
        $valueUserJson = app(UserJson::class)->where('id', 2)->value('email');
        $this->assertEquals($valueDatabaseUser, $valueUserJson);
    }

    public function testPluck()
    {
        $this->createMultipleDatabaseUsers(3);
        $countDatabaseUser = app(UserDatabase::class)->pluck('name', 'email');
        $countUserJson = app(UserJson::class)->pluck('name', 'email');
        $this->assertEquals($countDatabaseUser, $countUserJson);
    }

    public function testCount()
    {
        $this->createMultipleDatabaseUsers(10);
        $countDatabaseUser = app(UserDatabase::class)->count();
        $countUserJson = app(UserJson::class)->count();
        $this->assertEquals($countDatabaseUser, $countUserJson);
    }

    public function testMin()
    {
        $this->createMultipleDatabaseUsers(10);
        $minDatabaseUser = app(UserDatabase::class)->min('id');
        $minUserJson = app(UserJson::class)->min('id');
        $this->assertEquals($minDatabaseUser, $minUserJson);
    }

    public function testMax()
    {
        $this->createMultipleDatabaseUsers(10);
        $maxDatabaseUser = app(UserDatabase::class)->max('id');
        $maxUserJson = app(UserJson::class)->max('id');
        $this->assertEquals($maxDatabaseUser, $maxUserJson);
    }

    public function testAvg()
    {
        $this->createMultipleDatabaseUsers(10);
        $avgDatabaseUser = app(UserDatabase::class)->avg('id');
        $avgUserJson = app(UserJson::class)->avg('id');
        $this->assertEquals($avgDatabaseUser, $avgUserJson);
    }
}
