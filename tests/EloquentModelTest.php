<?php

use Mockery as m;

class EloquentModelTest extends PHPUnit_Framework_TestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testAttributeManipulation()
	{
		$model = new EloquentModelStub;
		$model->name = 'foo';
		$this->assertEquals('foo', $model->name);
		$this->assertTrue(isset($model->name));
		unset($model->name);
		$this->assertFalse(isset($model->name));

		// test mutation
		$model->list_items = array('name' => 'taylor');
		$this->assertEquals(array('name' => 'taylor'), $model->list_items);
		$attributes = $model->getAttributes();
		$this->assertEquals(json_encode(array('name' => 'taylor')), $attributes['list_items']);
	}


	public function testNewInstanceReturnsNewInstanceWithAttributesSet()
	{
		$model = new EloquentModelStub;
		$instance = $model->newInstance(array('name' => 'taylor'));
		$this->assertInstanceOf('EloquentModelStub', $instance);
		$this->assertEquals('taylor', $instance->name);
	}


	public function testCreateMethodSavesNewModel()
	{
		$_SERVER['__eloquent.saved'] = false;
		$model = EloquentModelSaveStub::create(array('name' => 'taylor'));
		$this->assertTrue($_SERVER['__eloquent.saved']);
		$this->assertEquals('taylor', $model->name);
	}


	public function testFindMethodCallsQueryBuilderCorrectly()
	{
		$result = EloquentModelFindStub::find(1);
		$this->assertEquals('foo', $result);
	}


	public function testWithMethodCallsQueryBuilderCorrectly()
	{
		$result = EloquentModelWithStub::with('foo', 'bar');
		$this->assertEquals('foo', $result);
	}


	public function testWithMethodCallsQueryBuilderCorrectlyWithArray()
	{
		$result = EloquentModelWithStub::with(array('foo', 'bar'));
		$this->assertEquals('foo', $result);
	}

}

class EloquentModelStub extends Illuminate\Database\Eloquent\Model {
	protected $table = 'stub';
	public function getListItems($value)
	{
		return json_decode($value, true);
	}
	public function setListItems($value)
	{
		return json_encode($value);
	}
}

class EloquentModelSaveStub extends Illuminate\Database\Eloquent\Model {
	protected $table = 'stub';
	public function save() { $_SERVER['__eloquent.saved'] = true; }
}

class EloquentModelFindStub extends Illuminate\Database\Eloquent\Model {
	public function newQuery()
	{
		$mock = m::mock('Illuminate\Database\Eloquent\Builder');
		$mock->shouldReceive('find')->once()->with(1, array('*'))->andReturn('foo');
		return $mock;
	}
}

class EloquentModelWithStub extends Illuminate\Database\Eloquent\Model {
	public function newQuery()
	{
		$mock = m::mock('Illuminate\Database\Eloquent\Builder');
		$mock->shouldReceive('with')->once()->with(array('foo', 'bar'))->andReturn('foo');
		return $mock;
	}
}