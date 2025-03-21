<?php

namespace App\Providers;

use App\Actions\Update\Apply as ApplyUpdate;
use App\Actions\Update\Check as CheckUpdate;
use App\Assets\Helpers;
use App\Assets\SizeVariantGroupedWithRandomSuffixNamingStrategy;
use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\Factories\AlbumFactory;
use App\Factories\LangFactory;
use App\Image\SizeVariantDefaultFactory;
use App\Image\StreamStatFilter;
use App\Locale\Lang;
use App\Metadata\GitHubFunctions;
use App\Metadata\GitRequest;
use App\Metadata\LycheeVersion;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Policies\AlbumQueryPolicy;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Safe\Exceptions\StreamException;
use function Safe\stream_filter_register;

class AppServiceProvider extends ServiceProvider
{
	public array $singletons
	= [
		SymLinkFunctions::class => SymLinkFunctions::class,
		ConfigFunctions::class => ConfigFunctions::class,
		LangFactory::class => LangFactory::class,
		Lang::class => Lang::class,
		Helpers::class => Helpers::class,
		GitRequest::class => GitRequest::class,
		GitHubFunctions::class => GitHubFunctions::class,
		LycheeVersion::class => LycheeVersion::class,
		CheckUpdate::class => CheckUpdate::class,
		ApplyUpdate::class => ApplyUpdate::class,
		AlbumFactory::class => AlbumFactory::class,
		AlbumQueryPolicy::class => AlbumQueryPolicy::class,
		PhotoQueryPolicy::class => PhotoQueryPolicy::class,
	];

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		if (config('app.db_log_sql', false) === true) {
			DB::listen(function ($query) {
				$msg = $query->sql . ' [' . implode(', ', $query->bindings) . ']';
				Log::info($msg);
			});
		}

		/**
		 * We enforce strict mode
		 * this has the following effect:
		 * - lazy loading is disabled
		 * - non-fillable attributes on creation of model are not discarded but throw an error
		 * - prevents accessing missing attributes.
		 */
		Model::shouldBeStrict();

		try {
			stream_filter_register(
				StreamStatFilter::REGISTERED_NAME,
				StreamStatFilter::class
			);
		} catch (StreamException) {
			// We ignore any error here, because Laravel calls the `boot`
			// method several times and any subsequent attempt to register a
			// filter for the same name anew will fail.
		}
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('lang', function () {
			return resolve(Lang::class);
		});

		$this->app->bind('Helpers', function () {
			return resolve(Helpers::class);
		});

		$this->app->bind(
			SizeVariantNamingStrategy::class,
			SizeVariantGroupedWithRandomSuffixNamingStrategy::class
		);

		$this->app->bind(
			SizeVariantFactory::class,
			SizeVariantDefaultFactory::class
		);
	}
}
