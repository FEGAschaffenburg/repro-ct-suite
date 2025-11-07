<?php
/**
 * Test case for Shortcode Presets Repository
 *
 * @package Repro_CT_Suite
 * @subpackage Tests
 */

class Test_Repro_CT_Suite_Shortcode_Presets_Repository extends WP_UnitTestCase {

	/**
	 * Repository instance
	 * @var Repro_CT_Suite_Shortcode_Presets_Repository
	 */
	private $repository;

	/**
	 * Set up test environment
	 */
	public function setUp(): void {
		parent::setUp();
		$this->repository = new Repro_CT_Suite_Shortcode_Presets_Repository();
		
		// Ensure clean database state
		global $wpdb;
		$table = $wpdb->prefix . 'rcts_shortcode_presets';
		$wpdb->query( "DELETE FROM $table" );
	}

	/**
	 * Test saving a new preset
	 */
	public function test_save_preset() {
		$preset_data = array(
			'name' => 'Test Preset',
			'view' => 'cards',
			'limit_count' => 10,
			'calendar_ids' => '1,2,3',
			'from_days' => 0,
			'to_days' => 30,
			'show_past' => 0,
			'order_dir' => 'ASC',
			'show_fields' => 'title,date,time'
		);

		$result = $this->repository->save( $preset_data );

		$this->assertIsInt( $result );
		$this->assertGreaterThan( 0, $result );
	}

	/**
	 * Test getting all presets
	 */
	public function test_get_all_presets() {
		// Add test data
		$this->repository->save( array(
			'name' => 'Preset 1',
			'view' => 'list',
			'limit_count' => 5
		) );

		$this->repository->save( array(
			'name' => 'Preset 2',
			'view' => 'cards',
			'limit_count' => 10
		) );

		$presets = $this->repository->get_all();

		$this->assertIsArray( $presets );
		$this->assertCount( 2, $presets );
		$this->assertEquals( 'Preset 1', $presets[0]->name );
		$this->assertEquals( 'Preset 2', $presets[1]->name );
	}

	/**
	 * Test getting preset by ID
	 */
	public function test_get_by_id() {
		$preset_id = $this->repository->save( array(
			'name' => 'Test Preset',
			'view' => 'cards',
			'limit_count' => 15
		) );

		$preset = $this->repository->get_by_id( $preset_id );

		$this->assertIsObject( $preset );
		$this->assertEquals( 'Test Preset', $preset->name );
		$this->assertEquals( 'cards', $preset->view );
		$this->assertEquals( 15, $preset->limit_count );
	}

	/**
	 * Test updating a preset
	 */
	public function test_update_preset() {
		$preset_id = $this->repository->save( array(
			'name' => 'Original Name',
			'view' => 'list'
		) );

		$result = $this->repository->update( $preset_id, array(
			'name' => 'Updated Name',
			'view' => 'cards'
		) );

		$this->assertTrue( $result );

		$updated_preset = $this->repository->get_by_id( $preset_id );
		$this->assertEquals( 'Updated Name', $updated_preset->name );
		$this->assertEquals( 'cards', $updated_preset->view );
	}

	/**
	 * Test deleting a preset
	 */
	public function test_delete_preset() {
		$preset_id = $this->repository->save( array(
			'name' => 'To Delete',
			'view' => 'list'
		) );

		$result = $this->repository->delete( $preset_id );
		$this->assertTrue( $result );

		$deleted_preset = $this->repository->get_by_id( $preset_id );
		$this->assertNull( $deleted_preset );
	}

	/**
	 * Test duplicate name validation
	 */
	public function test_duplicate_name_validation() {
		$this->repository->save( array(
			'name' => 'Duplicate Name',
			'view' => 'list'
		) );

		// Saving another preset with same name should fail
		$result = $this->repository->save( array(
			'name' => 'Duplicate Name',
			'view' => 'cards'
		) );

		$this->assertFalse( $result );
	}

	/**
	 * Test getting nonexistent preset
	 */
	public function test_get_nonexistent_preset() {
		$preset = $this->repository->get_by_id( 99999 );
		$this->assertNull( $preset );
	}
}