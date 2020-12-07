<?php
/**
 * Data
 *
 * @copyright Copyright © 2020 Alsurmedia. All rights reserved.
 * @author    s.bermejo@alsurmedia.com
 */

namespace Alsurmedia\ExcludeRegions\Helper;

/**
 * Class Data
 * @package Alsurmedia\ExcludeRegions\Helper
 */
class Data extends \Magento\Directory\Helper\Data
{
	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $scopeConfig;

	/**
	 * Data constructor.
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
	 * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
	 * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regCollectionFactory
	 * @param \Magento\Framework\Json\Helper\Data $jsonHelper
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\App\Cache\Type\Config $configCacheType,
		\Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection,
		\Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regCollectionFactory,
		\Magento\Framework\Json\Helper\Data $jsonHelper,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Directory\Model\CurrencyFactory $currencyFactory,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	)
	{
		$this->scopeConfig = $scopeConfig;
		parent::__construct($context, $configCacheType, $countryCollection, $regCollectionFactory, $jsonHelper, $storeManager, $currencyFactory);
	}

	/**
	 * Retrieve regions data
	 *
	 * @return array
	 */
	public function getRegionData()
	{
		$countryIds = [];
		foreach ($this->getCountryCollection() as $country) {
			$countryIds[] = $country->getCountryId();
		}
		$collection = $this->_regCollectionFactory->create();
		$collection->addCountryFilter($countryIds)->load();
		$regions = [
			'config' => [
				'show_all_regions' => $this->isShowNonRequiredState(),
				'regions_required' => $this->getCountriesWithStatesRequired(),
			],
		];

		$isEnabled = $this->scopeConfig->getValue(
			'limitregions/regions/enabled',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$this->_storeManager->getStore()->getStoreId());

		if($isEnabled) {
			$excludedRegions = explode(',', $this->scopeConfig->getValue(
				'limitregions/regions/excluded_regions',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
				$this->_storeManager->getStore()->getStoreId()));
		}

		foreach ($collection as $region) {
			/** @var $region \Magento\Directory\Model\Region */
			if (!$region->getRegionId()) {
				continue;
			}
			if($isEnabled) {
				if(in_array($region->getRegionId(), $excludedRegions)) {
					continue;
				}
			}
			$regions[$region->getCountryId()][$region->getRegionId()] = [
				'code' => $region->getCode(),
				'name' => (string)__($region->getName()),
			];
		}
		return $regions;
	}
}
