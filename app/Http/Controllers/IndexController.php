<?php

namespace App\Http\Controllers;

use App\Exceptions\ModelDBException;
use App\ModelFunctions\ConfigFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use function Safe\phpinfo;

class IndexController extends Controller
{
	private ConfigFunctions $configFunctions;
	private SymLinkFunctions $symLinkFunctions;

	/**
	 * @param ConfigFunctions  $configFunctions
	 * @param SymLinkFunctions $symLinkFunctions
	 */
	public function __construct(ConfigFunctions $configFunctions, SymLinkFunctions $symLinkFunctions)
	{
		$this->configFunctions = $configFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
	}

	/**
	 * Display the landing page if enabled
	 * otherwise display the gallery.
	 *
	 * @return View|RedirectResponse|BinaryFileResponse
	 *
	 * @throws BindingResolutionException
	 * @throws ModelDBException
	 */
	public function show(): View|RedirectResponse|BinaryFileResponse
	{
		if (Configs::getValueAsBool('landing_page_enable')) {
			$infos = $this->configFunctions->get_pages_infos();

			$title = Configs::getValueAsString('site_title');
			$rss_enable = Configs::getValueAsBool('rss_enable');

			$page_config = [];
			$page_config['show_hosted_by'] = false;
			$page_config['display_socials'] = false;

			return view('landing', [
				'title' => $title,
				'infos' => $infos,
				'page_config' => $page_config,
				'rss_enable' => $rss_enable,
			]);
		}

		return $this->gallery();
	}

	/**
	 * Just call the phpinfo function.
	 * Cannot be tested.
	 *
	 * @return void
	 */
	// @codeCoverageIgnoreStart
	public function phpinfo(): void
	{
		phpinfo();
	}

	// @codeCoverageIgnoreEnd

	/**
	 * Display the gallery.
	 *
	 * @return BinaryFileResponse
	 *
	 * @throws BindingResolutionException
	 * @throws ModelDBException
	 */
	public function gallery(): BinaryFileResponse
	{
		$this->symLinkFunctions->remove_outdated();
		$infos = $this->configFunctions->get_pages_infos();

		$title = Configs::getValueAsString('site_title');
		$rss_enable = Configs::getValueAsBool('rss_enable');
		$page_config = [];
		$page_config['show_hosted_by'] = true;
		$page_config['display_socials'] = Configs::getValueAsBool('footer_show_social_media');

		return response()->file(public_path('frontend.html'));

		/*return view('gallery', [
			'title' => $title,
			'infos' => $infos,
			'page_config' => $page_config,
			'rss_enable' => $rss_enable,
		]);*/
	}
}
