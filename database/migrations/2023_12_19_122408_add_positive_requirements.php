<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class() extends Migration {
	public const KEYS = [
		'compression_quality',
		'site_copyright_begin',
		'site_copyright_end',
		'recent_age',
		'SL_life_time_days',
		'update_check_every_days',
		'rss_recent_days',
		'rss_max_items',
		'swipe_tolerance_x',
		'swipe_tolerance_y',
		'log_max_num_line',
	];

	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		DB::table('configs')->whereIn('key', self::KEYS)->update(['type_range' => 'positive']);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		DB::table('configs')->whereIn('key', self::KEYS)->update(['type_range' => 'int']);
	}
};
