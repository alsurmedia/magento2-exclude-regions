<?php
/**
 * Collection
 *
 * @copyright Copyright © 2020 Alsurmedia. All rights reserved.
 * @author    s.bermejo@alsurmedia.com
 */

namespace Alsurmedia\ExcludeRegions\Plugin\Region;

/**
 * Class Collection
 */
class Collection
{
	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $scopeConfig;
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	protected $storeManager;

	/**
	 * Collection constructor.
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 */
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	)
	{
		$this->scopeConfig = $scopeConfig;
		$this->storeManager = $storeManager;
	}

	/**
	 * @param \Magento\Directory\Model\ResourceModel\Region\Collection $subject
	 * @param $result
	 * @return mixed
	 */
	function afterToOptionArray(
		\Magento\Directory\Model\ResourceModel\Region\Collection $subject,
		$result
	) {
		$isEnabled = $this->scopeConfig->getValue(
			'limitregions/regions/enabled',
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$this->storeManager->getStore()->getStoreId());

		if($isEnabled) {
			$excludedRegions = explode(',', $this->scopeConfig->getValue(
				'limitregions/regions/excluded_regions',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
				$this->storeManager->getStore()->getStoreId()));

			foreach ($excludedRegions as $excludedRegion) {
				$result = $this->removeElementWithValue($result, 'value', $excludedRegion);
			}
		}

		return $result;
	}

	/**
	 * @param $array
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	function removeElementWithValue($array, $key, $value){
		foreach($array as $subKey => $subArray){
			if($subArray[$key] == $value){
				unset($array[$subKey]);
			}
		}
		return $array;
	}

}
