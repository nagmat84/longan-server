<?php

namespace App\Console\Commands;

use App\Contracts\ExternalLycheeException;
use App\Contracts\LycheeException;
use App\Contracts\SizeVariantFactory;
use App\Exceptions\UnexpectedException;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Safe\Exceptions\InfoException;
use function Safe\set_time_limit;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

class GenerateThumbs extends Command
{
	/**
	 * @var array<string, int>
	 *
	 * @phpstan-var array<string, int<0,6>>
	 */
	public const SIZE_VARIANTS = [
		'thumb' => SizeVariant::THUMB,
		'thumb2x' => SizeVariant::THUMB2X,
		'small' => SizeVariant::SMALL,
		'small2x' => SizeVariant::SMALL2X,
		'medium' => SizeVariant::MEDIUM,
		'medium2x' => SizeVariant::MEDIUM2X,
	];

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:generate_thumbs {type : thumb name} {amount=100 : amount of photos to process} {timeout=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate intermediate thumbs if missing';

	/**
	 * Execute the console command.
	 *
	 * @return int
	 *
	 * @throws ExternalLycheeException
	 */
	public function handle(): int
	{
		try {
			$sizeVariantName = strval($this->argument('type'));
			if (!array_key_exists($sizeVariantName, self::SIZE_VARIANTS)) {
				$this->error(sprintf('Type %s is not one of %s', $sizeVariantName, implode(', ', array_flip(self::SIZE_VARIANTS))));

				return 1;
			}
			$sizeVariantType = self::SIZE_VARIANTS[$sizeVariantName];

			$amount = (int) $this->argument('amount');
			$timeout = (int) $this->argument('timeout');

			try {
				set_time_limit($timeout);
			} catch (InfoException) {
				// Silently do nothing, if `set_time_limit` is denied.
			}

			$this->line(
				sprintf(
					'Will attempt to generate up to %s %s images with a timeout of %d seconds...',
					$amount,
					$sizeVariantName,
					$timeout
				)
			);

			$photos = Photo::query()
				->where('type', 'like', 'image/%')
				->with('size_variants')
				->whereDoesntHave('size_variants', function (Builder $query) use ($sizeVariantType) {
					$query->where('type', '=', $sizeVariantType);
				})
				->take($amount)
				->get();

			if (count($photos) === 0) {
				$this->line('No picture requires ' . $sizeVariantName . '.');

				return 0;
			}

			$bar = $this->output->createProgressBar(count($photos));
			$bar->start();

			// Initialize factory for size variants
			$sizeVariantFactory = resolve(SizeVariantFactory::class);
			/** @var Photo $photo */
			foreach ($photos as $photo) {
				$sizeVariantFactory->init($photo);
				$sizeVariant = $sizeVariantFactory->createSizeVariantCond($sizeVariantType);
				if ($sizeVariant !== null) {
					$this->line('   ' . $sizeVariantName . ' (' . $sizeVariant->width . 'x' . $sizeVariant->height . ') for ' . $photo->title . ' created.');
				} else {
					$this->line('   Did not create ' . $sizeVariantName . ' for ' . $photo->title . '.');
				}
				$bar->advance();
			}

			$bar->finish();
			$this->line('  ');

			return 0;
		} catch (LycheeException|SymfonyConsoleException $e) {
			if ($e instanceof ExternalLycheeException) {
				throw $e;
			} else {
				throw new UnexpectedException($e);
			}
		}
	}
}
